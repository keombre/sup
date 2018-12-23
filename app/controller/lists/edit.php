<?php

namespace controller\lists;

class edit {
    
    use \traits\sendResponse;

    protected $container;
    private $userID;
    private $listID = false;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
        $this->userID = $this->container->auth->user->getInfo('id');
    }

    function __invoke($request, $response, $args) {

        $this->getListID($args);

        if ($request->isGet()) {
            $this->render($request, $response, $args);

        } else if ($request->isPut()) {

            $createNew = false;
            
            $data = $request->getParsedBody();
            $books = array_unique(filter_var_array(@$data['books'], FILTER_SANITIZE_STRING));
            
            if ($this->listID === false)
                return $this->redirectWithMessage($response, 'lists-edit', "error", ["Kánon nenalezen"]);
            
            if (!count($books)) {
                if ($this->listID === true)
                    return $this->redirectWithMessage($response, 'lists-edit', "error", ["Žádné knihy nezvoleny"]);
                else
                    return $this->redirectWithMessage($response, 'lists-edit', "error", ["Žádné knihy nezvoleny"], ['id' => $this->listID]);
            }
            
            if ($this->listID === true) {
                $this->generateListID();
                $createNew = true;
            }

            $list = $this->container->db->select("lists_books", "id");
            
            foreach ($this->container->db->select("lists_lists", "book", ["list" => $this->listID]) as $remove) {
                if (($index = array_search($remove, $list)) !== false)
                    unset($list[$index]);
            }

            $save = [];
            foreach ($books as $book) {
                if (in_array($book, $list))
                    array_push($save, ["list" => $this->listID, "book" => $book]);
            }

            if (!count($save)) {
                if ($this->removeEmptyList())
                    return $this->redirectWithMessage($response, 'lists', "error", ["Chyba při vytváření kánonu"]);
                else
                    return $this->redirectWithMessage($response, 'lists-edit', "error", ["Chyba při ukládání knih"], ["id" => $this->listID]);
            }
            
            $this->container->db->insert("lists_lists", $save);
            if ($createNew) {
                $this->container->db->insert("lists_main", [
                    "id" => $this->listID,
                    "user" => $this->userID,
                    "created" => time()
                ]);
                return $response->withRedirect($this->container->router->pathFor('lists-edit', ['id' => $this->listID]), 301);
            } else
                $this->render($request, $response, $args);

        } else if ($request->isDelete()) {
            $data = $request->getParsedBody();
            $books = array_unique(filter_var_array(@$data['books'], FILTER_SANITIZE_STRING));

            if ($this->removeEmptyList())
                return $response->withRedirect($this->container->router->pathFor('lists'), 301);
            
            if (!is_numeric($this->listID)) {
                if (!count($books))
                    return $response->withRedirect($this->container->router->pathFor('lists'), 301);
                else
                    return $this->redirectWithMessage($response, 'lists', "error", ["Kánon nenalezen"]);
            }

            if (!count($books))
                return $this->redirectWithMessage($response, 'lists-edit', "error", ["Žádné knihy nezvoleny"], ['id' => $this->listID]);
            
            $this->container->db->delete("lists_lists", ["list" => $this->listID, "OR" => ["book" => $books]]);
            
            if ($this->removeEmptyList())
                return $this->redirectWithMessage($response, 'lists', "status", ["Kánon smazán"]);
            else
                $this->render($request, $response, $args);

        } else if ($request->isPost()) {
            // action: finish
        }

        return $response;
    }

    private function render(&$request, &$response, $args) {
        $listbooks = [];

        if ($this->listID === false)
            return $this->redirectWithMessage($response, 'lists', "error", ["Kánon nenalezen"]);
        
        if (is_numeric($this->listID))
            $listbooks = $this->container->db->select("lists_lists", "book", ["list" => $this->listID]);

        $allbooks = [];
        foreach ($this->container->db->select("lists_books", "*") as $book)
            $allbooks[$book['id']] = $book;
        
        $books = [];
        $list = [];

        foreach ($listbooks as $book) {
            if (array_key_exists($book, $allbooks)) {
                if (!array_key_exists($allbooks[$book]['region'], $list))
                    $list[$allbooks[$book]['region']] = [];
                $list[$allbooks[$book]['region']][$book] = $allbooks[$book];
                unset($allbooks[$book]);
            }
        }
        
        foreach ($allbooks as $book) {
            if (!array_key_exists($book['region'], $books))
                $books[$book['region']] = [];
            $books[$book['region']][$book['id']] = $book;
        }

        $regions = array_column($this->container->db->select("lists_regions", "*"), 'name', 'id');
        $generes = array_column($this->container->db->select("lists_generes", "*"), 'name', 'id');
        $listLength = count($listbooks);
        
        $this->sendResponse($request, $response, "lists/edit.phtml", [
            "list" => $list,
            "books" => $books,
            "regions" => $regions,
            "generes" => $generes,
            "listLength" => $listLength,
            "listID" => $this->listID
        ]);
    }

    private function removeEmptyList() {
        if ($this->container->db->has('lists_main', ['AND' => ['user' => $this->userID, 'id' => $this->listID]])) {
            if (!$this->container->db->has('lists_lists', ['list' => $this->listID])) {
                $this->container->db->delete('lists_main', ['id' => $this->listID]);
                return true;
            }
        }
        return false;
    }

    private function getListID($args) {
        if (array_key_exists('id', @$args)) {
            $id = filter_var(@$args['id'], FILTER_SANITIZE_STRING);
            if ($this->container->db->has("lists_main", ["id" => $id, "user" => $this->userID]))
                $this->listID = $id;
        } else
            $this->listID = true;
    }

    private function generateListID() {
        do {
            $id = rand(100000, 999999);
        } while ($this->container->db->has("lists_main", ["id" => $id, "user" => $this->userID]));
        $this->listID = $id;
    }
}
