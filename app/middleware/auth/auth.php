<?php

namespace middleware\auth;

class auth extends \SUP\middleware {

    public function __invoke($request, $response, $next) {
        
        if ($this->container->auth->logged()) {
            return $next($request, $response);
        } else {
            return $response->withRedirect($this->container->router->pathFor('index', [], ["next" => base64_encode($request->getUri())]), 301);
        }
    }
}