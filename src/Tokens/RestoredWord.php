<?php

declare(strict_types=1);

namespace Turanjanin\SerbianLanguageTools\Tokens;

class RestoredWord extends Word
{
    private int $frequency;
    private Word $originalWord;

    public function __construct(array $characters, int $frequency, Word $originalWord)
    {
        $this->frequency = $frequency;
        $this->originalWord = $originalWord;

        parent::__construct($characters);
    }

    public function getFrequency(): int
    {
        return $this->frequency;
    }

    public function getOriginalWord(): Word
    {
        return $this->originalWord;
    }

    public static function fromString(string $string)
    {
        throw new \RuntimeException('You cannot create new instance from string - frequency and original word are missing.');
    }

    public static function fromStringWithMatchingCase(string $string, int $frequency, Word $original): self
    {
        $matchedCaseCharacters = self::matchCase(mb_str_split($string), $original->getCharacters());

        return new self($matchedCaseCharacters, $frequency, $original);
    }

    protected static function matchCase(array $replacement, array $original): array
    {
        $replacementLength = count($replacement);
        $originalLength = count($original);

        $separatorCount = array_count_values($replacement)[Word::CHARACTER_SEPARATOR] ?? 0;

        if ($originalLength + $separatorCount < $replacementLength) {
            // This shouldn't happen but let's keep this check, to avoid undefined index errors.
            return $replacement;
        }

        for ($oi = 0, $ri = 0; $ri < $replacementLength; $oi++, $ri++) {
            if ($replacement[$ri] === $original[$oi]) {
                continue;
            }

            if ($replacement[$ri] === Word::CHARACTER_SEPARATOR) {
                $ri++;
            }

            if (mb_strtoupper($original[$oi]) === $original[$oi]) {
                $replacement[$ri] = mb_strtoupper($replacement[$ri]);
            } else {
                $replacement[$ri] = mb_strtolower($replacement[$ri]);
            }

            if (in_array($original[$oi], ['d', 'D']) && in_array($replacement[$ri], ['đ', 'Đ'])) {
                // Skip additional letter in DJ
                $oi++;
            }
        }

        return $replacement;
    }
}
