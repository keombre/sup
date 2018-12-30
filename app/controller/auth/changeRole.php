<?php

namespace controller\auth;

class changeRole extends \sup\controller {

    function __invoke($request, $response, $args) {

        if ($request->isGet()) {
            $this->sendResponse($request, $response, "auth/changeRole.phtml", [
                "roles" => $this->container->auth->user->getInfo('roles'),
                "activeRole" => $this->container->auth->user->getInfo('activeRole')
            ]);

        } elseif ($request->isPut()) {

            $data = $request->getParsedBody();
            $role = intVal(filter_var(@$data['role'], FILTER_SANITIZE_STRING));
            $oldRole = $this->container->auth->user->getInfo('activeRole');

            $l = $this->container->lang;

            if (!$this->container->auth->changeRole($role))
                $this->redirectWithMessage($response, 'user-changeRole', "error", [
                    $l->g('error-notfound-title', 'user-changeRole'),
                    $l->g('error-notfound-message', 'user-changeRole')
                ]);

            else if ($oldRole == $role)
                return $response->withRedirect($this->container->router->pathFor('dashboard'), 301);
            else
                $this->redirectWithMessage($response, 'dashboard', "status", [
                    $l->g('success-change-title', 'user-changeRole'),
                    $l->g('success-change-message', 'user-changeRole') . strtolower($this->container->lang->g($role, 'roles'))
                ]);
        }
        return $response;
    }
}