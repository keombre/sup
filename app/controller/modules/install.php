<?php

namespace controller\modules;

class install extends \sup\controller {

    function __invoke($request, $response, $args) {
        $id = filter_var(@$args['id'], FILTER_SANITIZE_STRING);
        
        if (!is_null($request->getQueryParam("update"))) {
            $module = $this->container->modules->findInstalled($id);
            if (is_null($module))
                return $this->redirectWithMessage($response, 'modules-view', "error", ["Chyba!", "Modul nenalezen"]);
            
            if ($this->container->modules->update($module))
                return $this->redirectWithMessage($response, 'modules-view', "status", ["Modul aktualizován"]);
            else
                return $this->redirectWithMessage($response, 'modules-view', "error", ["Chyba při aktualizaci"]);
        } else if (!is_null($request->getQueryParam("remove"))) {
            $module = $this->container->modules->findInstalled($id);
            if (is_null($module))
                return $this->redirectWithMessage($response, 'modules-view', "error", ["Chyba!", "Modul nenalezen"]);
            
            if ($module->remove())
                return $this->redirectWithMessage($response, 'modules-view', "status", ["Modul odebrán"]);
            else
                return $this->redirectWithMessage($response, 'modules-view', "error", ["Chyba při odinstalaci"]);
        } else {
            $module = $this->container->modules->findRemote($id);
            if (is_null($module))
                return $this->redirectWithMessage($response, 'modules-view', "error", ["Chyba!", "Modul nenalezen!"]);
            
            if ($this->container->modules->install($module))
                return $this->redirectWithMessage($response, 'modules-view', "status", ["Modul nainstalován"]);
            else
                return $this->redirectWithMessage($response, 'modules-view', "error", ["Chyba při instalaci"]);
        }
    }
}