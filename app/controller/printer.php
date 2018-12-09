<?php

namespace controller;

class printer {

    use \traits\sendResponse;
    
    protected $container;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    function __invoke($request, $response) {
        if ($this->container->db->has("users", ["AND" => ["id" => $this->container->auth->user['id'], "state" => 0]])) {
            $this->container->db->update("users", ["state" => 1], ["id" => $this->container->auth->user['id']]);
        }
        $this->sendResponse($request, $response, "printer.phtml");

        return $response;
    }
}