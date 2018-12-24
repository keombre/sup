<?php

namespace controller\dashboard;

class subjects extends \sup\controller {
    
    function __invoke($request, $response, $args) {

        $response = $this->sendResponse($request, $response, "layout/dashboard.phtml", [
            "active" => "subjects",
            "site" => "Nemáte přístup k volbě předmětů"
        ]);

        return $response;
    }
}