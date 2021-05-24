<?php

declare(strict_types=1);

namespace Turanjanin\SerbianLanguageTools\Dictionary;

interface Dictionary
{
    /**
     * @return string[]
     */
    public function getPhrases(): array;

    /**
     * @param string $word
     * @return \Turanjanin\SerbianLanguageTools\Dictionary\Variant[]
     */
    public function getAsciiVariants(string $word): array;

    public function getDigraphException(string $word): ?string;
}
