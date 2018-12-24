<?php

namespace sup;

abstract class controller {

    use \traits\sendResponse;
    
    protected $container;
    protected $db;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
        $this->db = $this->container->db;
    }

    abstract function __invoke(\Slim\Request $request, \Slim\Response $response, $args);
}
