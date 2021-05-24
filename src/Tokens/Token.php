<?php

declare(strict_types=1);

namespace Turanjanin\SerbianLanguageTools\Tokens;

abstract class Token
{
    protected array $characters;

    public function __construct(array $characters)
    {
        $this->characters = $characters;
    }

    public function getCharacters(): array
    {
        return $this->characters;
    }

    /** @return static */
    public static function fromString(string $string)
    {
        return new static(mb_str_split($string));
    }

    public function __toString()
    {
        return implode('', $this->characters);
    }
}
