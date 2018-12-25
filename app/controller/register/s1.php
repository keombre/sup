<?php

namespace controller\register;

class s1 extends \sup\controller {

    function __invoke($request, $response, $args) {
        if ($request->isGet()) {

            do {
                $id = mt_rand(10000, 99999);
            } while ($this->container->db->has("users", ["id" => $id]));

            $this->sendResponse($request, $response, "register/s1.phtml", ["id" => $id]);

        } else if ($request->isPost()) {

            $data = $request->getParsedBody();

            $pass = substr(filter_var(@$data['pass'], FILTER_SANITIZE_STRING), 0, 30);
            $pass2 = substr(filter_var(@$data['pass2'], FILTER_SANITIZE_STRING), 0, 30);
            $id = filter_var(@$data['id'], FILTER_SANITIZE_STRING);
            
            if (!is_numeric($id) || strlen($id) != 5)
                $this->redirectWithMessage($response, 'register-s1', "error", ["Chyba!", "Zkuste to prosím později"]);
            else if (!is_string($pass) || !strlen($pass) > 0 || $pass != $pass2)
                $this->redirectWithMessage($response, 'register-s1', "error", ["Chyba!", "Hesla nesouhlasí!"]);
            else if (strlen($pass) < 8)
                $this->redirectWithMessage($response, 'register-s1', "error", ["Chyba!", "Zvolte delší heslo!"]);
            else if (strlen($data['pass']) > 30)
                $this->redirectWithMessage($response, 'register-s1', "error", ["Chyba!", "Zvolte kratší heslo!"]);
            else if ($pass !== $data['pass'])
                $this->redirectWithMessage($response, 'register-s2', "error", ["Chyba!", "Nepoužívejte speciální znaky!"]);
            else {
                $_SESSION["APP_PASS"] = $pass;
                $_SESSION["APP_ID"] = $id;
                $response = $response->withRedirect($this->container->router->pathFor("register-s2"), 301);
            }
        }

        return $response;
    }
}