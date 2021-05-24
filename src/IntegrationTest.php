<?php

namespace Turanjanin\SerbianLanguageTools;

use Turanjanin\SerbianLanguageTools\Tests\TestCase;
use Turanjanin\SerbianLanguageTools\Transformers\DiacriticRestorer;
use Turanjanin\SerbianLanguageTools\Transformers\ToAsciiLatin;
use Turanjanin\SerbianLanguageTools\Transformers\ToCyrillic;
use Turanjanin\SerbianLanguageTools\Transformers\ToLatin;

class IntegrationTest extends TestCase
{
    /** @test */
    public function it_can_restore_diacritics()
    {
        $text = Text::fromString('Cao ljudi, sta kazete o odjavi dosadasnje price o Djokovicu?');
        $restored = (new DiacriticRestorer)($text);

        $this->assertNotSame($text->toString(), $restored->toString());

        $this->assertSame('Ćao ljudi, šta kažete o odjavi dosadašnje priče o Đokoviću?', $restored->toString());
    }

    /** @test */
    public function it_can_restore_diacritics_and_convert_to_cyrillic()
    {
        $text = Text::fromString('<p>Sto puta sam vam rekao da ce <strong>Tanjug</strong> lepo izvestavati o injekciji. :D</p>');

        $restored = (new DiacriticRestorer)($text);
        $cyrillic = (new ToCyrillic)($restored);

        $this->assertSame('<p>Сто пута сам вам рекао да ће <strong>Танјуг</strong> лепо извештавати о инјекцији. :D</p>', $cyrillic->toString());
    }

    /** @test */
    public function it_can_convert_cyrillic_text_to_latin()
    {
        $text = Text::fromString('Ово би требало да буде прилично лако за Ђорђевић ЏАЛЕТА.');
        $latin = (new ToLatin)($text);

        $this->assertNotSame($text->toString(), $latin->toString());

        $this->assertSame('Ovo bi trebalo da bude prilično lako za Đorđević DŽALETA.', $latin->toString());
    }

    /** @test */
    public function it_can_convert_cyrillic_text_to_cyrillic()
    {
        $text = Text::fromString('<p>Ова акција неће имати ефекта! http://test.com</p>');
        $cyrillic = (new ToCyrillic)($text);

        $this->assertSame('<p>Ова акција неће имати ефекта! http://test.com</p>', $cyrillic->toString());
    }

    /** @test */
    public function it_can_convert_latin_text_to_ascii_latin()
    {
        $text = Text::fromString('Slušaj sada, pričam ti o ćudljivoj životinji od koje se prave džemperi.');
        $ascii = (new ToAsciiLatin)($text);

        $this->assertNotSame($text->toString(), $ascii->toString());

        $this->assertSame('Slusaj sada, pricam ti o cudljivoj zivotinji od koje se prave dzemperi.', $ascii->toString());
    }

    /** @test */
    public function it_can_convert_cyrillic_text_to_ascii_latin()
    {
        $text = Text::fromString('Четири чавке чучећи цијучу');
        $ascii = (new ToAsciiLatin)($text);

        $this->assertSame('Cetiri cavke cuceci cijucu', $ascii->toString());
    }
}
