<?php

namespace App\Core;

class Response
{
    private function __construct(
        private readonly mixed $data,
        private readonly int $status
    ) {
    }

    public static function json(array $data, int $status = 200): self
    {
        return new self($data, $status);
    }

    public function send(): void
    {
        http_response_code($this->status);
        header('Content-Type: application/json');
        echo json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
