<?php

namespace controller\modules;

class view extends \SUP\controller {

    function __invoke($request, $response, $args) {

        return $this->sendResponse($request, $response, "modules/view.phtml", [
            "local" => $this->container->modules->getInstalled(),
            "remote" => $this->container->modules->getRemote()
        ]);
    }
}
