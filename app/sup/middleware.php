<?php

namespace SUP;

abstract class middleware
{
    use \traits\sendResponse;

    protected $container;
    
    public function __construct(\Slim\Container $container)
    {
        $this->container = $container;
    }

    abstract public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, $next);
}
