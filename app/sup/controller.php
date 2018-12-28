<?php

namespace sup;

abstract class controller {

    use \traits\sendResponse;
    
    protected $container;
    protected $db;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
        $this->container->router->setBasePath('/' . $container->settings['module_name']);
        $this->db = $this->container->db;
    }

    abstract function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, $args);
}
