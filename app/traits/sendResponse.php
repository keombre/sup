<?php

namespace traits;

trait sendResponse
{
    public function sendResponse($request, &$response, $page, $args = [])
    {
        $nameKey = $this->container->csrf->getTokenNameKey();
        $valueKey = $this->container->csrf->getTokenValueKey();
        $name = $request->getAttribute($nameKey);
        $value = $request->getAttribute($valueKey);

        $args = array_merge($args, [
            "csrf" => [
                "nameKey"  => $nameKey,
                "name"     => $name,
                "valueKey" => $valueKey,
                "value"    => $value
            ]
        ], $this->container->flash->getMessages());

        $response = $this->container->view->render($response, $page, $args);
        return $response;
    }

    public function redirectWithMessage(&$response, $namedRoute, $message, $content, $args = [], $params = [])
    {
        $this->container->flash->addMessage($message, $content);

        $response = $response->withRedirect($this->container->router->pathFor($namedRoute, $args, $params), 301);
        return $response;
    }

    public function sendFile(string $filepath, string $filename = null)
    {
        if (!is_file($filepath)) {
            return false;
        }
        
        if (is_null($filename)) {
            $filename = $filepath;
        }
        
        $fh = fopen($filepath, 'rb');

        $stream = new \Slim\Http\Stream($fh);

        return $response->withHeader('Content-Type', 'application/force-download')
                        ->withHeader('Content-Type', 'application/octet-stream')
                        ->withHeader('Content-Type', 'application/download')
                        ->withHeader('Content-Description', 'File Transfer')
                        ->withHeader('Content-Transfer-Encoding', 'binary')
                        ->withHeader('Content-Disposition', 'attachment; filename="' . basename($filename) . '"')
                        ->withHeader('Expires', '0')
                        ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                        ->withHeader('Pragma', 'public')
                        ->withBody($stream);
    }
}
