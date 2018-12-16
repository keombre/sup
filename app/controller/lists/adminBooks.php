<?php

namespace controller;

class adminBooks {
    
    use \traits\sendResponse;
    
    protected $container;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    function __invoke($request, $response, $args) {

        $data = $request->getParsedBody();

        if ($request->isPut()) {

            $title  = filter_var(@$data['title'],  FILTER_SANITIZE_STRING);
            $author = filter_var(@$data['author'], FILTER_SANITIZE_STRING);
            $region = filter_var(@$data['region'], FILTER_SANITIZE_STRING);
            $genere = filter_var(@$data['genere'], FILTER_SANITIZE_STRING);

            $this->container->db->insert("books", ["name" => $title, "author" => $author, "region" => $region, "genere" => $genere]);
            $this->redirectWithMessage($response, 'dashboard', "status", ["Kniha přidána", ""]);
            
        } else if ($request->isDelete()) {
            $id  = filter_var(@$data['id'],  FILTER_SANITIZE_STRING);

            if ($this->container->db->has("books", ["id" => $id])) {
                $this->container->db->delete("books", ["id" => $id]);
                $this->container->db->delete("lists", ["book" => $id]);
                $this->redirectWithMessage($response, 'dashboard', "status", ["Kniha odebrána", ""]);
            } else {
                $this->redirectWithMessage($response, 'dashboard', "error", ["Chyba!", "Kniha nenalezena!"]);
            }
        }

        return $response;
    }
}