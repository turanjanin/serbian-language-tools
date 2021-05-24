<?php

declare(strict_types=1);

namespace Turanjanin\SerbianLanguageTools\Tests\Fakes;

use Turanjanin\SerbianLanguageTools\Dictionary\Dictionary;
use Turanjanin\SerbianLanguageTools\Dictionary\Variant;

class InMemoryDictionary implements Dictionary
{
    private array $asciiVariants = [];
    private array $digraphExceptions = [];
    private array $phrases = [];

    public array $searchedVariants = [];
    public array $searchedExceptions = [];

    public function getPhrases(): array
    {
        return $this->phrases;
    }

    public function getAsciiVariants(string $word): array
    {
        $this->searchedVariants[] = $word;

        return $this->asciiVariants[$word] ?? [];
    }

    public function getDigraphException(string $word): ?string
    {
        $this->searchedExceptions[] = $word;

        return $this->digraphExceptions[$word] ?? null;
    }

    public function addPhrase(string $phrase): void
    {
        $this->phrases[] = $phrase;
    }

    public function addAsciiVariant(string $word, Variant $variant): void
    {
        $this->asciiVariants[$word][] = $variant;
    }

    public function addDigraphException(string $word, string $separated): void
    {
        $this->digraphExceptions[$word] = $separated;
    }
}
