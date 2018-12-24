<?php

namespace controller\lists\admin;

class manage {
    
    use \traits\sendResponse;

    protected $container;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    function __invoke($request, $response, $args) {

        $books = $this->container->db->select("lists_books", "*");

        $response = $this->sendResponse($request, $response, "lists/admin/manage.phtml", ["books" => $books]);

        return $response;
    }
}