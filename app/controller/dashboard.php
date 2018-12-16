<?php

namespace controller;

class dashboard {
    
    use \traits\sendResponse;
    
    protected $container;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    function __invoke($request, $response, $args) {

        $level = $this->container->auth->user['level'];
        if ($level == 0) {
            $response = $this->sendResponse($request, $response, "dashboard.phtml", [
                "home" => "Not implemented",
                "admissions" => "Not implemented",
                "subjects" => "Not implemented",
                "lists" => $this->container->router->getNamedRoute('lists-view')->run($request, new \Slim\Http\Response)->getBody()
            ]);
        } else if ($level == 1) {
            $response = $this->sendResponse($request, $response, "dashboard.phtml", [
                "home" => "Not implemented",
                "admissions" => "Not implemented",
                "subjects" => "Not implemented",
                "lists" => "Not implemented"
            ]);
        } else if ($level == 2) {
            $response = $this->sendResponse($request, $response, "dashboard.phtml", [
                "home" => "Not implemented",
                "admissions" => "Not implemented",
                "subjects" => "Not implemented",
                "lists" => $this->container->router->getNamedRoute('lists-view')->run($request, new \Slim\Http\Response)->getBody()
            ]);
        }

        return $response;
    }
}