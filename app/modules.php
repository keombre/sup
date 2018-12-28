<?php

class modules {

    private $cacheName = '/supCacheUpdater';
    private $cacheTimeOffset = 60 * 10;
    
    protected $container;
    protected $modulesInstalled = [];
    protected $modulesRemote = [];

    function __construct($container) {
        $this->container = $container;
        
        $this->syncRepo();

        foreach ($this->loadDB() as $entry)
            $this->modulesInstalled[] = (new Module($this->container->db))->createFromArray($entry);
        
        foreach ($this->loadCache() as $entry) {
            $module = (new Module($this->container->db))->withName($entry['name'])->withVersion($entry['version']);
            $update = false;
            foreach ($this->modulesInstalled as $key => $installed) {
                if ($installed->getName() == $module->getName() && version_compare($installed->getVersion(), $module->getVersion(), '<')) {
                    $this->modulesInstalled[$key] = $installed->withUpdate(true);
                    $update = true;
                    break;
                }
            }
            if (!$update)
                $this->modulesRemote[] = $module;
        }
    }

    function install(Module $module) {
        if ($module->validateDB())
            return false;
        
        if ($zip = $this->request('https://api.github.com/repos/keombre/sup-modules/zipball/' . $module->getName()) === false);
            return false;
        
        $tmpName = tempnam($this->getCachePath(), 'sup_zip');
        file_put_contents($tmpName, $zip);
        unset($zip);

        $zip = new ZipArchive;
        $res = $zip->open($tmpName);
        if ($res !== TRUE) {
            unlink($tmpName);
            return false;
        }

        $tmpFolder = tempnam($this->getCachePath(), 'sup_zip');
        mkdir($tmpFolder);

        $zip->extractTo($tmpFolder);
        $zip->close();
        
        unlink($tmpName);
        
        $files = scandir($tmpFolder);
        if (count($files) != 3)
            return false;
        
        return rename($tmpFolder . '/' . $files[2], __DIR__ . '/../modules/' . $module->getName());

    }

    function update(Module $module) {
        if (!$module->validateDB())
            return false;
        
        $module->remove();
        return $this->install($module->getName());
    }

    function getInstalled() {
        return $this->modulesInstalled;
    }

    function getRemote() {
        return $this->modulesRemote;
    }

    function findInstalled($id) {
        if (!array_key_exists($id, $this->modulesInstalled))
            return null;
        return $this->modulesInstalled[$id];
    }

    function findRemote($name) {
        foreach ($this->modulesRemote as $module)
            if ($module->getName() == $name)
                return $module;
        return null;
    }

    function loadDB() {
        return $this->container->db->select('modules', ['id', 'name', 'version', 'active']);
    }

    function loadCache() {
        $cachePath = $this->getCachePath();

        if (!is_file($cachePath . $this->cacheName))
            return false;
        
        return json_decode(file_get_contents($cachePath . $this->cacheName), true);
    }

    function syncRepo() {
        $cachePath = $this->getCachePath();

        if (!is_file($cachePath . $this->cacheName))
            return $this->downloadRepo();
        
        if (filemtime($cachePath . $this->cacheName) + $this->cacheTimeOffset < time())
            return $this->downloadRepo();
        
        return true;
    }

    protected function getCachePath() {
        return sys_get_temp_dir();
    }

    private function downloadRepo() {
        if (($modules = $this->request('https://api.github.com/repos/keombre/sup-modules/branches')) === false)
            return false;

        $save = [];
        foreach (json_decode($modules, true) as $module) {
            if (($info = $this->request('https://raw.githubusercontent.com/keombre/sup-modules/' . $module['name'] . '/manifest.json')) === false)
                continue;
            
                try {
                $infoArr = json_decode($info, true);
            } catch (\ErrorException $e) {
                continue;
            }
            
            if (!array_key_exists('name', $infoArr) || $infoArr['name'] != $module['name'])
                continue;
            
            if (!array_key_exists('version', $infoArr))
                continue;
            
            $save[] = [
                "name" => $module['name'],
                "version" => $infoArr['version']
            ];
        }

        $cachePath = $this->getCachePath();
        $json = json_encode($save);
        
        file_put_contents($cachePath . $this->cacheName, $json);

        return true;
    }

    private function request($site) {
        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "Accept: application/vnd.github.v3+json\r\nUser-Agent: Mozilla/5.0 (API Updater)"
            ]
        ];

        $context = stream_context_create($opts);

        try {
            return file_get_contents($site, false, $context);
        } catch (\ErrorException $e) {
            return false;
        }
    }
}

class Module {

    protected $path = __DIR__ . '/../modules/';
    protected $DBpath = __DIR__ . '/../db/';

    protected $db;

    protected $name;
    protected $version;
    protected $id = null;
    protected $active = 0;
    protected $update = false;
    
    function __construct($db) {
        $this->db = $db;
    }

    function createFromArray($info) {
        $clone = clone $this;
        $clone->name = $info['name'];
        $clone->version = $info['version'];
        $clone->id = $info['id'];
        $clone->active = $this->parseActive($info['active']);

        return $clone;
    }

    function createFromDB($id) {
        if (!$this->db->has('modules', ['id' => $id]))
            return false;
        
        $info = $this->db->get('modules', ['id', 'name', 'version', 'active'], ['id' => $id]);

        return $this->createFromArray($info);
    }

    function withName($name) {
        if (!is_string($name))
            throw new \InvalidArgumentException('Invalid argument passed to withName of type ' . gettype($name));
        
        $clone = clone $this;
        $clone->name = $name;
        return $clone;
    }

    function withVersion($version) {
        if (!is_string($version))
            throw new \InvalidArgumentException('Invalid argument passed to withVersion of type ' . gettype($version));
        
        $clone = clone $this;
        $clone->version = $version;
        return $clone;
    }

    function withUpdate($update) {
        if (!is_bool($update))
            throw new \InvalidArgumentException('Invalid argument passed to withUpdate of type ' . gettype($update));
        
        $clone = clone $this;
        $clone->update = $update;
        return $clone;
    }

    function getName() {
        return $this->name;
    }

    function getVersion() {
        return $this->version;
    }

    function getUpdate() {
        return $this->update;
    }

    function validateInstall() {
        if (!is_dir($this->path . $this->name))
            return false;
        if (!is_class('\\modules\\' . $this->name . '\\routes'))
            return false;
        return true;
    }

    function validateDB() {
        if (!$this->db->has('modules', ['name' => $this->name]))
            return false;
    }

    function remove() {
        $this->removeDir($this->path . $this->name);
        $this->db->delete('modules', ['id' => $this->id]);
    }

    function purge() {
        $this->removeDir($this->path . $this->name);
        $this->removeDir($this->DBpath . $this->name);
    }

    function enable() {
        $this->db->update('modules', ['active' => 1], ['id' => $this->id]);
    }

    function disable() {
        $this->db->update('modules', ['active' => 0], ['id' => $this->id]);
    }

    private function parseActive($active) {
        if (!is_numeric($active)) return 0;
        return intval($active) == 1;
    }

    private function removeDir($path) {
        $it = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach($files as $file) {
            if ($file->isDir())
                rmdir($file->getRealPath());
            else
                unlink($file->getRealPath());
        }
        rmdir($path);
    }
}
