<?php

namespace controller\register;

class s2 extends \SUP\controller {

    function __invoke($request, $response, $args) {
        if ($request->isGet()) {

            $this->sendResponse($request, $response, "register/s2.phtml", ["id" => $_SESSION['APP_ID']]);

        } else if ($request->isPost()) {

            $data = $request->getParsedBody();

            $givenname = substr(filter_var(@$data['givenname'], FILTER_SANITIZE_STRING), 0, 50);
            $surname = substr(filter_var(@$data['surname'], FILTER_SANITIZE_STRING), 0, 50);
            $class = filter_var(@$data['class'], FILTER_SANITIZE_STRING);
            
            if (
                !is_string($givenname) ||
                strlen($givenname) == 0 ||
                !is_string($surname) ||
                strlen($surname) == 0
            )
                $this->redirectWithMessage($response, 'register-s2', "error", ["Chyba!", "Vyplňte jméno!"]);
            else if ($givenname !== $data['givenname'] || $surname !== $data['surname'])
                $this->redirectWithMessage($response, 'register-s2', "error", ["Chyba!", "Nepoužívejte speciální znaky!"]);
            
            else if (!(is_string($class) && strlen($class) == 1))
                $this->redirectWithMessage($response, 'register-s2', "error", ["Chyba!", "Zvolte třídu!"]);
            else if (
                is_numeric($_SESSION['APP_ID']) && strlen($_SESSION['APP_ID']) == 5 &&
                is_string($_SESSION['APP_PASS']) && strlen($_SESSION['APP_PASS']) > 7
            ) {
                if ($this->container->auth->register($_SESSION['APP_ID'], $_SESSION['APP_PASS'], [ROLE_STUDENT])) {
                    $this->container->auth->login($_SESSION['APP_ID'], $_SESSION['APP_PASS']);
                    $user = $this->container->auth->getUser();
                    $user->withAttribute("givenname", $givenname)
                         ->withAttribute("surname", $surname)
                         ->withAttribute("class", $class);

                    $response = $response->withRedirect($this->container->router->pathFor("dashboard"), 301);
                } else {
                    $this->redirectWithMessage($response, 'index', "error", ["Chyba!", "Nastala chyba, zkuste to prosím znovu."]);
                }
            } else {
                $this->redirectWithMessage($response, 'index', "error", ["Chyba!", "Nastala chyba, zkuste to prosím znovu."]);
            }
        }

        return $response;
    }
}