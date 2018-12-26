<?php

namespace middleware\auth;

class admin extends \sup\middleware {

    public function __invoke($request, $response, $next) {
        
        if ($this->container->auth->user->level(ROLE_ADMIN))
            return $next($request, $response);
        else
            return $response->withRedirect($this->container->router->pathFor('index'), 301);
    }
}
