<?php

namespace base\layout;

class routes {
    function __construct($app) {
        $app->get('', controller\home::class)
        ->setName('dashboard');
        $app->get('/home', \controller\dashboard\home::class)
        ->setName('dashboard-home');
        $app->get('/subjects', \controller\dashboard\subjects::class)
        ->setName('dashboard-subjects');
    }
}
