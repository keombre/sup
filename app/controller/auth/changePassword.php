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

            $l = $this->container->lang;

            if (!$this->container->auth->checkPass($old)) {
                $this->redirectWithMessage($response, 'user-changePassword', "error", [
                    $l->g('error-wrongPass-title', 'user-changePass'),
                    $l->g('error-worngPass-message', 'user-changePass')
                ]);
            } else if (
                !is_string($old) || strlen($old) == 0 ||
                !is_string($pass) || strlen($pass) == 0 ||
                $pass !== $pass2
            )
                $this->redirectWithMessage($response, 'user-changePassword', "error", [
                    $l->g('error-diffPass-title', 'user-changePass'),
                    $l->g('error-diffPass-message', 'user-changePass')
                ]);
            else if (strlen($data['pass']) > 30)
                $this->redirectWithMessage($response, 'user-changePassword', "error", [
                    $l->g('error-longPass-title', 'user-changePass'),
                    $l->g('error-longPass-message', 'user-changePass')
                ]);
            else if (strlen($pass) < 8)
                $this->redirectWithMessage($response, 'user-changePassword', "error", [
                    $l->g('error-shortPass-title', 'user-changePass'),
                    $l->g('error-shortPass-message', 'user-changePass')
                ]);
            else if ($pass !== $data['pass'])
                $this->redirectWithMessage($response, 'user-changePassword', "error", [
                    $l->g('error-charset-title', 'user-changePass'),
                    $l->g('error-charset-message', 'user-changePass')
                ]);
            
            else {
                $this->container->auth->changePass($pass);
                $this->redirectWithMessage($response, 'dashboard', "status", [
                    $l->g('success-change-title', 'user-changePass'),
                    $l->g('success-change-message', 'user-changePass')
                ]);
            }
        }
        return $response;
    }
}