<?php

namespace middleware\auth;

class admin {

    protected $container;
    
    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    public function __invoke($request, $response, $next) {
        
        if ($this->container->auth->logged() && $this->container->auth->user['level'] == 2) {
            return $next($request, $response);
        } else {
            return $response->withRedirect($this->container->router->pathFor('index'), 301);
        }
    }
}