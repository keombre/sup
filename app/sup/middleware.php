<?php

namespace sup;

abstract class middleware {

    protected $container;
    
    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    abstract function __invoke(\Slim\Request $request, \Slim\Response $response, $next);
}
