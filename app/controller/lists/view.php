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
        $response->getBody()->write("kitten");
    }

    public function admin($request, &$response, $args) {
        $versions = $this->container->db->select("lists_versions", "*");

        $response = $this->sendResponse($request, $response, "lists/admin/dash.phtml", [
            "versions" => $versions,
            "settings" => $this->settings
        ]);
    }
}
