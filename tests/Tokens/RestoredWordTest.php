<?php

declare(strict_types=1);

namespace Turanjanin\SerbianLanguageTools\Tests\Tokens;

use Turanjanin\SerbianLanguageTools\Tests\TestCase;
use Turanjanin\SerbianLanguageTools\Tokens\RestoredWord;
use Turanjanin\SerbianLanguageTools\Tokens\Word;

class RestoredWordTest extends TestCase
{
    /** @test */
    public function it_can_create_new_instance_with_matching_case()
    {
        $restored = RestoredWord::fromStringWithMatchingCase('ćirilica', 7, Word::fromString('Cirilica'));
        $this->assertInstanceOf(RestoredWord::class, $restored);
        $this->assertSame(7, $restored->getFrequency());
        $this->assertSame('Ćirilica', $restored->__toString());

        $restored = RestoredWord::fromStringWithMatchingCase('đorđe', 0, Word::fromString('Djordje'));
        $this->assertSame('Đorđe', $restored->__toString());

        $restored = RestoredWord::fromStringWithMatchingCase('đorđe', 0, Word::fromString('DJORDJE'));
        $this->assertSame('ĐORĐE', $restored->__toString());

        $restored = RestoredWord::fromStringWithMatchingCase('pređašnji', 0, Word::fromString('PreDJASNJI'));
        $this->assertSame('PreĐAŠNJI', $restored->__toString());

        $restored = RestoredWord::fromStringWithMatchingCase('tan!jug', 0, Word::fromString('Tanjug'));
        $this->assertSame('Tanjug', $restored->__toString());

        $restored = RestoredWord::fromStringWithMatchingCase('TAN!JUG', 0, Word::fromString('TANJUG'));
        $this->assertSame('TANJUG', $restored->__toString());

        $restored = RestoredWord::fromStringWithMatchingCase('nad!jačati', 0, Word::fromString('NADJACATI'));
        $this->assertSame('NADJAČATI', $restored->__toString());

        $restored = RestoredWord::fromStringWithMatchingCase('nepromenjeno', 0, Word::fromString('nepromenjeno'));
        $this->assertSame('nepromenjeno', $restored->__toString());
    }
}
