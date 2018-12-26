<?php

namespace controller\lists;

class preview extends lists {

    protected $state = null;

    public function student($request, &$response, $args) {
        $this->getState();

        if ($this->state == 0)
            return $response->withRedirect($this->container->router->pathFor('lists-edit', ["id" => $this->listID]), 301);

        return $this->preview($request, $response, $args);
    }

    public function teacher($request, &$response, $args) {
        $response->getBody()->write("kitten");
    }

    public function admin($request, &$response, $args) {
        $response->getBody()->write("kitten");
    }

    private function getState() {
        $this->state = $this->container->db->get('lists_main', 'state', ['id' => $this->listID]);
    }

    public function preview($request, &$response, $args) {
        if (is_null($this->state))
            $this->getState();

        $qrURL = (string) $request->getUri()->withPath($this->container->router->pathFor("lists-accept", ["id" => $this->listID]))->withQuery("")->withFragment("");

        $qrcode = (new \chillerlan\QRCode\QRCode(new \chillerlan\QRCode\QROptions([
            'outputType' => \chillerlan\QRCode\QRCode::OUTPUT_IMAGE_PNG,
        ])))->render($qrURL);

        $generatorPNG = new \Picqer\Barcode\BarcodeGeneratorPNG();
        $barcode = base64_encode($generatorPNG->getBarcode($this->listID, $generatorPNG::TYPE_EAN_13, 2.5));
        
        $versionName = $this->db->get('lists_versions', 'name', ['id' => $this->settings['active_version']]);
        
        $books = [];
        foreach ($this->container->db->select("lists_books", "*") as $book)
            $books[$book['id']] = $book;
        
        $list = [];
        foreach ($this->container->db->select("lists_lists", "book", ["list" => $this->listID]) as $book)
            array_push($list, $books[$book]);

        $this->sendResponse($request, $response, "lists/preview.phtml", [
            "list" => $list,
            "listID" => $this->listID,
            "print" => $this->state != 0,
            "barcode" => $barcode,
            "qrcode" => $qrcode,
            "version" => $versionName
        ]);
        return $response;
    }
}
