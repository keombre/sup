<?php

namespace controller;

class books {
    
    use \traits\sendResponse;
    
    protected $container;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    function __invoke($request, $response, $args) {

        $data = $request->getParsedBody();
        
        $id = filter_var(@$data['id'], FILTER_SANITIZE_STRING);

        if ($request->isPut()) {

            if ($this->container->db->has("books", ["id" => $id])) {
                if ($this->container->db->has("lists", ["user" => $this->container->auth->user['id'], "book" => $id])) {
                    $this->redirectWithMessage($response, 'dashboard', "error", ["Chyba!", "Knihu již máte přidanou!"]);
                } else {
                    $this->container->db->insert("lists", ["user" => $this->container->auth->user['id'], "book" => $id]);
                    $this->redirectWithMessage($response, 'dashboard', "status", ["Kniha přidána", ""]);
                }
            } else {
                $this->redirectWithMessage($response, 'dashboard', "error", ["Chyba!", "Kniha nenalezena!"]);
            }

        } else if ($request->isDelete()) {
            if ($this->container->db->has("lists", ["user" => $this->container->auth->user['id'], "book" => $id])) {
                $this->container->db->delete("lists", ["user" => $this->container->auth->user['id'], "book" => $id]);
                $this->redirectWithMessage($response, 'dashboard', "status", ["Kniha odebrána", ""]);
            } else {
                $this->redirectWithMessage($response, 'dashboard', "error", ["Chyba!", "Kniha nenalezena!"]);
            }
        }

        return $response;
    }
}