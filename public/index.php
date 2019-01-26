<?php

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

require '../vendor/autoload.php';

set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) {
    if (0 === error_reporting()) return false;
    //debug_print_backtrace();
    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
});

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = true;
$config['name'] = "SUP";
$config['public']['version'] = '0.2.8_dev';

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

$container['factory'] = function ($c) {
    $factory = new \SUP\Factory($c);
    return $factory;
};

$container['modules'] = function($c) {
    $modules = new \modules($c);
    return $modules;
};

$container['auth'] = function($c) {
    $auth = new \SUP\Auth($c);
    return $auth;
};

$container['lang'] = function ($c) {
    $lang = new lang($c);
    return $lang;
};

$container->lang->loadLangs(__DIR__ . "/../lang");
$container->lang->getLang();

$container['view'] = function ($container) {
    $templateVariables = [
        "router" => $container->router,
        "auth" => $container->auth,
        "lang" => $container->lang,
        "ROOT_PATH" => $container->get('settings')['path'],
        "baseLang" => $container->lang,
        "baseRouter" => $container->router,
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
    $medoo = new \database\Medoo([
        'database_type' => 'sqlite',
        'database_file' => __DIR__ . '/../db/database.db'
    ]);
    return $medoo;
};

(new \database\seed($container, null))->update();

foreach ($container->modules->getInstalled() as $module) {
    if (!$module->isEnabled())
        continue;

    $name = $module->getName();

    if (class_exists('\\modules\\' . $name . '\\routes')) {
        $app->get('/' . $name . '/assets/{path:.*}', new \controller\assets($container, $name))
        ->add(\middleware\auth\auth::class);

        $app->any('/' . $name . '[/{params:.*}]', function ($request, $response) use ($module, $name, $container) {
            return createModule($module, __DIR__ . '/../modules/' . $name, '\\modules\\' . $name, $container);
        })
        ->add(\middleware\auth\auth::class)
        ->setName($name);
    }
}

new routes($app);

$container['base'] = $container;

$app->run();

function createModule(\Module $module, $path, $namespace, $globalContainer) {
    $settings = $globalContainer['settings']->all();

    $name = $module->getName();
    $settings['module_name'] = $name;

    $app = new \Slim\App(['settings' => $settings]);
    $container = $app->getContainer();
    
    $container['factory']  = clone $globalContainer['factory'];
    $container['auth']  = clone $globalContainer['auth'];
    $container['flash'] = clone $globalContainer['flash'];
    $container['csrf']  = clone $globalContainer['csrf'];
    $container['base'] = $globalContainer;

    $container['lang'] = function ($c) use ($name) {
        $lang = new lang($c, __DIR__ . "/../modules" . $name . "/lang");
        return $lang;
    };
    
    $container->lang->loadLangs($path . "/lang");
    $container->lang->getLang();

    $container['view'] = function ($container) use ($path, $name) {
        $templateVariables = [
            "router" => $container->router,
            "auth" => $container->auth,
            "lang" => $container->lang,
            "ROOT_PATH" => $container->get('settings')['path'],
            "ASSET_PATH" => $container->get('settings')['path'] . '/' . $name . "/assets" ,
            "baseLang" => $container->base->lang,
            "baseRouter" => $container->base->router,
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

    $container['db'] = function ($c) use ($name) {
        if (!is_dir(__DIR__ . '/../db/' . $name))
            mkdir(__DIR__ . '/../db/' . $name);
        
        $db = $c['settings']['db'];
        $medoo = new \database\Medoo([
            'database_type' => 'sqlite',
            'database_file' => __DIR__ . '/../db/' . $name . '/database.db'
        ]);
        return $medoo;
    };

    $manifest = $module->getManifest();

    if (array_key_exists('schema', $manifest) && is_array($manifest['schema']))
        (new \database\seed($container, $manifest['schema']))->update();

    $initClass = $namespace . '\\init';
    if (class_exists($initClass))
        new $initClass($container);
    
    $routes = $namespace . "\\routes";
    $app->group('/' . $name, function () use ($routes) {
        new $routes($this);
    })->add(\middleware\auth\auth::class);

    new routes($app);
    
    return $app->run();
}
