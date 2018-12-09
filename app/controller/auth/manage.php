<?php

namespace controller\auth;

class manage {
    
    use \traits\sendResponse;

    protected $container;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    function __invoke($request, $response) {
        
        $users = [];
        foreach ($this->container->db->select("users", ["id", "name"], ["level" => [1, 2]]) as $user) {
            $users[$user['id']] = $user['name'];
        }
        
        $students = [];
        foreach ($this->container->db->select("users", ["id", "name", "class"], ["level" => 0]) as $user) {
            $students[$user['id']] = $user;
        }

        if ($request->isGet()) {

            $this->sendResponse($request, $response, "auth/manage.phtml", [
                "users" => $users,
                "students" => $students
            ]);

        } elseif ($request->isDelete()) {

            $data = $request->getParsedBody();
            $id = filter_var(@$data['id'], FILTER_SANITIZE_STRING);

            if (is_string($id) && strlen($id) > 0) {
                
                if ($users[$id] == 'admin') {
                    $this->redirectWithMessage($response, 'manageUsers', "error", ["Chyba odebírání!", "Nelze odebrat administrátora"]);
                } else {

                    $this->container->db->delete("users", ["id" => $id]);

                    $this->redirectWithMessage($response, 'manageUsers', "status", ["Oderání úspěšné!", "Účet " . $users[$id]. " byl odebrán!"]);
                }
            } else {
                $this->redirectWithMessage($response, 'manageUsers', "error", ["Chyba odebírání!", "Žádný uživatel nebyl specifikován"]);
            }
        }
        return $response;
    }
}