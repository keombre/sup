<?php

namespace controller\lists\admin;

class create extends upload {

    function __invoke() {
        $data = $request->getParsedBody();

        $name = filter_var(@$data['name'], FILTER_SANITIZE_STRING);
        if ($this->container->db->has("lists_versions", ["name" => $name]))
            return $this->redirectWithMessage($response, 'lists', "error", ["Období " . $name . " již existuje"]);
        
        $response = parrent::__invoke($request, $response);

        $this->container->db->insert("lists_versions", ["name" => $name]);
        $version = $this->container->db->id();

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
        $this->container->db->insert("lists_books", $save);
        $this->redirectWithMessage($response, 'lists', "status", [count($save) . " knih nahráno"]);
    }

}