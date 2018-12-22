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
        $students = [];

        foreach ($this->container->db->select("users", '*') as $user) {
            $userinfo = $this->container->db->get('userinfo', '*', ['id' => $user['id']]);
            $userCL = new \user();
            $userCL->setInfo($user['id'], $user['name'], $user['role'], $user['activeRole']);
            if ($userinfo) {
                foreach (['givenname', 'surname', 'class'] as $val)
                    if (array_key_exists($val, $userinfo))
                        $userCL->setAttrib($val, $userinfo[$val]);
            }
            if ($userCL->getInfo('roles') == [0])
                array_push($students, $userCL);
            else
                array_push($users, $userCL);
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
                    $this->redirectWithMessage($response, 'user-manageUsers', "error", ["Chyba odebírání!", "Nelze odebrat administrátora"]);
                } else {

                    $this->container->db->delete("users", ["id" => $id]);

                    $this->redirectWithMessage($response, 'user-manageUsers', "status", ["Oderání úspěšné!", "Účet " . $users[$id]. " byl odebrán!"]);
                }
            } else {
                $this->redirectWithMessage($response, 'user-manageUsers', "error", ["Chyba odebírání!", "Žádný uživatel nebyl specifikován"]);
            }
        }
        return $response;
    }
}