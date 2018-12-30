<?php

namespace middleware\auth;

class level extends \sup\middleware {
    private $level;
    
    function __construct(\Slim\Container $container, $level) {
        parent::__construct($container);

        $this->level = $level;
    }

    public function __invoke($request, $response, $next) {
        
        if (
            $this->container->auth->logged() && 
            $this->container->auth->getUser()->is($this->level)
        )
            return $next($request, $response);
        return $response->withRedirect($this->container->router->pathFor('index'), 301);
    }
}
