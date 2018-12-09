<?php

namespace controller;

class lists {
    
    use \traits\sendResponse;
    
    protected $container;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    function __invoke($request, $response, $args) {
        
        $id = filter_var(@$args['id'], FILTER_SANITIZE_STRING);

        if ($this->container->db->has('lists', ["user" => $id])) {
            $books = $this->container->db->query(
                "SELECT books.name, books.author, books.region, books.genere, books.id
                 FROM books, lists
                 WHERE $id = lists.user AND lists.book = books.id;")->fetchAll();

            $user = $this->container->db->get("users", "*", ["id" => $id]);
            $response = $this->sendResponse($request, $response, "list.phtml", [
                "uname" => $user['name'],
                "class" => $user['class'],
                "books" => $books,
                "state" => $user['state']
            ]);
        } else {
            $this->redirectWithMessage($response, 'dashboard', "error", ["Chyba!", "Kánon nenalezen"]);
        }

        return $response;
    }
}