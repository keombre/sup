<?php

namespace middleware\register;

class s1 extends \sup\middleware {

    public function __invoke($request, $response, $next) {

        if (
            array_key_exists('APP_PASS', $_SESSION) && is_string($_SESSION["APP_PASS"]) &&
            array_key_exists('APP_ID', $_SESSION) && is_string($_SESSION["APP_ID"])
        )
            return $next($request, $response);
        else
            return $response->withRedirect($this->container->router->pathFor('register-s1'), 301);
    }
}