<?php

namespace controller\auth;

class logout extends \SUP\controller {

    function __invoke($request, $response, $args) {

        $this->container->auth->logout();
        $lang = @$_SESSION['lang'];
        session_unset();
        $_SESSION['lang'] = $lang;

        return $response->withRedirect($this->container->router->pathFor('index'), 301);
    }
}