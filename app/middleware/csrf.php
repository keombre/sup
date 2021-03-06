<?php

namespace middleware;

class csrf extends \SUP\middleware {

    use \traits\sendResponse;
    
    public function __invoke($request, $response, $next) {
        $args = @$request->getAttribute('routeInfo')[2];
        $path = @$request->getAttribute('routeInfo')['request'][1];
        
        $csrf_status = @$args['csrf_status'];

        if ($csrf_status === false) {

            $this->redirectWithMessage($response, "dashboard", "error", ["Communication error!", "Please try again"]);

            return $response;
        } else {
            return $next($request, $response);
        }
        
    }

    protected function escapeName($name) {
        $name = preg_replace('/[^\x20-\x7E]/','', $name);
        return isset($name) ? $name : null;
    }
}
