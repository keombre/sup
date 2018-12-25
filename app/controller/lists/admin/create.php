<?php

namespace controller\lists\admin;

class create extends upload {

    function __invoke($request, $response, $args) {
        $data = $request->getParsedBody();

        $name = filter_var(@$data['name'], FILTER_SANITIZE_STRING);
        if ($this->db->has("lists_versions", ["name" => $name]))
            return $this->redirectWithMessage($response, 'lists', "error", ["Období " . $name . " již existuje"]);
        
        $parsed = parent::__invoke($request, $response, $args);
        
        if (!is_array($parsed))
            return $parsed;

        $this->db->insert("lists_versions", ["name" => $name]);
        $version = $this->db->id();

        $save = [];
        foreach ($parsed as $entry) {
            array_push($save, [
                "name"    => $entry[2],
                "author"  => $entry[1],
                "region"  => intval($entry[0]),
                "genere"  => intval($entry[3]),
                "version" => $version
            ]);
        }
        $this->db->insert("lists_books", $save);
        $this->redirectWithMessage($response, 'lists', "status", [count($save) . " knih nahráno"]);
        return $response;
    }

}