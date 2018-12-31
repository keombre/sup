<?php

namespace controller\auth;

class register extends \sup\controller {

    function __invoke($request, $response, $args) {

        if ($request->isGet()) {

            $l = $this->container->lang;
            
            $roles = [
                ROLE_STUDENT => $l->g(ROLE_STUDENT, 'roles'),
                ROLE_TEACHER => $l->g(ROLE_TEACHER, 'roles'),
                ROLE_ADMIN => $l->g(ROLE_ADMIN, 'roles')
            ];

            $this->sendResponse($request, $response, "auth/register.phtml", [
                "roles" => $roles
            ]);

        } elseif ($request->isPut()) {

            $data = $request->getParsedBody();
            $name = filter_var(@$data['name'], FILTER_SANITIZE_STRING);
            $pass = filter_var(@$data['pass'], FILTER_SANITIZE_STRING);
            $pass2 = filter_var(@$data['pass2'], FILTER_SANITIZE_STRING);

            $givenname = filter_var(@$data['givenname'], FILTER_SANITIZE_STRING);
            $surname = filter_var(@$data['surname'], FILTER_SANITIZE_STRING);
            $roles = array_reduce(filter_var_array(@$data['roles'], FILTER_VALIDATE_INT), function ($e, $f) {
                if ($f === false) return $e;
                if (in_array($f, [ROLE_STUDENT, ROLE_TEACHER, ROLE_ADMIN])) $e[] = $f;
                return $e;
            }, []);

            $l = $this->container->lang;

            if (count($roles) == 0) {
                $this->redirectWithMessage($response, 'user-register', "error", [
                    $l->g('error-noroles-title', 'user-register'),
                    $l->g('error-noroles-message', 'user-register')
                ]);

            } if (!is_string($name) || strlen($name) == 0)
                $this->redirectWithMessage($response, 'user-register', "error", [
                    $l->g('error-nouname-title', 'user-register'),
                    $l->g('error-nouname-message', 'user-register')
                ]);

            else if ($this->container->db->has("users", ["uname" => $name]))
                $this->redirectWithMessage($response, 'user-register', "error", [
                    $l->g('error-unametaken-title', 'user-register'),
                    $l->g('error-unametaken-message', 'user-register')
                ]);

            else if (!is_string($pass) || strlen($pass) < 8)
                $this->redirectWithMessage($response, 'user-register', "error", [
                    $l->g('error-passshort-title', 'user-register'),
                    $l->g('error-passshort-message', 'user-register')
                ]);
            
            else if ($pass !== $pass2)
                $this->redirectWithMessage($response, 'user-register', "error", [
                    $l->g('error-passdiff-title', 'user-register'),
                    $l->g('error-passdiff-message', 'user-register')
                ]);
            
            else if ($data['name'] != $name || $data['pass'] != $pass)
                $this->redirectWithMessage($response, 'user-register', "error", [
                    $l->g('error-charset-title', 'user-register'),
                    $l->g('error-charset-message', 'user-register')
                ]);
            
            else {
                if ($this->container->auth->register($name, $pass, $roles)) {
                    $user = (new \sup\User($this->container))->createFromDB($this->db->get('users', 'id', ['uname' => $name]));
                    $user->withAttribute('givenname', $givenname)
                         ->withAttribute('surname', $surname);
                    
                    $this->redirectWithMessage($response, 'dashboard', "status", [
                        $l->g('success-title', 'user-register'),
                        $l->g('success-message', 'user-register')
                    ]);
                } else
                    $this->redirectWithMessage($response, 'dashboard', "error", [
                        $l->g('error-generic-title', 'user-register'),
                        $l->g('error-generic-message', 'user-register')
                    ]);
            }
        }
        return $response;
    }
}