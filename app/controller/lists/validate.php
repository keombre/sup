<?php

namespace controller\lists;

class validate {
    
    use \traits\sendResponse;

    protected $container;
    private $listID = false;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
        $this->userID = $this->container->auth->user->getInfo('id');
    }

    function __invoke($request, $response, $args) {
        $this->getListID($args);

        if (!$this->listID)
            return $this->redirectWithMessage($response, 'lists', "error", ["Kánon nenalezen"]);

        $state = $this->container->db->get('lists_main', 'state', ['id' => $this->listID]);

        if ($state == 0) {
            if (!$this->validate($response))
                return $response;
        }
        
        if ($request->isPut()) {
            if ($state == 0) {
                $this->container->db->update("lists_main", ["state" => 1], ["id" => $this->listID]);
                $state = 1;
            }
        }

        $this->preview($request, $response, $state != 0);
        return $response;
    }

    private function preview($request, &$response, $print = false) {
        $books = [];
        foreach ($this->container->db->select("lists_books", "*") as $book)
            $books[$book['id']] = $book;

        $list = [];
        foreach ($this->container->db->select("lists_lists", "book", ["list" => $this->listID]) as $book)
            array_push($list, $books[$book]);

        $this->sendResponse($request, $response, "lists/preview.phtml", [
            "list" => $list,
            "listID" => $this->listID,
            "print" => $print
        ]);
    }

    private function validate(&$response) {
        $list = $this->container->db->select("lists_lists", "book", ["list" => $this->listID]);
        
        if (count($list) != 20) {
            $this->redirectWithMessage($response, 'lists-edit', "error", ["Nezvolili jste 20 knih"], ["id" => $this->listID]);
            return false;
        }
        
        $books = [];
        foreach ($this->container->db->select("lists_books", "*") as $book) {
            $books[$book['id']] = $book;
        }

        $genereCounter = [];
        $regionCounter = [];
        $authorCounter = [];

        $genereInfo = [];
        $regionInfo = [];

        foreach ($this->container->db->select("lists_generes", "*") as $genere) {
            $genereCounter[$genere['id']] = 0;
            $genereInfo[$genere['id']] = $genere;
        }

        foreach ($this->container->db->select("lists_regions", "*") as $region) {
            $regionCounter[$region['id']] = 0;
            $regionInfo[$region['id']] = $region;
        }

        foreach ($list as $book) {
            if (!array_key_exists($book, $books)) {
                $this->container->db->delete("lists_lists", ["list" => $this->listID, "book" => $book]);
            }
            
            if (!array_key_exists($books[$book]['author'], $authorCounter))
                $authorCounter[$books[$book]['author']] = 0;
            
            $genereCounter[$books[$book]['genere']]++;
            $regionCounter[$books[$book]['region']]++;
            $authorCounter[$books[$book]['author']]++;
        }

        $message = "";

        foreach ($authorCounter as $author => $count) {
            if ($count > 2 && $author != "") {
                $message .= $author . "<br />";
            }
        }

        if ($message == "") {

            $regionMessage = $this->checkCount($regionInfo, $regionCounter);
            $this->checkCount($genereInfo, $genereCounter);

            if ($regionMessage != "") {
                $message .= "<h5><b>Období:</b></h5>" . PHP_EOL;
                $message .= $regionMessage;
            }
            if ($genereMessage != "") {
                if ($regionMessage != "")
                    $message .= "<hr>";
                
                $message .= "<h5><b>Žánry:</b></h5>" . PHP_EOL;
                $message .= $genereMessage;
            }
            if ($message != "") {
                $this->redirectWithMessage($response, 'lists-edit', "message", ["title" => "Nezvolili jste dostatečný počet děl", "message" => $message], ["id" => $this->listID]);
                return false;
            } else {
                return true;
            }
        } else {
            $this->redirectWithMessage($response, 'lists-edit', "message", ["title" => "Máte více než dvě díla od následujících autorů", "message" => $message], ["id" => $this->listID]);
            return false;
        }
    }

    private function getListID($args) {
        if (array_key_exists('id', @$args)) {
            $id = filter_var(@$args['id'], FILTER_SANITIZE_STRING);
            $version = $this->container->db->get("lists_settings", "active_version");
            if ($this->container->db->has("lists_main", ["id" => $id, "user" => $this->userID, "version" => $version]))
                $this->listID = $id;
        } else
            $this->listID = true;
    }

    private function checkCount($info, $counter) {
        $ret = true;
        $message = "";
        foreach ($counter as $id => $count) {
            if (!is_null($info[$id]['min']) && $info[$id]['min'] > $count) {
                $message .= "<span class='text-danger'><span class='glyphicon glyphicon-remove'></span> " . $info[$id]['name'] . " (<b>" . $info[$id]['min'] . " ≤</b> " . $count . " ≤ " . $info[$id]['max'] . ")</span><br />" . PHP_EOL;
                $ret = false;
            } elseif (!is_null($info[$id]['max']) && $info[$id]['max'] < $count) {
                $message .= "<span class='text-danger'><span class='glyphicon glyphicon-remove'></span> " . $info[$id]['name'] . " (" . $info[$id]['min'] . " ≤ " . $count . " <b>≤ " . $info[$id]['max'] . "</b>)</span><br />" . PHP_EOL;
            } else
                $message .= "<span class='text-success'><span class='glyphicon glyphicon-ok'></span> " . $info[$id]['name'] . " (" . $info[$id]['min'] . " ≤ " . $count . " ≤ " . $info[$id]['max'] . ")</span><br />" . PHP_EOL;
        }
        return $ret ? "" : $message;
    }
}
