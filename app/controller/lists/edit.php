<?php

namespace controller\lists;

class edit {
    
    use \traits\sendResponse;

    protected $container;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    function __invoke($request, $response, $args) {

        if ($request->isGet()) {
            
            $listbooks = [];
            
            if (array_key_exists('id', @$args)) {
                $id = filter_var(@$args['id'], FILTER_SANITIZE_STRING);
                if (!$this->container->db->has("listgroups", ["id" => $id, "user" => $this->container->auth->user['id']])) {
                    $this->redirectWithMessage($response, 'dashboard', "error", ["Kánon nenalezen"]);
                }

                $listbooks = $this->container->db->select("lists", ["[>]listgroups" => ["list" => "id"]], "lists.book", ["listgroups.user" => $this->container->auth->user['id']]);
            }

            $books = [];
            $tmpbooks = [];

            foreach ($this->container->db->select("books", "*") as $book) {
                if (!array_key_exists($book['region'], $books))
                    $books[$book['region']] = [];
                $books[$book['region']][$book['id']] = $book;
                $tmpbooks[$book['id']] = $book;
            }

            $list = [];
            foreach ($listbooks as $book) {
                if (!array_key_exists($tmpbooks[$book]['region'], $list))
                    $list[$tmpbooks[$book]['region']] = [];
                array_push($list[$tmpbooks[$book]['region']], $book);
            }
            
            $regions = [];
            foreach ($this->container->db->select("regions", "*") as $region) {
                $regions[$region['id']] = $region['name'];
            }

            $generes = [];
            foreach ($this->container->db->select("generes", "*") as $genere) {
                $generes[$genere['id']] = $genere['name'];
            }

            $this->sendResponse($request, $response, "lists/edit.phtml", [
                "list" => $list,
                "books" => $books,
                "regions" => $regions,
                "generes" => $generes
            ]);

        } elseif ($request->isPut()) {
            null;
        }

        return $response;
    }
}
