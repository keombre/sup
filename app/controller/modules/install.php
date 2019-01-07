<?php

namespace controller\modules;

class install extends \SUP\controller {

    function __invoke($request, $response, $args) {
        
        $data = $request->getParsedBody();
        $name = intVal(filter_var(@$data['name'], FILTER_SANITIZE_STRING));
        
        $l = $this->container->lang;

        if ($request->isPut()) {
            $module = $this->container->modules->findRemote($name);
            if (is_null($module))
                $this->redirectWithMessage($response, 'modules-view', "error", [
                    $l->g('error-notfound-title', 'modules'),
                    $l->g('error-notfound-message', 'modules')
                ]);
            
            $state = $this->container->modules->install($module);
            $this->sendComposerOutput();

            if ($state)
                $this->redirectWithMessage($response, 'modules-view', "status", [
                    $l->g('install-success-title', 'modules'),
                    $l->g('install-success-message', 'modules')
                ]);
            else
                $this->redirectWithMessage($response, 'modules-view', "error", [
                    $l->g('install-error-title', 'modules'),
                    $l->g('install-error-message', 'modules')
                ]);
            return $response;
        }

        $module = $this->container->modules->findInstalled($name);
        if (is_null($module))
            return $this->redirectWithMessage($response, 'modules-view', "error", [
                $l->g('error-notfound-title', 'modules'),
                $l->g('error-notfound-message', 'modules')
            ]);
        
        if ($request->isPatch()) {

            $state = $this->container->modules->update($module);
            $this->sendComposerOutput();

            if ($state)
                $this->redirectWithMessage($response, 'modules-view', "status", [
                    $l->g('update-success-title', 'modules'),
                    $l->g('update-success-message', 'modules')
                ]);
            else
                $this->redirectWithMessage($response, 'modules-view', "error", [
                    $l->g('update-error-title', 'modules'),
                    $l->g('update-error-message', 'modules')
                ]);
            
            return $response;
        }
        
        if ($request->isDelete()) {

            $state = $this->container->modules->remove($module);
            $this->sendComposerOutput();

            if ($state)
                $this->redirectWithMessage($response, 'modules-view', "status", [
                    $l->g('delete-success-title', 'modules'),
                    $l->g('delete-success-message', 'modules')
                ]);
            else
                $this->redirectWithMessage($response, 'modules-view', "error", [
                    $l->g('delete-error-title', 'modules'),
                    $l->g('delete-error-message', 'modules')
                ]);

            return $response;
        }
    }

    private function sendComposerOutput() {
        $this->container->flash->addMessage('composerOut', $this->container->modules->getComposerOutput());
    }
}