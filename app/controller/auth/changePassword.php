<?php

namespace controller\auth;

class changePassword {
    
    use \traits\sendResponse;

    protected $container;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    function __invoke($request, $response) {

        if ($request->isGet()) {

            $this->sendResponse($request, $response, "auth/change.phtml");

        } elseif ($request->isPut()) {

            $data = $request->getParsedBody();
            $old = filter_var(@$data['old'], FILTER_SANITIZE_STRING);
            $pass = filter_var(@$data['pass'], FILTER_SANITIZE_STRING);
            $pass2 = filter_var(@$data['pass2'], FILTER_SANITIZE_STRING);

            if (!$this->container->auth->checkPassword($old)) {
                
                $this->redirectWithMessage($response, 'changePassword', "error", ["Chyba!", "Chybné aktuální heslo!"]);
            } else if (
                is_string($old) && strlen($old) > 0 &&
                is_string($pass) && strlen($pass) > 0 &&
                $pass === $pass2
            ) {
                if (strlen($pass) > 7) {
                    $this->container->auth->changePass($pass);
                
                    $this->redirectWithMessage($response, 'dashboard', "status", ["Úspěch!", "Heslo úspěšně změneno"]);
                } else {
                    $this->redirectWithMessage($response, 'changePassword', "error", ["Chyba!", "Heslo je příliš krátké!"]);
                }
            } else {
                $this->redirectWithMessage($response, 'changePassword', "error", ["Chyba!", "Hesla nesouhlasí!"]);
            }
        }
        return $response;
    }
}