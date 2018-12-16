<?php

namespace controller\dashboard;

class home {
    
    use \traits\sendResponse;
    
    protected $container;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    function __invoke($request, $response, $args) {

        $response = $this->sendResponse($request, $response, "layout/dashboard.phtml", [
            "active" => "home",
            "site" => "Home - Not implemented"
        ]);

        return $response;
    }
}