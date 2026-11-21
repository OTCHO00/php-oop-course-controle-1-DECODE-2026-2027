<?php

namespace App\Controllers\Contact;

use App\Lib\Controllers\AbstractController;
use App\Lib\Http\Request;
use App\Lib\Http\Response;

class DeleteContactController extends AbstractController {
    private const string CONTACT_DIR = __DIR__ . '/../../../var/contacts';

    public function process(Request $request): Response {

        $fichier = basename($request->getRouteParam('filename') ?? '');

        if ($fichier === '') {
            return $this->jsonResponse(404, ['error' => 'Contact not found']);
        }

        $f = self::CONTACT_DIR . '/' . $fichier . '.json';

        if (is_file($f) === false) {
            return $this->jsonResponse(404, ['error' => 'Contact not found']);
        }

        if (unlink($f) === false) {
            return $this->jsonResponse(500, ['error' => 'Impossible to delete contact']);
        }

        $response = new Response();
        $response->statusCode = 204;
        $response->content = '';

        return $response;
    }
}