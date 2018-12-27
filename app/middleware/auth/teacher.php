<?php

namespace middleware\auth;

class teacher extends \sup\middleware {

    public function __invoke($request, $response, $next) {
        
        if ($this->container->auth->user->level(ROLE_TEACHER))
            return $next($request, $response);
        else if ($this->container->auth->canBecome(ROLE_TEACHER))
            return $next($request, $response);
        else
            return $this->container->get('notFoundHandler')($request, $response);
    }
}