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

            $this->get('/dashboard', \controller\dashboard\home::class)
            ->add(\middleware\layout::class)
            ->setName('dashboard');
    
            $this->group('/modules', function () {
                $this->get('', \controller\modules\view::class)->setName('modules-view');

                $this->map(['PUT', 'PATCH', 'DELETE'], '/install', \controller\modules\install::class)
                ->setName('modules-install');

                $this->map(['PUT', 'DELETE'], '/manage', \controller\modules\manage::class)
                ->setName('modules-manage');
                
            })->add(\middleware\auth\admin::class);
        
        })->add(\middleware\auth\auth::class);

        $app->get('/{lang}', \controller\lang::class)
        ->setName('lang');

        $app->group('', function() {
            $this->get('/', \controller\index::class)
                ->setName('index');
            
            $this->post('/login', \controller\auth\login::class)
                ->setName('login');
            
        })->add(\middleware\auth\autologin::class);
        
    }
}
