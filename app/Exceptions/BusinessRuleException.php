<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown when a request is well-formed but violates a ClassHub business rule
 * (e.g. duplicate enrollment, class at capacity). Controllers should catch
 * this and translate $code into the standard error response format
 * documented in classhub_technical_specs.md.
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
