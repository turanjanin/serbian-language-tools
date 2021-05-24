<?php

declare(strict_types=1);

namespace Turanjanin\SerbianLanguageTools\Exceptions;

use Throwable;

class InvalidDatabaseException extends \RuntimeException
{
    public static function forFilename(string $filename, Throwable $previous = null): self
    {
        $message = "Invalid database file provided: {$filename}";

        return new self($message, 0, $previous);
    }
}
