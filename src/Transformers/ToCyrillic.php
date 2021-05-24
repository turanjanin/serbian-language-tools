<?php

declare(strict_types=1);

namespace Turanjanin\SerbianLanguageTools\Transformers;

use Turanjanin\SerbianLanguageTools\Dictionary\Dictionary;
use Turanjanin\SerbianLanguageTools\Dictionary\SqliteDictionary;
use Turanjanin\SerbianLanguageTools\Tokens\RestoredWord;
use Turanjanin\SerbianLanguageTools\Tokens\Word;

class ToCyrillic extends WordTransformer
{
    protected Dictionary $dictionary;

    public function __construct(Dictionary $dictionary = null)
    {
        if ($dictionary === null) {
            $dictionary = new SqliteDictionary();
        }

        $this->dictionary = $dictionary;
    }

    protected function getMap(): array
    {
        $separator = Word::CHARACTER_SEPARATOR;

        return [
            'a' => 'а',
            'b' => 'б',
            'c' => 'ц',
            'č' => 'ч',
            'ć' => 'ћ',
            'd' => 'д',
            'dž' => 'џ',
            'đ' => 'ђ',
            'e' => 'е',
            'f' => 'ф',
            'g' => 'г',
            'h' => 'х',
            'i' => 'и',
            'j' => 'ј',
            'k' => 'к',
            'l' => 'л',
            'lj' => 'љ',
            'm' => 'м',
            'n' => 'н',
            'nj' => 'њ',
            'o' => 'о',
            'p' => 'п',
            'r' => 'р',
            's' => 'с',
            'š' => 'ш',
            't' => 'т',
            'u' => 'у',
            'v' => 'в',
            'z' => 'з',
            'ž' => 'ж',

            'A' => 'А',
            'B' => 'Б',
            'C' => 'Ц',
            'Č' => 'Ч',
            'Ć' => 'Ћ',
            'D' => 'Д',
            'Dž' => 'Џ',
            'Đ' => 'Ђ',
            'E' => 'Е',
            'F' => 'Ф',
            'G' => 'Г',
            'H' => 'Х',
            'I' => 'И',
            'J' => 'Ј',
            'K' => 'К',
            'L' => 'Л',
            'LJ' => 'Љ',
            'M' => 'М',
            'N' => 'Н',
            'NJ' => 'Њ',
            'O' => 'О',
            'P' => 'П',
            'R' => 'Р',
            'S' => 'С',
            'Š' => 'Ш',
            'T' => 'Т',
            'U' => 'У',
            'V' => 'В',
            'Z' => 'З',
            'Ž' => 'Ж',

            'DJ' => 'Ђ',
            'dj' => 'ђ',
            "d{$separator}j" => 'дј',
            "D{$separator}j" => 'Дј',
            "D{$separator}J" => 'ДЈ',
            'DŽ' => 'Џ',
            "d{$separator}ž" => 'дж',
            "D{$separator}ž" => 'Дж',
            "D{$separator}Ž" => 'ДЖ',
            'Lj' => 'Љ',
            "l{$separator}j" => 'лј',
            "L{$separator}j" => 'Лј',
            "L{$separator}J" => 'ЛЈ',
            'Nj' => 'Њ',
            "n{$separator}j" => 'нј',
            "N{$separator}j" => 'Нј',
            "N{$separator}J" => 'НЈ'
        ];
    }

    protected function shouldBeReplaced(Word $word): bool
    {
        return $word->containsOnlySerbianLatin();
    }

    protected function replaceWord(Word $word): Word
    {
        if ($word->hasPotentialDigraph()) {
            $word = $this->splitPotentialDigraph($word);
        }

        return parent::replaceWord($word);
    }

    protected function splitPotentialDigraph(Word $word): Word
    {
        $string = mb_strtolower($word->__toString());

        if ($exception = $this->dictionary->getDigraphException($string)) {
            $frequency = 100000;
            $word = RestoredWord::fromStringWithMatchingCase($exception, $frequency, $word);
        }

        return $word;
    }
}
