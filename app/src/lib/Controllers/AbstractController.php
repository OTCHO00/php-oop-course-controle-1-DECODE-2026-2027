<?php

namespace App\Lib\Controllers;

use App\Lib\Http\Response;
use App\Lib\Http\Request;

abstract class AbstractController {
    public abstract function process(Request $request): Response;

    protected function jsonResponse(int $statusCode, array $data): Response {

        $response = new Response();
        $response->statusCode = $statusCode;
        $response->headers["Content-Type"] = "application/json";
        $response->content = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return $response;

    }

}

