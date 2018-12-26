<?php

namespace middleware\auth;

class student extends \sup\middleware {

    public function __invoke($request, $response, $next) {
        
        if ($this->container->auth->user->level(ROLE_STUDENT))
            return $next($request, $response);
        else
            return $response->withRedirect($this->container->router->pathFor('index'), 301);
    }
}