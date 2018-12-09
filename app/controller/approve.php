<?php

namespace controller;

class approve {
    
    use \traits\sendResponse;
    
    protected $container;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    function __invoke($request, $response, $args) {

        $data = $request->getParsedBody();
        
        $id = filter_var(@$data['id'], FILTER_SANITIZE_STRING);
        
        if ($id < 10000 || $id > 99999) {
            $this->redirectWithMessage($response, 'dashboard', "error", ["Chyba!", "Kánon nenalezen!"]);
        } else if ($this->container->db->has("users", ["id" => $id])) {
            if ($this->container->db->get("users", "state", ["id" => $id]) == "0") {
                $this->redirectWithMessage($response, 'dashboard', "error", ["Chyba!", "Kánon neuzavřen!"]);
            } else if ($this->container->db->get("users", "state", ["id" => $id]) == "2") {
                $this->redirectWithMessage($response, 'dashboard', "error", ["Chyba!", "Kánon již schválen!"]);
            } else {
                $this->container->db->update("users", ["state" => "2"], ["id" => $id]);
                $this->redirectWithMessage($response, 'dashboard', "status", ["Kánon schválen", ""]);
            }
        } else {
            $this->redirectWithMessage($response, 'dashboard', "error", ["Chyba!", "Kánon nenalezen!"]);
        }

        return $response;
    }
}