<?php

namespace controller\lists\admin;

class upload {

    use \traits\sendResponse;
    
    protected $container;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    function __invoke($request, $response) {

        $data = $request->getParsedBody();

        $name = filter_var(@$data['name'], FILTER_SANITIZE_STRING);
        if ($this->container->db->has("lists_versions", ["name" => $name]))
            return $this->redirectWithMessage($response, 'lists', "error", ["Období " . $name . " již existuje"]);

        $directory = $this->container['settings']['upload_directory'];
        $uploadedFiles = $request->getUploadedFiles();

        $uploadedFile = $uploadedFiles['book'];
        if (!$uploadedFile)
            return $this->redirectWithMessage($response, 'lists', "error", ["Soubor neodeslán"]);

        if ($uploadedFile->getError() !== UPLOAD_ERR_OK)
            return $this->redirectWithMessage($response, 'lists', "error", ["Chyba nahrávání"]);
        
        $filename = $this->moveUploadedFile($directory, $uploadedFile);
        $parsed = $this->validateFile($directory . "/" . $filename);
        unlink($directory . "/" . $filename);
        if (!is_array($parsed))
            return $this->redirectWithMessage($response, 'lists', "error", ["Špatný formát", "Chyba na řádku " . $parsed]);
        
        $this->container->db->insert("lists_versions", ["name" => $name]);
        $version = $this->container->db->id();

        $save = [];
        foreach ($parsed as $entry) {
            array_push($save, [
                "name"    => $entry[2],
                "author"  => $entry[1],
                "region"  => intval($entry[0]),
                "genere"  => intval($entry[3]),
                "version" => $version
            ]);
        }
        $this->container->db->insert("lists_books", $save);
        $this->redirectWithMessage($response, 'lists', "status", [count($save) . " knih nahráno"]);
        
        return $response;
    }

    private function validateFile($filename) {
        $f = fopen($filename, "r");
        if (!$f) return 0;
        $list = [];
        $line = 0;
        while (($data = fgetcsv($f, 0, ";")) !== false) {
            if (count($data) != 4)
                return $line;
            if (
                !is_numeric($data[0]) ||
                !is_numeric($data[3]) ||
                strlen($data[2]) == 0
            )
                return $line;
            $list[$line] = $data;
            $line++;
        }
        return $list;
    }

    private function moveUploadedFile($directory, \Slim\Http\UploadedFile $uploadedFile) {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8));
        $filename = sprintf('%s.%0.8s', $basename, $extension);
    
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
    
        return $filename;
    }
}
