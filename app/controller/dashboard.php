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

            $id = $this->container->auth->user['id'];

            $listgroups = $this->container->db->select("listgroups", "*", ["user" => $id]);
            
            $response = $this->sendResponse($request, $response, "dash/student.phtml", ["lists" => $listgroups]);
            
        } else if ($level == 1) {
            $books = $this->container->db->query(//fix me!!
                "SELECT books.name, books.author, COUNT(lists.book)
                 FROM books, lists, users
                 WHERE lists.book = books.id AND lists.user = users.id AND users.state = 2;");
            if ($books)
                $books = $books->fetchAll();
            $lists = $this->container->db->select("users", "*", ["state" => 2]);
            $response = $this->sendResponse($request, $response, "dash/teacher.phtml", [
                "books" => $books,
                "lists" => $lists
            ]);
        } else if ($level == 2) {
            $books = $this->container->db->select("books", "*");
            $users = $this->container->db->select("users", "*");
            $response = $this->sendResponse($request, $response, "dash/admin.phtml", [
                "books" => $books,
                "users" => $users
            ]);
        }

        return $response;
    }
}