<?php

namespace middleware;

class level {

    protected $container;
    private $level;
    
    function __construct(\Slim\Container $container, $level) {
        $this->container = $container;
        $this->level = $level;
    }

    public function __invoke($request, $response, $next) {
        
        if ($this->container->auth->logged()) {
            if (is_array($this->level)) {
                if (in_array($this->container->auth->user['level'], $this->level))
                    return $next($request, $response);
            } else if ($this->container->auth->user['level'] == $this->level) {
                return $next($request, $response);
            }
        }
        return $response->withRedirect($this->container->router->pathFor('index'), 301);
    }
}
