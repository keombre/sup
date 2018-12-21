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

            if ($this->container->db->has("users", ["name" => $name])) {

                $this->redirectWithMessage($response, 'register', "error", ["Error!", "Username alredy taken!"]);
            } else if (
                is_string($name) && strlen($name) > 0 &&
                is_string($pass) && strlen($pass) > 0 &&
                $pass === $pass2
            ) {
                if (strlen($pass) > 7) {
                    
                    // todo: modify for new api
                    if ($this->container->auth->register(null, $name, $pass, null, 1)) {
                        
                        $this->redirectWithMessage($response, 'dashboard', "status", ["Success!", "User " . $users[$id]. " was created!"]);
                    } else {
                        $this->sendResponse($request, $response, "auth/register.phtml", [
                            "error" => [["Error!", "Use only ASCII in UserName & keep it short!"]]
                        ]);
                    }
                } else {
                    $this->redirectWithMessage($response, 'register', "error", ["Error!", "Password too short!"]);
                }
            } else {
                $this->redirectWithMessage($response, 'register', "error", ["Error!", "Passwords don't match!"]);
            }
        }
        return $response;
    }
}