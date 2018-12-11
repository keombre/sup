<?php

namespace controller;

class lang {

    use \traits\sendResponse;
    
    protected $container;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    function __invoke($request, $response, $args) {

        if (array_key_exists('lang', @$args)) {
            $lang = filter_var(@$args['lang'], FILTER_SANITIZE_STRING);

            $this->container->lang->setLang($lang);
        }
        
        $response = $response->withRedirect($this->container->router->pathFor('index'), 301);
        
        return $response;
    }
}