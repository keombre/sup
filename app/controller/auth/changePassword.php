<?php

namespace controller\auth;

class changePassword extends \sup\controller {
    
    function __invoke($request, $response) {

        if ($request->isGet()) {

            $this->sendResponse($request, $response, "auth/change.phtml");

        } elseif ($request->isPut()) {

            $data = $request->getParsedBody();
            $old = filter_var(@$data['old'], FILTER_SANITIZE_STRING);
            $pass = filter_var(@$data['pass'], FILTER_SANITIZE_STRING);
            $pass2 = filter_var(@$data['pass2'], FILTER_SANITIZE_STRING);

            if (!$this->container->auth->checkPassword($old)) {
                
                $this->redirectWithMessage($response, 'user-changePassword', "error", ["Chyba!", "Chybné aktuální heslo!"]);
            } else if (
                is_string($old) && strlen($old) > 0 &&
                is_string($pass) && strlen($pass) > 0 &&
                $pass === $pass2
            ) {
                if (strlen($pass) > 7) {
                    $this->container->auth->changePass($pass);
                
                    $this->redirectWithMessage($response, 'dashboard', "status", ["Úspěch!", "Heslo úspěšně změneno"]);
                } else {
                    $this->redirectWithMessage($response, 'user-changePassword', "error", ["Chyba!", "Heslo je příliš krátké!"]);
                }
            } else {
                $this->redirectWithMessage($response, 'user-changePassword', "error", ["Chyba!", "Hesla nesouhlasí!"]);
            }
        }
        return $response;
    }
}