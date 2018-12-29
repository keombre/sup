<?php

namespace controller\modules;

class manage extends \sup\controller {

    function __invoke($request, $response, $args) {
        
        $data = $request->getParsedBody();
        $name = intVal(filter_var(@$data['name'], FILTER_SANITIZE_STRING));

        $module = $this->container->modules->findInstalled($name);
        if (is_null($module))
            return $this->redirectWithMessage($response, 'modules-view', "error", ["Chyba!", "Modul nenalezen"]);
        
        if ($request->isPut()) {

            if ($module->enable())
                $this->redirectWithMessage($response, 'modules-view', "status", ["Modul povolen"]);
            else
                $this->redirectWithMessage($response, 'modules-view', "error", ["Chyba při povolování"]);
            return $response;
        }
        
        if ($request->isDelete()) {
            if ($module->disable())
                $this->redirectWithMessage($response, 'modules-view', "status", ["Modul zakázán"]);
            else
                $this->redirectWithMessage($response, 'modules-view', "error", ["Chyba při zakazování"]);

            return $response;
        }
    }
}