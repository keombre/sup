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

        $modules = [];

        foreach ($this->container->base->modules->getInstalled() as $module)
            if ($module->isEnabled())
                $modules[] = $module;

        $this->container->view->setTemplatePath(__DIR__ . '/../../templates/layout/');
        $response = $this->sendResponse($request, $response, "dashboard.phtml", [
            "active" => $active,
            "site" => $res->getBody(),
            "modules" => $modules
        ]);
        return $response;
    }
}
