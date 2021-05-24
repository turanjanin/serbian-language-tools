<?php

declare(strict_types=1);

namespace Turanjanin\SerbianLanguageTools\Dictionary;

class Variant
{
    public string $word;
    public int $frequency;

    public function __construct(string $word, int $frequency)
    {
        $this->word = $word;
        $this->frequency = $frequency;
    }
}
