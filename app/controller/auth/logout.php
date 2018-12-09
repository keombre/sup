<?php

namespace controller\auth;

class logout {
    
    protected $container;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    function __invoke($request, $response) {

        $this->container->auth->logout();

        return $response->withRedirect($this->container->router->pathFor('index'), 301);
    }
}