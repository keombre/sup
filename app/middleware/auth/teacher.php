<?php

namespace middleware\auth;

class teacher extends \sup\middleware {

    public function __invoke($request, $response, $next) {
        
        if ($this->container->auth->user->logged() && $this->container->auth->user['level'] >= 1) {
            return $next($request, $response);
        } else {
            return $response->withRedirect($this->container->router->pathFor('index'), 301);
        }
    }
}