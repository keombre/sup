<?php

namespace middleware;

class token {
    
    const sleep = 5;

    public function __invoke($request, $response, $next) {
        
        $args = $request->getAttribute('routeInfo')[2];
        $token = strtoupper($args['tok']);

        sleep(self::sleep);
        if (!preg_match('/([^0-9A-F])/', $token) && strlen($token) == 64) {

            $request = $request->withAttributes([
                "token" => $token,
                'name' => $this->escapeName(@$args['name'])
            ]);

            return $next($request, $response);
        } else {
            return $response->withJson(["result" => "invalid request"], 400);
        }
        
    }

    protected function escapeName($name) {
        $name = preg_replace('/[^\x20-\x7E]/','', $name);
        return isset($name) ? $name : null;
    }
}
