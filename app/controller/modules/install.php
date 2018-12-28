<?php

namespace controller\modules;

class install extends \sup\controller {

    function __invoke($request, $response, $args) {
        $data = $request->getParsedBody();
        $id = filter_var(@$data['id'], FILTER_SANITIZE_STRING);
        
        if (!is_null($request->getQueryParam("update"))) {
            $module = $this->container->modules->findInstalled($id);
            if (is_null($module))
                return $this->redirectWithMessage($response, 'modules', "error", ["Chyba!", "Modul nenalezen"]);
            
            if ($this->container->modules->update($module))
                return $this->redirectWithMessage($response, 'modules', "error", ["Chyba při aktualizaci"]);
            else
                return $this->redirectWithMessage($response, 'modules', "status", ["Modul aktualizován"]);
        } else {
            $module = $this->container->modules->findRemote($id);
            if (is_null($module))
                return $this->redirectWithMessage($response, 'modules', "error", ["Chyba!", "Modul nenalezen"]);
            
            if ($this->container->modules->install($module))
                return $this->redirectWithMessage($response, 'modules', "error", ["Chyba při instalaci"]);
            else
                return $this->redirectWithMessage($response, 'modules', "status", ["Modul nainstalován"]);
        }
    }
}