<?php

final class routes {

    private $app;

    function __construct(\Slim\App $app) {
        $this->app = $app;
        
        $app->group('', function() {
            
            $this->group('/user', function () {
                $this->post('/logout', \controller\auth\logout::class)
                ->setName('user-logout');
                
                $this->map(['GET', 'PUT'], '/changepass', \controller\auth\changePassword::class)
                ->setName('user-changePassword');
                
                $this->map(['GET', 'PUT'], '/changerole', \controller\auth\changeRole::class)
                ->setName('user-changeRole');
                
                $this->group('', function () {
                    $this->map(['GET', 'DELETE'], '/manage', \controller\auth\manage::class)
                    ->setName('user-manageUsers');
                    
                    $this->map(['GET', 'PUT'], '/register', \controller\auth\register::class)
                    ->setName('user-register');
                })->add(\middleware\auth\admin::class);
            });
        
        })->add(\middleware\auth\auth::class);
        
        $this->registerIfNotExists('/dashboard', 'dashboard');
        $this->registerIfNotExists('/lang/{lang}', 'lang');
        

        /*$app->group('', function() {
            $this->get('/', \controller\index::class)
                ->setName('index');
            
            $this->post('/login', \controller\auth\login::class)
                ->setName('login');
            
            $this->group('/new', function () {
                $this->map(['GET', 'POST'], '/s1', \controller\register\s1::class)
                    ->setName('register-s1');

                $this->map(['GET', 'POST'], '/s2', \controller\register\s2::class)
                    ->add(\middleware\register\s1::class)
                    ->setName('register-s2');
            });
            
        })->add(\middleware\auth\autologin::class);
        */
    }

    private function registerIfNotExists($routePattern, $name) {
        foreach ($this->app->getContainer()['router']->getRoutes() as $route)
            if ($route->getPattern() == $routePattern)
                return;
        
        $this->app->any($routePattern, null)->setName($name);
    }
}
