<?php

namespace Core\Http;

class Request {
    private string $uri;
    private string $path;
    private string $method;
    private array $headers;
    private array $slugs;
    private array $urlParams;
    private string $payload;

    public function __construct() {
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->path = parse_url($this->uri, PHP_URL_PATH);
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->headers = getallheaders();
        $this->urlParams = $_GET;
        $this->payload = file_get_contents('php://input');
    }

    public function getUri(): string {
        return $this->uri;
    }

    public function getPath(): string {
        return $this->path;
    }

    public function getMethod(): string {
        return $this->method;
    }

    public function addSlug(string $key, string $value): self {
        $this->slugs[$key] = $value;
        
        return $this;
    }

    public function getSlugs(): array {
        return $this->slugs;
    }

    public function getSlug(string $key): ?string {
        return $this->slugs[$key] ?? null;
    }

    public function getUrlParams(): array {
        return $this->urlParams;
    }
    
    public function getHeaders(): array {
        return $this->headers;
    }

    public function getPayload(): string {
        return $this->payload;
    }

    public function getContentType(): string {
        return $this->headers['Content-Type'] ?? '';
    }

    public function expectsJson(): bool {
        $accept = $this->headers['Accept'] ?? '';
        $contentType = $this->getContentType();
        return str_contains($accept, 'application/json') || str_contains($contentType, 'application/json');
    }

    public function getJsonBody(): array {
        $data = json_decode($this->payload, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON body: ' . json_last_error_msg());
        }
        return $data ?? [];
    }

    public function getFormBody(): array {
        return $_POST;
    }
}
