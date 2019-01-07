<?php

namespace middleware\auth;

class teacher extends \SUP\middleware {

    public function __invoke($request, $response, $next) {
        
        if ($this->container->auth->getUser()->is(ROLE_TEACHER))
            return $next($request, $response);
        else if ($this->container->auth->getUser()->become(ROLE_TEACHER))
            return $next($request, $response);
        else
            return $this->container->get('notFoundHandler')($request, $response);
    }
}