<?php

namespace middleware\auth;

class autologin extends \SUP\middleware {

    public function __invoke($request, $response, $next) {
        
        if ($this->container->auth->logged()) {
            return $response->withRedirect($this->container->router->pathFor('dashboard'), 301);
        } else {
            return $next($request, $response);
        }
    }
}