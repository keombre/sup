<?php

namespace controller;

class assets extends \sup\controller {

    private $module_name;

    function __construct($container, $module_name) {
        parent::__construct($container);
        $this->module_name = $module_name;
    }

    function __invoke($request, $response, $args) {
        $path = __DIR__ . '/../../modules/'. $this->module_name;

        if (!is_dir($path . '/assets'))
            return $this->container->get('notFoundHandler')($request, $response);

        $file = preg_replace('/(^|\/)\.{2}\//', '', filter_var(@$args['path'], FILTER_SANITIZE_STRING));

        if (!is_file($path . '/assets/' . $file))
            return $this->container->get('notFoundHandler')($request, $response);

        $size = filesize($path . '/assets/' . $file);
        $ext = strtolower(pathinfo('/' . $file, PATHINFO_EXTENSION));

        if (in_array($ext, ['css', 'js']))
            $type = [
                'css' => 'text/css',
                'js'  => 'application/javascript'
            ][$ext];
        else
            $type = mime_content_type($path . '/assets/' . $file);

        $response->getBody()->write(file_get_contents($path . '/assets/' . $file));

        return $response->withHeader('Accept-Ranges', 'bytes')
                        ->withHeader('Content-Length', $size)
                        ->withHeader('Content-Type', $type);
    }
}
