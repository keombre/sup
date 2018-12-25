<?php

namespace controller;

class index extends \sup\controller {

    function __invoke($request, $response, $args) {
        $this->sendResponse($request, $response, "index.phtml", ["next" => $request->getQueryParam("next")]);
        
        return $response;
    }
}