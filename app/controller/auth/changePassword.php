<?php

namespace controller\auth;

class changePassword extends \sup\controller {
    
    function __invoke($request, $response, $args) {

        if ($request->isGet()) {

            $this->sendResponse($request, $response, "auth/change.phtml");

        } elseif ($request->isPut()) {

            $data = $request->getParsedBody();
            $old = filter_var(@$data['old'], FILTER_SANITIZE_STRING);
            $pass = substr(filter_var(@$data['pass'], FILTER_SANITIZE_STRING), 0, 30);
            $pass2 = substr(filter_var(@$data['pass2'], FILTER_SANITIZE_STRING), 0, 30);

            if (!$this->container->auth->checkPassword($old)) {
                $this->redirectWithMessage($response, 'user-changePassword', "error", ["Chyba!", "Chybné aktuální heslo!"]);
            } else if (
                !is_string($old) || strlen($old) == 0 ||
                !is_string($pass) || strlen($pass) == 0 ||
                $pass !== $pass2
            )
                $this->redirectWithMessage($response, 'user-changePassword', "error", ["Chyba!", "Hesla nesouhlasí!"]);
            else if (strlen($data['pass']) > 30)
                $this->redirectWithMessage($response, 'user-changePassword', "error", ["Chyba!", "Zvolte kratší heslo!"]);
            else if ($pass !== $data['pass'])
                $this->redirectWithMessage($response, 'user-changePassword', "error", ["Chyba!", "Nepoužívejte speciální znaky!"]);
            else if (strlen($pass) < 8)
                $this->redirectWithMessage($response, 'user-changePassword', "error", ["Chyba!", "Heslo je příliš krátké!"]);
            else {
                $this->container->auth->changePass($pass);
                $this->redirectWithMessage($response, 'dashboard', "status", ["Úspěch!", "Heslo úspěšně změneno"]);
            }
        }
        return $response;
    }
}