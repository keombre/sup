<?php

require '../vendor/autoload.php';

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = true;
$config['name'] = "SUP";
$config['lang_directory'] = __DIR__ . "/../lang";
$config['public']['version'] = '0.1.4_dev';

session_start();

$app = new \Slim\App(["settings" => $config]);

$app->add(function (Request $request, Response $response, callable $next) {
    $uri = $request->getUri();
    $path = $uri->getPath();
    if ($path != '/' && substr($path, -1) == '/' && $path != $this->get('settings')['path'] . '/') {
        $uri = $uri->withPath(substr($path, 0, -1));
        
        if ($request->getMethod() == 'GET')
            return $response->withRedirect((string)$uri, 301);
        else
            return $next($request->withUri($uri), $response);
    }
    return $next($request, $response);
});

$container = $app->getContainer();

$container['auth'] = function($c) {
    $auth = new \auth($c);
    return $auth;
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
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates/', $templateVariables);
};

$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};

$container['csrf'] = function ($c) {
    $guard = new \Slim\Csrf\Guard();
    $guard->setFailureCallable(function ($request, $response, $next) {
        $request = $request->withAttribute("csrf_status", false);
        return $next($request, $response);
    });
    return $guard;
};

$container['db'] = function ($c) {
    $medoo = new \Medoo\Medoo([
        'database_type' => 'sqlite',
        'database_file' => __DIR__ . '/../db/database.db'
    ]);
    return $medoo;
};
new \database\seed($container);

foreach (scandir(__DIR__ . '/../modules/') as $module)
    if (is_file(__DIR__ . '/../modules/' . $module . "/routes.php"))
        $app->any('/' . $module . '[/{params:.*}]', function ($request, $response) use ($module, $container) {
            createModule($module, __DIR__ . '/../modules/' . $module, '\\modules\\' . $module, $container);
        });

new routes($app);

$app->run();

function createModule($module, $path, $namespace, $globalContainer) {
    $app = new \Slim\App(['settings' => $globalContainer['settings']->all()]);
    $container = $app->getContainer();
    
    $container['auth']  = clone $globalContainer['auth'];
    $container['flash'] = clone $globalContainer['flash'];
    $container['lang']  = clone $globalContainer['lang'];
    $container['csrf']  = clone $globalContainer['csrf'];

    $container['view'] = function ($container) use ($path) {
        $templateVariables = [
            "router" => $container->router,
            "auth" => $container->auth,
            "lang" => $container->lang,
            "ROOT_PATH" => $container->get('settings')['path'],
            "config" => $container['settings']['public']
        ];
        return new \Slim\Views\PhpRenderer($path . '/templates/', $templateVariables);
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

    $container['db'] = function ($c) use ($module) {
        if (!is_dir(__DIR__ . '/../db/' . $module))
            mkdir(__DIR__ . '/../db/' . $module);
        
        $db = $c['settings']['db'];
        $medoo = new \Medoo\Medoo([
            'database_type' => 'sqlite',
            'database_file' => __DIR__ . '/../db/' . $module . '/database.db'
        ]);
        return $medoo;
    };

    $seedClass = $namespace . '\\seed';
    if (class_exists($seedClass))
        new $seedClass($container);
    
    $routes = $namespace . "\\routes";
    $app->group('/' . $module, function () use ($routes) {
        new $routes($this);
    });

    new routes($app);
    
    return $app->run();
}
