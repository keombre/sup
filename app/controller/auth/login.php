<?php

namespace controller\auth;

class login extends \sup\controller {

    function __invoke($request, $response) {
        if ($request->isGet()) {

            $this->sendResponse($request, $response, "auth/login.phtml");

        } elseif ($request->isPost()) {

            $data = $request->getParsedBody();

            $name = filter_var(@$data['name'], FILTER_SANITIZE_STRING);
            $pass = filter_var(@$data['pass'], FILTER_SANITIZE_STRING);
            
            if ($this->container->auth->login($name, $pass)) {
                $response = $response->withRedirect($this->container->router->pathFor('dashboard'), 301);
            } else {
                sleep(2);
                $this->redirectWithMessage($response, 'index', "error", [
                    $this->container->lang->g('login-failed-title', 'index'),
                    $this->container->lang->g('login-failed-message', 'index')
                ]);
            }
        }

        return $response;
    }
}