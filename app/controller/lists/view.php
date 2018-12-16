<?php

namespace controller\lists;

class view {
    
    use \traits\sendResponse;

    protected $container;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    function __invoke($request, $response, $args) {
        $level = $this->container->auth->user['level'];
        if ($level == 0) {
            $id = $this->container->auth->user['id'];

            $listgroups = $this->container->db->select("listgroups", "*", ["user" => $id]);
            
            $response = $this->sendResponse($request, $response, "lists/view.phtml", ["lists" => $listgroups]);
        } else if ($level == 1) {
            $response->writeBody("kitten");
        } else if ($level == 2) {
            $books = $this->container->db->select("books", "*");

            $response = $this->sendResponse($request, $response, "lists/manage.phtml", ["books" => $books]);
        }

        return $response;
    }
}