<?php declare(strict_types=1);

namespace SUP;

abstract class Controller {

    use \traits\sendResponse;
    
    protected $container;
    protected $db;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
        $this->db = $this->container->db;
    }

    abstract function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, $args);

    protected function sanitizePost(\Slim\Http\Request $request, string $queryField, int $sanitize) {
        $data = $request->getParsedBody();

        if (!\array_key_exists($queryField, $data))
            return null;
        else
            return \filter_var($data[$queryField], $sanitize);
    }

    protected function sanitizePostArray(\Slim\Http\Request $request, string $queryField, string $sanitize) {
        $data = $request->getParsedBody();

        if (!\array_key_exists($queryField, $data))
            return null;
        else
            return \filter_var_array($data[$queryField], $sanitize);
    }
}
