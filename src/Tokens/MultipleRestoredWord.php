<?php

declare(strict_types=1);

namespace Turanjanin\SerbianLanguageTools\Tokens;

class MultipleRestoredWord extends RestoredWord
{
    private array $variants;

    /**
     * @param \Turanjanin\SerbianLanguageTools\Tokens\RestoredWord[] $variants
     */
    public function __construct(array $variants)
    {
        $bestMatch = $variants[0];
        $this->variants = $variants;

        parent::__construct($bestMatch->getCharacters(), $bestMatch->getFrequency(), $bestMatch->getOriginalWord());
    }

    public function getVariants(): array
    {
        return $this->variants;
    }
}
