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
        
        $list = $this->container->db->select("lists_lists", "book", ["list" => $this->listID]);
        
        if (count($list) != 20)
            return $this->redirectWithMessage($response, 'lists-edit', "error", ["Nezvolili jste správný počet knih"], ["id" => $this->listID]);
        
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

        $message = "Generes:<br />" . PHP_EOL;
        $message .= $this->checkCount($genereInfo, $genereCounter);
        $message .= "Regions:<br />" . PHP_EOL;
        $message .= $this->checkCount($regionInfo, $regionCounter);
        
        return $this->redirectWithMessage($response, 'lists-edit', "message", ["title" => "Kitten fart", "message" => $message], ["id" => $this->listID]);

    }

    private function getListID($args) {
        if (array_key_exists('id', @$args)) {
            $id = filter_var(@$args['id'], FILTER_SANITIZE_STRING);
            if ($this->container->db->has("lists_main", ["id" => $id, "user" => $this->userID]))
                $this->listID = $id;
        } else
            $this->listID = true;
    }

    private function checkCount($info, $counter) {
        $message = "";
        foreach ($counter as $id => $count) {
            if (!is_null($info[$id]['min']) && $info[$id]['min'] > $count)
                $message .= "<span class='text-danger'><span class='glyphicon glyphicon-remove'></span> " . $info[$id]['name'] . " (<b>" . $info[$id]['min'] . " ≤</b> " . $count . " ≤ " . $info[$id]['max'] . ")</span><br />" . PHP_EOL;
            elseif (!is_null($info[$id]['max']) && $info[$id]['max'] < $count)
                $message .= "<span class='text-danger'><span class='glyphicon glyphicon-remove'></span> " . $info[$id]['name'] . " (" . $info[$id]['min'] . " ≤ " . $count . " <b>≤ " . $info[$id]['max'] . "</b>)</span><br />" . PHP_EOL;
            else
                $message .= "<span class='text-success'><span class='glyphicon glyphicon-ok'></span> " . $info[$id]['name'] . " (" . $info[$id]['min'] . " ≤ " . $count . " ≤ " . $info[$id]['max'] . ")</span><br />" . PHP_EOL;
        }

        return $message;
    }

}