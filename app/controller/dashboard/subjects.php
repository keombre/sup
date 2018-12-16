<?php

namespace controller\dashboard;

class subjects {
    
    use \traits\sendResponse;
    
    protected $container;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    function __invoke($request, $response, $args) {

        $response = $this->sendResponse($request, $response, "layout/dashboard.phtml", [
            "active" => "subjects",
            "site" => "Nemáte přístup k volbě předmětů"
        ]);

        return $response;
    }
}