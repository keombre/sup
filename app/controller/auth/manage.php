<?php

namespace controller\auth;

class manage extends \sup\controller {

    function __invoke($request, $response, $args) {
        
        $users = [];
        $students = array_filter($this->db->select('users', ['[>]userinfo' => 'id'], [
            'users.id [Int]',
            'users.name(code) [String]',
            'users.role [String]',
            'name' => [
                'userinfo.givenname(given) [String]',
                'userinfo.surname(sur) [String]'
            ],
            'userinfo.class [String]'
        ], ['users.role[!]' => -1]), function ($e) use (&$users) {
            $e['role'] = explode(',', $e['role']);
            
            if (max($e['role']) == 0) {
                return $e;
            }
            else
                array_push($users, $e);
        });

        if ($request->isGet()) {

            $this->sendResponse($request, $response, "auth/manage.phtml", [
                "users" => $users,
                "students" => $students
            ]);

        } elseif ($request->isDelete()) {

            $data = $request->getParsedBody();
            $id = filter_var(@$data['id'], FILTER_SANITIZE_STRING);

            if (is_string($id) && strlen($id) > 0) {
                
                if (max($users[array_search(array_column($users, 'id'), $id)]['role']) == 2) {
                    $this->redirectWithMessage($response, 'user-manageUsers', "error", ["Chyba odebírání!", "Nelze odebrat administrátora"]);
                } else {

                    $this->db->update("users", ["role" => -1], ['id' => $id]);

                    $this->redirectWithMessage($response, 'user-manageUsers', "status", ["Oderání úspěšné!", "Účet " . $users[$id]. " byl odebrán!"]);
                }
            } else {
                $this->redirectWithMessage($response, 'user-manageUsers', "error", ["Chyba odebírání!", "Žádný uživatel nebyl specifikován"]);
            }
        }
        return $response;
    }
}