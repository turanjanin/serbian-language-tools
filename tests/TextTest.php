<?php

declare(strict_types=1);

namespace Turanjanin\SerbianLanguageTools\Tests;

use Turanjanin\SerbianLanguageTools\Tests\TestCase;
use Turanjanin\SerbianLanguageTools\Text;

class TextTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $sentence = 'Zdravo svima, ovo je moj novi projekat! ;)';

        $text = Text::fromString($sentence);
        $this->assertSame($sentence, $text->toString());
    }

    /** @test */
    public function it_can_return_if_text_is_in_cyrillic_script()
    {
        $this->assertTrue(Text::fromString('Четири чавке чучећи цијучу')->isCyrillic());
        $this->assertFalse(Text::fromString('Slušaj sada, pričam ti o ćudljivoj životinji od koje se prave džemperi.')->isCyrillic());

        $this->assertFalse(Text::fromString('')->isCyrillic());
    }

    /** @test */
    public function it_can_return_if_text_is_in_latin_script()
    {
        $this->assertFalse(Text::fromString('Четири чавке чучећи цијучу')->isLatin());
        $this->assertTrue(Text::fromString('Slušaj sada, pričam ti o ćudljivoj životinji od koje se prave džemperi.')->isLatin());

        $this->assertFalse(Text::fromString('')->isLatin());
    }
}
