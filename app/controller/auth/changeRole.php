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

            if (!$this->container->auth->changeRole($role))
                $this->redirectWithMessage($response, 'changeRole', "error", ["Chyba!", "Neexistující role"]);
            else if ($this->container->auth->user->getInfo('roles')[0] == $role)
                return $response->withRedirect($this->container->router->pathFor('dashboard'), 301);
            else
                $this->redirectWithMessage($response, 'dashboard', "status", ["Úspěch", "Nyní jste " . strtolower($this->container->lang->g($role, 'roles'))]);
        }
        return $response;
    }
}