<?php

namespace controller\layout;

class home extends \sup\controller {

    function __invoke($request, $response, $args) {

        if ($this->container->auth->user->level(ROLE_ADMIN)) {
            return $this->sendResponse($request, $response, "dash/admin.phtml");
        } else if ($this->container->auth->user->level(ROLE_TEACHER)) {
            return $this->sendResponse($request, $response, "dash/teacher.phtml");
        } else if ($this->container->auth->user->level(ROLE_STUDENT)) {
            return $this->sendResponse($request, $response, "dash/student.phtml");
        }
    }
}
