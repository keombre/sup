<?php

namespace controller\lists;

class view {
    
    use \traits\sendResponse;

    protected $container;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    function __invoke($request, $response, $args) {

        $settings = $this->container->db->get("lists_settings", "*");

        if ($this->container->auth->user->level(ROLE_STUDENT)) {
            $id = $this->container->auth->user->getInfo('id');

            if ($settings['open_editing']) {
                $listgroups = $this->container->db->select("lists_main", "*", ["user" => $id], ["version" => $settings['active_version']]);
                
                $response = $this->sendResponse($request, $response, "lists/view.phtml", ["lists" => $listgroups]);
            } else {
                $response->getBody()->write("Nemáte přístup k tvorbě kánonu");
            }
        } else if ($this->container->auth->user->level(ROLE_TEACHER)) {
            $response->getBody()->write("kitten");

        } else if ($this->container->auth->user->level(ROLE_ADMIN)) {
            $versions = $this->container->db->select("lists_versions", "*");
            $settings = $this->container->db->select("lists_settings", "*");

            $response = $this->sendResponse($request, $response, "lists/admin/dash.phtml", [
                "versions" => $versions,
                "settings" => @$settings[0]
            ]);
        }

        return $response;
    }
}