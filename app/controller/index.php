<?php

namespace controller;

class index {

    use \traits\sendResponse;
    
    protected $container;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    function __invoke($request, $response) {
        $this->sendResponse($request, $response, "index.phtml");
        
        return $response;
    }
}