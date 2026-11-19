<?php

namespace App\Controllers\Contact;

use App\Lib\Controllers\AbstractController;
use App\Lib\Http\Request;
use App\Lib\Http\Response;

class GetAllContact extends AbstractController {
    private const string CONTACT_DIR = __DIR__ . '/../../../var/contacts';
    private const array KEYS = ['email', 'subject', 'message', 'dateOfCreation', 'dateOfLastUpdate'];

    public function process(Request $request): Response {

        $fichiers = glob(self::CONTACT_DIR . '/*.json') ?: [];
        $contacts = [];

        foreach ($fichiers as $fichier) {

            if (is_file($fichier)) {

                $contenu = file_get_contents($fichier);

                try {
                    $data = json_decode($contenu, true, 512, JSON_THROW_ON_ERROR);

                    if (is_array($data) === false || array_is_list($data) === true) {
                        continue;
                    }

                    $keys = array_keys($data);
                    $exceptedKeys = self::KEYS;
                    sort($keys);
                    sort($exceptedKeys);

                    if ($keys === $exceptedKeys) {
                        $contacts[] = $data;
                    }

                } catch (\JsonException $e) {
                    continue;
                }
            }

        }
        
        return $this->jsonResponse(200, $contacts);
    }
}