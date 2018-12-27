<?php

require '../vendor/autoload.php';

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = true;
$config['db']['type']   = "sqlite";
$config['db']['file'] = "../db/database.db";
$config['name'] = "Kánon";
$config['path'] = "/maturita";
$config['upload_directory'] = __DIR__ . "/../uploads";
$config['lang_directory'] = __DIR__ . "/../langs";
$config['public']['version'] = '0.1.4_dev';

session_start();

$app = new \Slim\App(["settings" => $config]);
$container = $app->getContainer();

//remove trailing slash
$app->add(function (Request $request, Response $response, callable $next) {
    $uri = $request->getUri();
    $path = $uri->getPath();
    if ($path != '/' && substr($path, -1) == '/' && $path != $this->get('settings')['path'] . '/') {
        $uri = $uri->withPath(substr($path, 0, -1));
        
        if($request->getMethod() == 'GET') {
            return $response->withRedirect((string)$uri, 301);
        }
        else {
            return $next($request->withUri($uri), $response);
        }
    }

    return $next($request, $response);
});

$container['auth'] = function($c) {
    $auth = new \auth($c);
    return $auth;
};

$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};

$container['lang'] = function ($c) {
    $lang = new lang($c);
    return $lang;
};

$container->lang->loadLangs();
$container->lang->getLang();

$container['view'] = function ($container) {
    $templateVariables = [
        "router" => $container->router,
        "auth" => $container->auth,
        "lang" => $container->lang,
        "ROOT_PATH" => $container->get('settings')['path'],
        "config" => $container['settings']['public']
    ];
    return new \Slim\Views\PhpRenderer('../templates/', $templateVariables);
};

$container['csrf'] = function ($c) {
    $guard = new \Slim\Csrf\Guard();
    $guard->setFailureCallable(function ($request, $response, $next) {
        $request = $request->withAttribute("csrf_status", false);
        return $next($request, $response);
    });
    return $guard;
};

$app->add($container->csrf);
$app->add(\middleware\csrf::class);

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $medoo = new \Medoo\Medoo([
        'database_type' => $db['type'],
        'database_file' => $db['file']
    ]);
    return $medoo;
};

$container['seed'] = function ($c) {
    $seed = new \database\seed($c);
    return $seed;
};

$container->seed->update();

new routes($app);

$app->run();