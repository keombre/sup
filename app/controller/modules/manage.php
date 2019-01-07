<?php

namespace controller\modules;

class manage extends \SUP\controller {

    function __invoke($request, $response, $args) {
        
        $data = $request->getParsedBody();
        $name = intVal(filter_var(@$data['name'], FILTER_SANITIZE_STRING));

        $l = $this->container->lang;

        $module = $this->container->modules->findInstalled($name);
        if (is_null($module))
            return $this->redirectWithMessage($response, 'modules-view', "error", [
                $l->g('manage-error-notfound-title', 'modules'),
                $l->g('manage-error-notfound-message', 'modules')
            ]);

        if ($request->isPut()) {

            if ($module->enable())
                $this->redirectWithMessage($response, 'modules-view', "status", [
                    $l->g('manage-success-enable-title', 'modules'),
                    $l->g('manage-success-enable-message', 'modules')
                ]);
            else
                $this->redirectWithMessage($response, 'modules-view', "error", [
                    $l->g('manage-error-enable-title', 'modules'),
                    $l->g('manage-error-enable-message', 'modules')
                ]);
            return $response;
        }
        
        if ($request->isDelete()) {
            if ($module->disable())
                $this->redirectWithMessage($response, 'modules-view', "status", [
                    $l->g('manage-success-disable-title', 'modules'),
                    $l->g('manage-success-disable-message', 'modules')
                ]);
            else
                $this->redirectWithMessage($response, 'modules-view', "error", [
                    $l->g('manage-error-disable-title', 'modules'),
                    $l->g('manage-error-disable-message', 'modules')
                ]);

            return $response;
        }
    }
}