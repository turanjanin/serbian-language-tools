<?php

declare(strict_types=1);

namespace Turanjanin\SerbianLanguageTools\Tokens;

class Word extends Token
{
    public const CHARACTER_SEPARATOR = '!';

    public function hasDiacritic(): bool
    {
        return $this->containsCharacters(['č', 'ć', 'ž', 'š', 'Č', 'Ć', 'Ž', 'Š']);
    }

    public function hasDj(): bool
    {
        $string = mb_strtolower($this->__toString());

        return strpos($string, 'dj') !== false;
    }

    public function hasPotentialDigraph(): bool
    {
        $digraphs = [
            'dj',
            'lj',
            'nj',
            'dz',
            'dž',
        ];

        $string = mb_strtolower($this->__toString());

        foreach ($digraphs as $digraph) {
            if (strpos($string, $digraph) !== false) {
                return true;
            }
        }

        return false;
    }

    public function containsOnlySerbianLatin(): bool
    {
        $latinLetters = [
            'a', 'b', 'c', 'č', 'ć', 'd', 'ž', 'đ', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 'š', 't', 'u', 'v', 'z', 'ž',
            'A', 'B', 'C', 'Č', 'Ć', 'D', 'Ž', 'Đ', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'Š', 'T', 'U', 'V', 'Z', 'Ž',
            '-', "'",
            Word::CHARACTER_SEPARATOR,
        ];

        return count(array_diff($this->characters, $latinLetters)) === 0;
    }

    public function containsOnlySerbianCyrillic(): bool
    {
        $cyrillicLetters = [
            'а', 'б', 'в', 'г', 'д', 'ђ', 'е', 'ж', 'з', 'и', 'ј', 'к', 'л', 'љ', 'м', 'н', 'њ', 'о', 'п', 'р', 'с', 'т', 'ћ', 'у', 'ф', 'х', 'ц', 'ч', 'џ', 'ш',
            'А', 'Б', 'В', 'Г', 'Д', 'Ђ', 'Е', 'Ж', 'З', 'И', 'Ј', 'К', 'Л', 'Љ', 'М', 'Н', 'Њ', 'О', 'П', 'Р', 'С', 'Т', 'Ћ', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Џ', 'Ш',
            '-', "'",
            Word::CHARACTER_SEPARATOR,
        ];

        return count(array_diff($this->characters, $cyrillicLetters)) === 0;
    }

    public function isRestorationCandidate(): bool
    {
        if ($this->hasDiacritic()) {
            return false;
        }

        return $this->containsCharacters(['c', 's', 'z', 'C', 'S', 'Z']) || $this->hasDj();
    }

    private function containsCharacters(array $characters): bool
    {
        return count(array_intersect($characters, $this->characters)) > 0;
    }

    public function __toString()
    {
        return implode('', array_diff($this->characters, [static::CHARACTER_SEPARATOR]));
    }
}
