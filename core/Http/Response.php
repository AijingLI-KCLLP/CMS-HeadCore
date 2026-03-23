<?php

namespace Core\Http;

class Response {
    private string $content;
    private int $status;
    private array $headers;

    public static function json(mixed $data, int $status = 200, array $headers = []): self {
        $content = json_encode($data, JSON_UNESCAPED_UNICODE);
        if ($content === false) {
            $content = json_encode(['error' => 'Failed to encode JSON']);
            $status = 500;
        }
        $headers['Content-Type'] = 'application/json';
        return new self($content, $status, $headers);
    }

    public static function error(string $message, int $status): self {
        return self::json(['error' => $message], $status);
    }

    public function send(): void {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    header("$name: $v");
                }
            } else {
                header("$name: $value");
            }
        }
        echo $this->content;
    }

    public function __construct(string $content = '', int $status = 200, array $headers = [])
    {
        $this->content = $content;
        $this->status = $status;
        $this->headers = $headers;
    }
    public function setContent(string $content): void {
        $this->content = $content;
    }

    public function getContent(): string {
        return $this->content;
    }
    
    public function setStatus(int $status): void{
        $this->status = $status;
    }
    
    public function getStatus(): int {
        return $this->status;
    }
    
    public function setHeaders(array $headers): void{
        $this->headers = $headers;
    }

    public function addHeader(string $name, string $value): void{
        $this->headers[$name] = $value;
    }
    
    public function getHeaders(): array {
        return $this->headers;
    }

    public function getHeadersAsString(): string {
        $headersAsString = '';
        foreach($this->getHeaders() as $headerName => $headerValue) {
            $headersAsString .= "$headerName: $headerValue\n";
        }

        return $headersAsString;
    }
}


?>
