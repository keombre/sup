<?php

namespace middleware;

class registers1 {

    protected $container;
    
    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    public function __invoke($request, $response, $next) {

        if (
            array_key_exists('APP_PASS', $_SESSION) && is_string($_SESSION["APP_PASS"]) &&
            array_key_exists('APP_ID', $_SESSION) && is_string($_SESSION["APP_ID"])
        )
            return $next($request, $response);
        else
            return $response->withRedirect($this->container->router->pathFor('register-s1'), 301);
    }
}