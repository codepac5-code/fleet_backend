<?php

namespace App\Http\Core\Exceptions;

use RuntimeException;

class DomainException extends RuntimeException
{
    public function __construct(
        public readonly string $errorCode,
        public readonly int $status = 422,
        string $message = ''
    ) {
        parent::__construct($message !== '' ? $message : str_replace('_', ' ', $errorCode) . '.');
    }

    public static function make(string $errorCode, int $status = 422, string $message = ''): self
    {
        return new self($errorCode, $status, $message);
    }

    public static function notFound(string $errorCode = 'not_found', string $message = ''): self
    {
        return new self($errorCode, 404, $message);
    }

    public static function conflict(string $errorCode, string $message = ''): self
    {
        return new self($errorCode, 409, $message);
    }
}
