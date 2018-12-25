<?php

namespace controller\auth;

class login extends \sup\controller {

    function __invoke($request, $response, $args) {

        $data = $request->getParsedBody();

        $name = substr(filter_var(@$data['name'], FILTER_SANITIZE_STRING), 0, 50);
        $pass = substr(filter_var(@$data['pass'], FILTER_SANITIZE_STRING), 0, 50);
        
        $next = $request->getQueryParam("next");
        if (!is_null($next) && !filter_var(base64_decode($next), FILTER_VALIDATE_URL))
            $next = null;

        if ($this->container->auth->login($name, $pass)) {
            if (!is_null($next))
                $response = $response->withRedirect(base64_decode($next), 301);
            else
                $response = $response->withRedirect($this->container->router->pathFor('dashboard'), 301);
        } else {
            sleep(2);
            $this->redirectWithMessage($response, 'index', "error", [
                $this->container->lang->g('login-failed-title', 'index'),
                $this->container->lang->g('login-failed-message', 'index')
            ], [], is_null($next)?[]:["next" => $next]);
        }

        return $response;
    }
}