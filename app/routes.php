<?php

final class routes {

    function __construct(\Slim\App $app) {
        
        $app->group($app->getContainer()->get('settings')['path'], function() {
            $this->group('', function() {

                $this->group('/dashboard', function () {
                    $this->get('', \controller\dashboard\home::class)
                    ->setName('dashboard');
                    $this->get('/home', \controller\dashboard\home::class)
                    ->setName('dashboard-home');

                    $this->get('/subjects', \controller\dashboard\subjects::class)
                    ->setName('dashboard-subjects');
                });

                $this->group('/lists', function () {
                    $this->get('', \controller\lists\view::class)
                    ->setName('lists');

                    $this->get('/view[/{id}]', \controller\lists\view::class)
                    ->setName('lists-view')
                    ->add(\middleware\lists\listID::class)
                    ->add(\middleware\lists\open_editing::class);

                    $this->map(['GET', 'PUT', 'DELETE'], '/edit[/{id}]', \controller\lists\edit::class)
                    ->setName('lists-edit')
                    ->add(new \middleware\auth\level($this->getContainer(), 0))
                    ->add(\middleware\lists\listID::class)
                    ->add(\middleware\lists\open_editing::class);

                    $this->map(['GET', 'PUT'], '/validate/{id}', \controller\lists\validate::class)
                    ->setName('lists-validate')
                    ->add(\middleware\lists\listID::class)
                    ->add(\middleware\lists\open_editing::class);

                    $this->group('/admin', function () {
                        $this->post('/create', \controller\lists\admin\create::class)
                        ->setName('lists-admin-create');

                        $this->post('/settings', \controller\lists\admin\settings::class)
                        ->setName('lists-admin-settings');

                        $this->map(['GET', 'PUT'], '/manage', \controller\lists\admin\manage::class)
                        ->setName('lists-admin-manage');
                    })->add(\middleware\auth\admin::class);
                    
                })->add(\middleware\dashboard::class);
                
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

            $this->get('/lang/{lang}', \controller\lang::class)
                ->setName('lang');
            
            $this->group('', function() {
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
        });
    }
}
