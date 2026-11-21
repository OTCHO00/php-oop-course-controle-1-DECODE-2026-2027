<?php

namespace App\Controllers\Contact;

use App\Lib\Controllers\AbstractController;
use App\Lib\Http\Request;
use App\Lib\Http\Response;

class UpdateContactController extends AbstractController {
    private const string CONTACT_DIR = __DIR__ . '/../../../var/contacts';
    private const array ALLOWED_KEYS = ['email', 'subject', 'message'];
    private const array KEYS = ['email', 'subject', 'message', 'dateOfCreation', 'dateOfLastUpdate'];

    public function process(Request $request): Response {

        $fichier = basename($request->getRouteParam('filename') ?? '');

        if ($fichier === '') {
            return $this->jsonResponse(404, ['error' => 'Contact not found']);
        }

        $f = self::CONTACT_DIR . '/' . $fichier . '.json';

        if (is_file($f) === false) {
            return $this->jsonResponse(404, ['error' => 'Contact not found']);
        }

        $headers = array_change_key_case($request->getHeaders(), CASE_LOWER);
        $contentType = $headers['content-type'] ?? '';

        if (str_starts_with($contentType, 'application/json') === false) {
            return $this->jsonResponse(400, ['error' => 'Content-Type must be application/json']);
        }

        try {
            $data = json_decode($request->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return $this->jsonResponse(400, ['error' => 'Invalid Json']);
        }

        if (is_array($data) === false || array_is_list($data) === true) {
            return $this->jsonResponse(400, ['error' => 'Invalid Json']);
        }

        $forbiddenKeys = array_diff(array_keys($data), self::ALLOWED_KEYS);

        if (count($forbiddenKeys) > 0) {
            return $this->jsonResponse(400, ['error' => 'Invalid Json']);
        }

        foreach ($data as $key => $value) {
            if (is_string($value) === false || trim($value) === '') {
                return $this->jsonResponse(400, ['error' => $key . ' is empty']);
            }
        }

        if (isset($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
            return $this->jsonResponse(400, ['error' => 'email is not good']);
        }

        try {
            $contact = json_decode(file_get_contents($f), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return $this->jsonResponse(500, ['error' => 'File corrupted']);
        }

        if (is_array($contact) === false || array_is_list($contact) === true) {
            return $this->jsonResponse(500, ['error' => 'File corrupted']);
        }

        $keys = array_keys($contact);
        $expectedKeys = self::KEYS;
        sort($keys);
        sort($expectedKeys);

        if ($keys !== $expectedKeys) {
            return $this->jsonResponse(500, ['error' => 'File corrupted']);
        }

        foreach ($data as $key => $value) {
            $contact[$key] = $value;
        }

        $contact['dateOfLastUpdate'] = time();

        $saved = file_put_contents($f, json_encode($contact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        if ($saved === false) {
            return $this->jsonResponse(500, ['error' => "Can't save contact"]);
        }

        return $this->jsonResponse(200, $contact);
    }
}