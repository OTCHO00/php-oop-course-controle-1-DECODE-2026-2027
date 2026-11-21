<?php

namespace App\Controllers\Contact;

use App\Lib\Controllers\AbstractController;
use App\Lib\Http\Request;
use App\Lib\Http\Response;

class GetContactById extends AbstractController {
    private const string CONTACT_DIR = __DIR__ . '/../../../var/contacts';
    private const array KEYS = ['email', 'subject', 'message', 'dateOfCreation', 'dateOfLastUpdate'];

    public function process(Request $request): Response {

        $fichier = basename($request->getRouteParam('filename')) ?? "";

        if ($fichier === "") {
            return $this->jsonResponse(404, ['error' => 'Contact not found']);
        }

        $f = self::CONTACT_DIR . '/' . $fichier . '.json';

        if (is_file($f) === false) {
            return $this->jsonResponse(404, ['error' => 'Contact not found']);
        }

        try {
            $data = json_decode(file_get_contents($f), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return $this->jsonResponse(500, ['error' => 'File Corruptedd']);
        }
        
        if (is_array($data) === false || array_is_list($data) === true) {
            return $this->jsonResponse(500, ['error' => 'File Corrupted']);
        }

        $keys = array_keys($data);
        $expectedKeys = self::KEYS;
        sort($keys);
        sort($expectedKeys);

        if ($keys !== $expectedKeys) {
            return $this->jsonResponse(500, ['error' => 'File Corrupted']);
        }
        
        return $this->jsonResponse(200, $data);
    }
}