<?php

namespace controller\modules;

class install extends \sup\controller {

    function __invoke($request, $response, $args) {
        
        $data = $request->getParsedBody();
        $name = intVal(filter_var(@$data['name'], FILTER_SANITIZE_STRING));
        
        if ($request->isPut()) {
            $module = $this->container->modules->findRemote($name);
            if (is_null($module))
                $this->redirectWithMessage($response, 'modules-view', "error", ["Chyba!", "Modul nenalezen!"]);

            if ($this->container->modules->install($module))
                $this->redirectWithMessage($response, 'modules-view', "status", ["Modul nainstalován"]);
            else
                $this->redirectWithMessage($response, 'modules-view', "error", ["Chyba při instalaci"]);
            return $response;
        }

        $module = $this->container->modules->findInstalled($name);
        if (is_null($module))
            return $this->redirectWithMessage($response, 'modules-view', "error", ["Chyba!", "Modul nenalezen"]);
        
        if ($request->isPatch()) {
            if ($this->container->modules->update($module))
                $this->redirectWithMessage($response, 'modules-view', "status", ["Modul aktualizován"]);
            else
                $this->redirectWithMessage($response, 'modules-view', "error", ["Chyba při aktualizaci"]);
            
            return $response;
        }
        
        if ($request->isDelete()) {
            if ($module->remove())
                $this->redirectWithMessage($response, 'modules-view', "status", ["Modul odebrán"]);
            else
                $this->redirectWithMessage($response, 'modules-view', "error", ["Chyba při odinstalaci"]);

            return $response;
        }
    }
}