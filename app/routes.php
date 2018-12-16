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

                    $this->get('/admissions', \controller\dashboard\admissions::class)
                    ->setName('dashboard-admissions');
                    $this->get('/subjects', \controller\dashboard\subjects::class)
                    ->setName('dashboard-subjects');
                });

                $this->group('/lists', function () {
                    $this->get('', \controller\lists\view::class)
                    ->setName('lists');

                    $this->get('/view[/{id}]', \controller\lists\view::class)
                    ->setName('lists-view');

                    $this->map(['GET', 'PUT', 'POST', 'DELETE'], '/edit[/{id}]', \controller\lists\edit::class)
                    ->setName('lists-edit')
                    ->add(new \middleware\auth\level($this->getContainer(), 0));

                    $this->group('/admin', function () {
                        $this->post('/upload', \controller\lists\admin\upload::class)
                        ->setName('lists-admin-upload');
                    });
                })->add(\middleware\dashboard::class);
                
                /*
                $this->post('/dashboard/print', \controller\printer::class)
                    ->setName('printer');
                
                $this->map(['PUT', 'DELETE'], '/books', \controller\books::class)
                    ->setName('books');

                $this->map(['PUT', 'DELETE'], '/admin/books', \controller\adminBooks::class)
                    ->add(\middleware\admin::class)
                    ->setName('adminBooks');
                
                $this->post('/dashboard/approve', \controller\approve::class)
                    ->add(\middleware\teacher::class)
                    ->setName('approve');
                
                $this->get('/dashboard/list/{id}', \controller\lists::class)
                    ->add(\middleware\teacher::class)
                    ->setName('lists');*/
                
                $this->group('/user', function () {
                    $this->post('/logout', \controller\auth\logout::class)
                         ->setName('logout');
                    
                    $this->map(['GET', 'PUT'], '/changepass', \controller\auth\changePassword::class)
                         ->setName('changePassword');
                    
                    $this->group('', function () { // admin middleware
                        $this->map(['GET', 'DELETE'], '/manage', \controller\auth\manage::class)
                             ->setName('manageUsers');
                        
                        $this->map(['GET', 'PUT'], '/register', \controller\auth\register::class)
                             ->setName('register');
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
