<?php

namespace controller\lists;

class view extends lists {

    public function student($request, &$response, $args) {
        if ($this->settings['open_editing']) {
            $listgroups = $this->container->db->select("lists_main", "*", [
                "user" => $this->userID,
                "version" => $this->settings['active_version']
            ]);
            
            $response = $this->sendResponse($request, $response, "lists/view.phtml", ["lists" => $listgroups]);
        } else {
            $response->getBody()->write("Nemáte přístup k tvorbě kánonu");
        }
    }

    public function teacher($request, &$response, $args) {
        $books = [];
        foreach ($this->db->select('lists_books', '*', ['version' => $this->settings['active_version']]) as $book)
            $books[$book['id']] = $book;
        
        $count = array_count_values($this->db->select('lists_lists', ['[>]lists_main' => ['list' => 'id']], 'book', ['lists_main.version' => $this->settings['active_version'], 'lists_main.state' => 2]));
        arsort($count);

        $response = $this->sendResponse($request, $response, "lists/teacher/dash.phtml", [
            "books" => $books,
            "count" => $count,
            "allowAccepting" => $this->settings['open_accepting'] == 1
        ]);
    }

    public function admin($request, &$response, $args) {
        $versions = $this->container->db->select("lists_versions", "*");

        $response = $this->sendResponse($request, $response, "lists/admin/dash.phtml", [
            "versions" => $versions,
            "settings" => $this->settings
        ]);
    }
}
