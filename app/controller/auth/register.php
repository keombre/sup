<?php

namespace controller\auth;

class register {
    
    use \traits\sendResponse;

    protected $container;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    function __invoke($request, $response) {

        if ($request->isGet()) {

            $this->sendResponse($request, $response, "auth/register.phtml");

        } elseif ($request->isPut()) {

            $data = $request->getParsedBody();
            $name = filter_var(@$data['name'], FILTER_SANITIZE_STRING);
            $pass = filter_var(@$data['pass'], FILTER_SANITIZE_STRING);
            $pass2 = filter_var(@$data['pass2'], FILTER_SANITIZE_STRING);

            if (!is_string($name) || strlen($name) == 0)
                $this->redirectWithMessage($response, 'user-register', "error", ["Error!", "Username missing"]);

            else if ($this->container->db->has("users", ["name" => $name]))
                $this->redirectWithMessage($response, 'user-register', "error", ["Error!", "Username alredy taken!"]);

            else if (!is_string($pass) || strlen($pass) < 8)
                $this->redirectWithMessage($response, 'user-register', "error", ["Error!", "Password too short!"]);
            
            else if ($pass !== $pass2)
                $this->redirectWithMessage($response, 'user-register', "error", ["Error!", "Passwords don't match!"]);
            
            else if ($data['name'] != $name || $data['pass'] != $pass)
                $this->redirectWithMessage($response, 'user-register', "error", ["Chyba!", "Nepoužívejte speciální znaky!"]);
            
            else {
                if ($this->container->auth->register($name, $pass, [0, 1]))
                    $this->redirectWithMessage($response, 'dashboard', "status", ["Success!", "User was created!"]);
                
                else
                    $this->redirectWithMessage($response, 'dashboard', "error", ["Chyba!", "Chyba při tvorbě uživatele!"]);                
            }
        }
        return $response;
    }
}