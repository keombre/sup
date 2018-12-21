<?php

namespace controller\auth;

class changeRole {
    
    use \traits\sendResponse;

    protected $container;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    function __invoke($request, $response) {

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
            else
                $this->redirectWithMessage($response, 'dashboard', "status", ["Úspěch", "Nyní jste " . strtolower($this->container->lang->g($role, 'roles'))]);
        }
        return $response;
    }
}