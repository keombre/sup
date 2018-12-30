<?php

namespace controller\auth;

class manage extends \sup\controller {

    function __invoke($request, $response, $args) {
        
        $users = [];
        $students = array_reduce($this->db->select('users', ['[>]userinfo' => 'id'], [
            'users.id [Int]',
            'users.name(code) [String]',
            'users.role [String]',
            'name' => [
                'userinfo.givenname(given) [String]',
                'userinfo.surname(sur) [String]'
            ],
            'userinfo.class [String]'
        ], ['users.role[!]' => -1]), function ($e, $f) use (&$users) {
            $f['role'] = filter_var_array(explode(',', $f['role']), FILTER_VALIDATE_INT);
            ${max($f['role']) == 0?'e':'users'}[$f['id']] = $f;
            return $e;
        }, []);

        if ($request->isGet()) {

            $this->sendResponse($request, $response, "auth/manage.phtml", [
                "users" => $users,
                "students" => $students
            ]);

        } elseif ($request->isDelete()) {

            $data = $request->getParsedBody();
            $id = filter_var(@$data['id'], FILTER_SANITIZE_STRING);

            $l = $this->container->lang;

            if (!is_string($id) || strlen($id) == 0)
                return $this->redirectWithMessage($response, 'user-manageUsers', "error", [
                    $l->g('error-notfound-title', 'user-manage'),
                    $l->g('error-notfound-message', 'user-manage')
                ]);
            
            else if (intval($id) == 1)
                return $this->redirectWithMessage($response, 'user-manageUsers', "error", [
                    $l->g('error-admindel-title', 'user-manage'),
                    $l->g('error-admindel-message', 'user-manage')
                ]);
            
            else if (!$this->db->has('users', ['id' => $id]))
                return $this->redirectWithMessage($response, 'user-manageUsers', "error", [
                    $l->g('error-notfound-title', 'user-manage'),
                    $l->g('error-notfound-message', 'user-manage')
                ]);
            
            else {
                $this->db->update("users", ["role" => -1], ['id' => $id]);

                return $this->redirectWithMessage($response, 'user-manageUsers', "status", [
                    $l->g('success-remove-title', 'user-manage'),
                    $l->g('success-remove-message', 'user-manage')
                ]);
            }
        }
        return $response;
    }
}
