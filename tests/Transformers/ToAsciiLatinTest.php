<?php

declare(strict_types=1);

namespace Turanjanin\SerbianLanguageTools\Tests\Transformers;

use Turanjanin\SerbianLanguageTools\Tests\TestCase;
use Turanjanin\SerbianLanguageTools\Transformers\ToAsciiLatin;

class ToAsciiLatinTest extends TestCase
{
    /** @test */
    public function it_can_transliterate_latin_to_ascii_latin()
    {
        $latin = 'Šefe, čiji je dođavola ovaj žuti džemper iz Ćuprije?';
        $ascii = 'Sefe, ciji je dodjavola ovaj zuti dzemper iz Cuprije?';

        $asciiText = (new ToAsciiLatin)($this->getTokens($latin));
        $this->assertSame($ascii, $asciiText->toString());
    }

    /** @test */
    public function it_can_transliterate_cyrillic_to_ascii_latin()
    {
        $cyrillic = 'Фијуче ветар у шибљу, леди пасаже и куће иза њих и гунђа у оџацима.';
        $ascii = 'Fijuce vetar u siblju, ledi pasaze i kuce iza njih i gundja u odzacima.';

        $asciiText = (new ToAsciiLatin)($this->getTokens($cyrillic));
        $this->assertSame($ascii, $asciiText->toString());
    }
}
