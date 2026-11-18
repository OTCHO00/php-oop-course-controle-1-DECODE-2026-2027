<?php

namespace App\Controllers\Contact;

use App\Lib\Controllers\AbstractController;
use App\Lib\Http\Request;
use App\Lib\Http\Response;

class CreateContactController extends AbstractController {
    private const string CONTACTS_PATH = __DIR__ . '/../../../var/contacts';
    private const array ALLOWED_KEYS = ['email', 'subject', 'message'];

    public function process(Request $request): Response {

    $headers = array_change_key_case($request->getHeaders(), CASE_LOWER);
        $contentType = $headers['content-type'] ?? '';

        if (str_starts_with($contentType, 'application/json') === false) {
            return $this->jsonResponse(400, ['error' => 'Content-Type must be application/json']);
        }

        try {
            $data = json_decode($request->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return $this->jsonResponse(400, ['error' => 'Invalid JSON body']);
        }

        if (is_array($data) === false || array_is_list($data) === true) {
            return $this->jsonResponse(400, ['error' => 'Body must be a JSON object']);
        }

        $keys = array_keys($data);
        $expectedKeys = self::ALLOWED_KEYS;
        sort($keys);
        sort($expectedKeys);

        if ($keys !== $expectedKeys) {
            return $this->jsonResponse(400, ['error' => 'Body must only contain email, subject and message']);
        }

        foreach (self::ALLOWED_KEYS as $key) {
            if (is_string($data[$key]) === false || trim($data[$key]) === '') {
                return $this->jsonResponse(400, ['error' => $key . ' must be a non empty string']);
            }
        }

        if (filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
            return $this->jsonResponse(400, ['error' => 'email is not valid']);
        }

        if (is_dir(self::CONTACTS_PATH) === false && mkdir(self::CONTACTS_PATH, 0775, true) === false) {
            return $this->jsonResponse(500, ['error' => 'Unable to create contacts directory']);
        }

        $timestamp = time();
        $fileName = $timestamp . '_' . $data['email'] . '.json';

        $contact = [
            'email' => $data['email'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'dateOfCreation' => $timestamp,
            'dateOfLastUpdate' => $timestamp,
        ];

        $saved = file_put_contents(
            self::CONTACTS_PATH . '/' . $fileName,
            json_encode($contact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );

        if ($saved === false) {
            return $this->jsonResponse(500, ['error' => 'Unable to save contact']);
        }

        return $this->jsonResponse(201, ['file' => $fileName]);
    }
}