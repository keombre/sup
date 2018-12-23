<?php

namespace controller\lists;

class view {
    
    use \traits\sendResponse;

    protected $container;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    function __invoke($request, $response, $args) {
        if ($this->container->auth->user->level(ROLE_STUDENT)) {
            $id = $this->container->auth->user->getInfo('id');

            $listgroups = $this->container->db->select("lists_main", "*", ["user" => $id]);
            
            $response = $this->sendResponse($request, $response, "lists/view.phtml", ["lists" => $listgroups]);

        } else if ($this->container->auth->user->level(ROLE_TEACHER)) {
            $response->getBody()->write("kitten");

        } else if ($this->container->auth->user->level(ROLE_ADMIN)) {
            $books = $this->container->db->select("lists_books", "*");

            $response = $this->sendResponse($request, $response, "lists/manage.phtml", ["books" => $books]);
        }

        return $response;
    }
}