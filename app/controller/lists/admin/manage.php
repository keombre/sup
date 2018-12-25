<?php

namespace controller\lists\admin;

final class manage extends upload {

    function __invoke($request, $response, $args) {

        $version = filter_var($args['id'], FILTER_SANITIZE_STRING);
        
        if (!$this->db->has('lists_versions', ["id" => $version]))
            return $this->redirectWithMessage($response, 'lists', "error", ["Verze nenalezena"]);
        
        $status = null;
        if ($request->isPut()) {
            $parsed = parent::__invoke($request, $response, $args);
        
            if (!is_array($parsed))
                return $parsed;

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
            $this->db->delete("lists_books", ["version" => $version]);
            $this->db->insert("lists_books", $save);
            return $this->redirectWithMessage($response, 'lists-admin-manage', "status", [count($save) . " knih nahráno"], ["id" => $version]);
        }
        
        $name = $this->db->get('lists_versions', 'name', ["id" => $version]);
        $books = $this->db->select("lists_books", "*", ["version" => $version]);
        $response = $this->sendResponse($request, $response, "lists/admin/manage.phtml", [
            "books" => $books,
            "version" => $version,
            "name" => $name,
            "status" => $status
        ]);

        return $response;
    }
}