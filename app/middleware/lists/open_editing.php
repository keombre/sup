<?php

namespace middleware\lists;

class open_editing {

    protected $container;
    
    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    public function __invoke($request, $response, $next) {
        
        if ($this->container->auth->user->level([ROLE_TEACHER, ROLE_ADMIN])) {
            return $next($request, $response);
        } else if (!$this->container->db->get("lists_settings", "open_editing")) {
            return $response->withRedirect($this->container->router->pathFor('lists'), 301); 
        } else {
            return $next($request, $response);
        }
    }
}