<?php

namespace controller\auth;

class manage extends \SUP\controller {

    function __invoke($request, $response, $args) {

        $users = [];
        $students = [];
        foreach ($this->db->select('users', 'id', ['roles[!]' => 'a:1:{i:0;i:-1;}']) as $id) {
            $user = (new \SUP\User($this->container))->createFromDB($id);
            if ($user->canBecome(ROLE_TEACHER) || $user->canBecome(ROLE_ADMIN))
                $users[] = $user;
            else
                $students[] = $user;
        }

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
                $this->db->update('users', ['roles' => [ROLE_DISABLED]], ['id' => $id]);

                return $this->redirectWithMessage($response, 'user-manageUsers', "status", [
                    $l->g('success-remove-title', 'user-manage'),
                    $l->g('success-remove-message', 'user-manage')
                ]);
            }
        } else if ($request->isPut()) {
            $id = $this->sanitizePost($request, 'id', FILTER_SANITIZE_STRING);

            if (is_null($id) || $id === false) {
                return $this->redirectWithMessage($response, 'user-manageUsers', "error", [
                    $l->g('error-notfound-title', 'user-manage'),
                    $l->g('error-notfound-message', 'user-manage')
                ]);
            }

            if ($this->container->auth->fakeLogin((int)$id)) {
                return $this->redirectWithMessage($response, 'dashboard', "status", [
                    $this->container->lang->g('login-success', 'user-manage', ['user' => $this->container->auth->getUser()->getAnyName()])
                ]);
            } else {
                return $this->redirectWithMessage($response, 'user-manageUsers', "error", [
                    $l->g('login-error', 'user-manage')
                ]);
            }

        }
        return $response;
    }
}
