<?php

namespace middleware;

class dashboard {

    use \traits\sendResponse;

    protected $container;
    
    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    public function __invoke($request, $response, $next) {
        
        $res = $next($request, new \Slim\Http\Response);
        if ($res->getStatusCode() != 200)
            return $res;

        $response = $this->sendResponse($request, $response, "dashboard.phtml", [
            "home" => "Not implemented",
            "admissions" => "Not implemented",
            "subjects" => "Not implemented",
            "lists" => $res->getBody()
        ]);
        return $response;
    }
}