<?php

namespace middleware\auth;

class student extends \sup\middleware {

    public function __invoke($request, $response, $next) {
        
        if ($this->container->auth->user->level(ROLE_STUDENT))
            return $next($request, $response);
        else if ($this->container->auth->canBecome(ROLE_STUDENT))
            return $next($request, $response);
        else
            return $this->container->get('notFoundHandler')($request, $response);
    }
}