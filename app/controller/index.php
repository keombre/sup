<?php

namespace controller;

class index extends \SUP\controller
{
    public function __invoke($request, $response, $args)
    {
        $this->sendResponse($request, $response, "index.phtml", ["next" => $request->getQueryParam("next")]);
        
        return $response;
    }
}
