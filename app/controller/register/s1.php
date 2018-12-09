<?php

namespace controller\register;

class s1 {

    use \traits\sendResponse;
    
    protected $container;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    function __invoke($request, $response) {
        if ($request->isGet()) {

            do {
                $id = mt_rand(10000, 99999);
            } while ($this->container->db->has("users", ["id" => $id]));

            $this->sendResponse($request, $response, "register/s1.phtml", ["id" => $id]);

        } elseif ($request->isPost()) {

            $data = $request->getParsedBody();

            $pass = filter_var(@$data['pass'], FILTER_SANITIZE_STRING);
            $pass2 = filter_var(@$data['pass2'], FILTER_SANITIZE_STRING);
            $id = filter_var(@$data['id'], FILTER_SANITIZE_STRING);
            
            if (
                is_string($pass) && strlen($pass) > 0 &&
                $pass === $pass2
            ) {
                if (strlen($pass) > 7) {
                    $_SESSION["APP_PASS"] = $pass;
                    $_SESSION["APP_ID"] = $id;
                    $response = $response->withRedirect($this->container->router->pathFor("register-s2"), 301);
                } else {
                    $this->redirectWithMessage($response, 'register-s1', "error", ["Chyba!", "Zvolte delší heslo!"]);
                }
            } else {
                $this->redirectWithMessage($response, 'register-s1', "error", ["Chyba!", "Hesla nesouhlasí!"]);
            }
        }

        return $response;
    }
}