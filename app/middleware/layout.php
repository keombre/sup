<?php

namespace middleware;

class layout extends \sup\middleware {

    use \traits\sendResponse;

    public function __invoke($request, $response, $next) {
        
        $routeName = $request->getAttributes()['route']->getName();
        $active = "home";
        if ($routeName != NULL)
            $active = explode("-", $routeName)[0];
        
        $res = $next($request, new \Slim\Http\Response);
        if ($res->getStatusCode() != 200)
            return $res;
        
        $this->container->view->setTemplatePath(__DIR__ . '/../base/layout/templates/');
        $response = $this->sendResponse($request, $response, "dashboard.phtml", [
            "active" => $active,
            "site" => $res->getBody()
        ]);
        return $response;
    }
}
