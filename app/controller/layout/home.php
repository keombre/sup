<?php

namespace controller\layout;

class home extends \sup\controller {

    function __invoke($request, $response, $args) {
        if ($this->container->auth->user->level(ROLE_ADMIN)) {
            return $this->sendResponse($request, $response, "layout/dashboard.phtml", [
                "active" => "home",
                "site" => "<h1>Upozornění pro správce:</h1><h3><b>API a GUI není fixní a může se kdykoliv změnit!</b></h3>Pečlivě čtěte všechny popisky a nespoléhejte na předešlou znalost aplikace."
            ]);
        } else {
            return $this->sendResponse($request, $response, "layout/dashboard.phtml", [
                "active" => "home",
                "site" => "Home dashboard"
            ]);
        }
    }
}
