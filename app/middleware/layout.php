<?php

namespace middleware;

class layout extends \SUP\middleware {

    use \traits\sendResponse;

    public function __invoke($request, $response, $next) {
        
        $active = "home";
        if ($this->container->settings->has('module_name'))
        $active = $this->container->settings->get('module_name');
        
        $res = $next($request, new \Slim\Http\Response);
        if (
            $res->getStatusCode() != 200 ||
            count($res->getHeader('Content-Type')) > 0
        )
            return $res;

        $modules = [];

        foreach ($this->container->base->modules->getInstalled() as $module)
            if ($module->isEnabled())
                $modules[] = $module;

        $this->container->view->setTemplatePath(__DIR__ . '/../../templates/layout/');
        $response = $this->sendResponse($request, $response, "site.phtml", [
            "active" => $active,
            "site" => $res->getBody(),
            "modules" => $modules
        ]);
        return $response;
    }
}
