<?php

class lang {
    
    protected $container;
    protected $langs = [];
    protected $setLang = "";
    private   $table;
    private   $path;
    
    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    function loadLangs($path) {
        $this->path = realpath($path);
        
        if (!is_dir($this->path)) {
            throw new \Exception("Language directory not found");
            return false;
        }
        
        if (!is_file($this->path . "/langs.ini")) {
            throw new \Exception("langs.ini not found");
            return false;
        }

        foreach (parse_ini_file($this->path . "/langs.ini") as $lang => $name) {
            if ($lang == 'default') {
                $this->langs[$lang] = $name;
                continue;
            }
            if (!is_dir($this->path . "/" . $lang)) {
                throw new \Exception($lang . " defined but missing dir");
                return false;
            }
            
            if (!is_file($this->path . "/" . $lang . "/lang.ini")) {
                throw new \Exception($lang . " missing lang.ini");
                return false;
            }

            $this->langs[$lang] = $name;
        }

        if (count($this->langs) == 0) {
            throw new \Exception("No languages found");
            return false;
        }

        if (!array_key_exists('default', $this->langs)) {
            throw new \Exception("No default language specified");
            return false;
        }

        if (!array_key_exists($this->langs['default'], $this->langs)) {
            throw new \Exception("Default language not found");
            return false;
        }
        return true;
    }

    function setLang($lang) {
        if (!array_key_exists($lang, $this->langs) || $lang == 'default')
            return false;
        
        $this->setLang = $lang;
        $_SESSION['lang'] = $lang;

        $this->table = parse_ini_file($this->path . "/" . $lang . "/lang.ini", true);
        return true;
    }

    function getLang() {
        if (
            array_key_exists('lang', $_SESSION) &&
            $_SESSION['lang'] != 'default' &&
            $this->setLang($_SESSION['lang'])
        ) return;
        else $this->setLang($this->langs['default']);
    }

    function getLangs() {
        $ret = [];
        foreach ($this->langs as $id => $name) {
            if ($id == 'default') continue;
            $ret[$id] = $name;
        }

        return $ret;
    }

    function getActive() {
        return $this->setLang;
    }

    function g($field, $section, $replaceArr = null) {
        if (!array_key_exists($section, $this->table))
            return null;
        if (!array_key_exists($field, $this->table[$section]))
            return null;
        
        if (!is_array($replaceArr))
            return $this->table[$section][$field];
        
        $search = array_map(function ($e) {return "%%" . $e . "%%";}, array_keys($replaceArr));
        $replace = array_values($replaceArr);

        return str_replace($search, $replace, $this->table[$section][$field]);
    }

}
