<?php

namespace App\Lib\Http;

class Request {
    private readonly string $uri;
    private readonly string $method;
    private readonly array $headers;
    private readonly string $body;
    private array $routeParams;

    public function __construct()
    {
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->headers = getallheaders();
        $this->body = file_get_contents('php://input') ?: "";
        $this->routeParams = [];
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): string 
    {
        return $this->body;
    }

    public function setRouteParams(array $params)
    {
        $this->routeParams = $params;
    }

    public function getRouteParam(string $name): ?string 
    {
        return $this->routeParams[$name] ?? null;
    }
}