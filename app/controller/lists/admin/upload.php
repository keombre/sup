<?php

namespace controller\lists\admin;

class upload {

    use \traits\sendResponse;
    
    protected $container;

    function __construct(\Slim\Container $container) {
        $this->container = $container;
    }

    function __invoke($request, $response) {

        $directory = $this->container['settings']['upload_directory'];
        $uploadedFiles = $request->getUploadedFiles();

        $uploadedFile = $uploadedFiles['book'];
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = $this->moveUploadedFile($directory, $uploadedFile);
            $parsed = $this->validateFile($directory . "/" . $filename);
            unlink($directory . "/" . $filename);
            if (!is_array($parsed)) {
                $this->redirectWithMessage($response, 'dashboard', "error", ["Špatný formát", "Chyba na řádku " . $parsed]);
            } else {
                $save = [];
                foreach ($parsed as $entry) {
                    array_push($save, [
                        "id" => intval ($entry[0]),
                        "name" => $entry[3],
                        "author" => $entry[2],
                        "region" => intval($entry[1]),
                        "genere" => intval($entry[4])
                    ]);
                }
                $this->container->db->delete("lists_books", ["id[!]" => null]);
                $this->container->db->insert("lists_books", $save);
                $this->redirectWithMessage($response, 'lists', "status", [count($save) . " knih nahráno"]);
            }
        } else {
            $this->redirectWithMessage($response, 'dashboard', "error", ["Chyba nahrávání"]);
        }
        return $response;
    }

    private function validateFile($filename) {
        $f = fopen($filename, "r");
        if (!$f) return 0;
        $char;
        $list = [];
        $entry = ["", "", "", "", ""];
        $state = 0;
        while (!feof($f)) {
            $char = fread($f, 1);
            switch ($state) {
                case 0:
                    if ($char == ";") $state = 1;
                    else if ($char == "\n") return count($list) + 1;
                    else $entry[0] .= $char;
                    break;
                case 1:
                    if ($char == ";") $state = 2;
                    else if ($char == "\n") return count($list) + 1;
                    else $entry[1] .= $char;
                    break;
                case 2:
                    if ($char == ";") $state = 3;
                    else if ($char == "\n") return count($list) + 1;
                    else $entry[2] .= $char;
                    break;
                case 3:
                    if ($char == ";") $state = 4;
                    else if ($char == "\n") {
                        array_push($list, $entry);
                        $entry = ["", "", "", "", ""];
                        $state = 0;
                    }
                    else $entry[3] .= $char;
                    break;
                case 4:
                    if ($char == ";") return count($list) + 1;
                    else if ($char == "\n") {
                        array_push($list, $entry);
                        $entry = ["", "", "", "", ""];
                        $state = 0;
                    }
                    else $entry[4] .= $char;
                    break;
            }
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
