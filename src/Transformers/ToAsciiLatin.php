<?php

declare(strict_types=1);

namespace Turanjanin\SerbianLanguageTools\Transformers;

use Turanjanin\SerbianLanguageTools\Text;

class ToAsciiLatin extends WordTransformer
{
    protected function getMap(): array
    {
        return [
            'Č' => 'C',
            'Ć' => 'C',
            'Đ' => 'DJ',
            'Š' => 'S',
            'Ž' => 'Z',
            'č' => 'c',
            'ć' => 'c',
            'đ' => 'dj',
            'š' => 's',
            'ž' => 'z',
            'Đa' => 'Dja',
            'Đe' => 'Dje',
            'Đi' => 'Dji',
            'Đo' => 'Djo',
            'Đu' => 'Dju',
        ];
    }

    public function __invoke(Text $text): Text
    {
        $text = (new ToLatin)($text);

        return parent::__invoke($text);
    }
}
