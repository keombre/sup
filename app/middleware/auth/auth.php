<?php

namespace middleware\auth;

class auth extends \sup\middleware {

    public function __invoke($request, $response, $next) {
        
        if ($this->container->auth->user->logged()) {
            return $next($request, $response);
        } else {
            return $response->withRedirect($this->container->router->pathFor('index'), 301);
        }
    }
}