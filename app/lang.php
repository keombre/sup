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

    function loadLangs($path = __DIR__ . "/../langs/") {
        $this->path = realpath($path);
        
        if (!is_dir($this->path)) {
            throw new \Exception("Language directory not found");
            return 0;
        }
        
        if (!is_file($this->path . "/langs.ini")) {
            throw new \Exception("langs.ini not found");
            return 0;
        }

        foreach (parse_ini_file($this->path . "/langs.ini") as $lang => $name) {
            if (!is_dir($this->path . "/" . $lang)) {
                throw new \Exception($lang . " defined but missing dir");
                return 0;
            }
            
            if (!is_file($this->path . "/" . $lang . "/lang.ini")) {
                throw new \Exception($lang . " missing lang.ini");
                return 0;
            }

            $this->langs[$lang] = $name;
        }

        if (count($this->langs) == 0) {
            throw new \Exception("No languages found");
            return 0;
        }
        return 1;
    }

    function setLang($lang) {
        if (!array_key_exists($lang, $this->langs))
            return 0;
        
        $this->setLang = $lang;
        $_SESSION['lang'] = $lang;

        $this->table = parse_ini_file($this->path . "/" . $lang . "/lang.ini", true);
        return 1;
    }

    function getLang() {
        reset($this->langs);
        if (array_key_exists('lang', $_SESSION)) {
            if (!$this->setLang($_SESSION['lang'])) {
                $this->setLang(key($this->langs));
            }
        } else {
            $this->setLang(key($this->langs));
        }
    }

    function getLangs() {
        return $this->langs;
    }

    function g($field, $section) {
        if (!array_key_exists($section, $this->table))
            return null;
        if (!array_key_exists($field, $this->table[$section]))
            return null;
        return $this->table[$section][$field];
    }

}
