<?php

namespace controller\lists\admin;

class settings {
    
    use \traits\sendResponse;

    protected $container;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    function __invoke($request, $response, $args) {
        
        $title  = filter_var(@$data['edit'],  FILTER_SANITIZE_STRING);
        $author = filter_var(@$data['validate'], FILTER_SANITIZE_STRING);
        $region = filter_var(@$data['draw'], FILTER_SANITIZE_STRING);
        $genere = filter_var(@$data['active'], FILTER_SANITIZE_STRING);

        $response = $this->sendResponse($request, $response, "lists/admin/manage.phtml", ["books" => $books]);

        return $response;
    }
}