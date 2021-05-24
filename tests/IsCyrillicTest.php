<?php

declare(strict_types=1);

namespace Turanjanin\SerbianLanguageTools\Tests;

use Turanjanin\SerbianLanguageTools\IsSerbianCyrillic;

class IsCyrillicTest extends TestCase
{
    /** @test */
    public function it_will_return_true_if_text_contains_only_serbian_cyrillic_words()
    {
        $tokens = $this->getTokens('Брза вижљаста лија хоће да ђипи преко лењог флегматичног џукца');

        $this->assertTrue((new IsSerbianCyrillic)($tokens));
    }

    /** @test */
    public function it_will_return_true_if_there_is_at_least_half_of_cyrillic_words()
    {
        $tokens = $this->getTokens('prva друга treća четврта peta шеста sedma осма');

        $this->assertTrue((new IsSerbianCyrillic)($tokens));
    }

    /** @test */
    public function it_will_count_only_word_tokens_when_detecting_alphabet()
    {
        $tokens = $this->getTokens('<p> <b> <i> Здраво svete сада! </i> </b> </p>');

        $this->assertCount(18, $tokens);
        $this->assertTrue((new IsSerbianCyrillic)($tokens));
    }

    /** @test */
    public function it_will_return_false_if_text_contains_only_latin_words()
    {
        $tokens = $this->getTokens('Ljubičasti jež iz fioke hoće da pecne rđavog miša džonjala');

        $this->assertFalse((new IsSerbianCyrillic)($tokens));
    }

    /** @test */
    public function it_will_return_false_if_text_contains_more_than_half_of_non_cyrillic_words()
    {
        $tokens = $this->getTokens('prva друга treća четврта peta шеста sedma осма deveta deseta');

        $this->assertFalse((new IsSerbianCyrillic)($tokens));
    }

    /** @test */
    public function it_will_return_false_if_text_doesnt_contain_words()
    {
        $tokens = $this->getTokens('');

        $this->assertFalse((new IsSerbianCyrillic)($tokens));
    }
}
