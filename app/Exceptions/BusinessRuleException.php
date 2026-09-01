<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown when a request is well-formed but violates a ClassHub business rule
 * (e.g. duplicate enrollment, class at capacity). The Router catches this
 * and returns the standard 422 error response.
 */
class BusinessRuleException extends Exception
{
    public function __construct(string $message, private readonly string $code)
    {
        parent::__construct($message);
    }

    public function errorCode(): string
    {
        return $this->code;
    }
}
