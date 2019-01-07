<?php

namespace SUP;

abstract class middleware {

    use \traits\sendResponse;

    protected $container;
    
    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    abstract function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, $next);
}
