<?php

declare(strict_types=1);

namespace Turanjanin\SerbianLanguageTools\Tests\Tokens;

use Turanjanin\SerbianLanguageTools\Tests\TestCase;
use Turanjanin\SerbianLanguageTools\Tokens\Word;

class WordTest extends TestCase
{
    /** @test */
    public function it_can_return_if_word_contains_only_serbian_latin_characters()
    {
        $this->assertTrue(Word::fromString('abcdefghijklmnoprstuvz')->containsOnlySerbianLatin());
        $this->assertTrue(Word::fromString('ABCDEFGHIJKLMNOPRSTUVZ')->containsOnlySerbianLatin());

        $this->assertTrue(Word::fromString('čćžđš')->containsOnlySerbianLatin());
        $this->assertTrue(Word::fromString('ČĆŽĐŠ')->containsOnlySerbianLatin());

        $this->assertTrue(Word::fromString('abcd-efgh')->containsOnlySerbianLatin());
        $this->assertTrue(Word::fromString('tako-reći')->containsOnlySerbianLatin());

        $this->assertTrue(Word::fromString('najčešće')->containsOnlySerbianLatin());
        $this->assertTrue(Word::fromString('NAJČEŠĆE')->containsOnlySerbianLatin());

        $this->assertTrue((new Word(['i', 'n', '!', 'j', 'e', 'k', 'c', 'i', 'j', 'a']))->containsOnlySerbianLatin());

        $this->assertFalse(Word::fromString('ћирилично')->containsOnlySerbianLatin());
        $this->assertFalse(Word::fromString('wxyq')->containsOnlySerbianLatin());
        $this->assertFalse(Word::fromString('awćž')->containsOnlySerbianLatin());
        $this->assertFalse(Word::fromString('äjde')->containsOnlySerbianLatin());
        $this->assertFalse(Word::fromString('predšqlsko')->containsOnlySerbianLatin());
    }

    /** @test */
    public function it_can_return_if_word_contains_only_serbian_cyrillic_characters()
    {
        $this->assertTrue(Word::fromString('абвгдђежзијклљмнњопрстћуфхцчџш')->containsOnlySerbianCyrillic());
        $this->assertTrue(Word::fromString('АБВГДЂЕЖЗИЈКЛЉМНЊОПРСТЋУФХЦЧЏШ')->containsOnlySerbianCyrillic());
        $this->assertTrue(Word::fromString('лево-десно')->containsOnlySerbianCyrillic());

        $this->assertFalse(Word::fromString('яйё')->containsOnlySerbianCyrillic());
        $this->assertFalse(Word::fromString('latinica')->containsOnlySerbianCyrillic());
        $this->assertFalse(Word::fromString('Čeličićeš')->containsOnlySerbianCyrillic());
        $this->assertFalse(Word::fromString('wxqy')->containsOnlySerbianCyrillic());
    }

    /** @test */
    public function it_can_return_if_word_contains_diacritic_characters()
    {
        $this->assertFalse(Word::fromString('abcdefghijklmnoprstuvz')->hasDiacritic());

        $this->assertTrue(Word::fromString('prećutno')->hasDiacritic());
        $this->assertTrue(Word::fromString('česma')->hasDiacritic());
        $this->assertTrue(Word::fromString('pretškolski')->hasDiacritic());
        $this->assertTrue(Word::fromString('nadživeti')->hasDiacritic());
        $this->assertTrue(Word::fromString('odžak')->hasDiacritic());

        $this->assertTrue(Word::fromString('Ćubica')->hasDiacritic());
        $this->assertTrue(Word::fromString('OČUVANO')->hasDiacritic());
        $this->assertTrue(Word::fromString('ŽIVOT')->hasDiacritic());
        $this->assertTrue(Word::fromString('ŠPATULA')->hasDiacritic());
    }

    /** @test */
    public function it_can_return_if_word_contains_dj_digraph()
    {
        $this->assertTrue(Word::fromString('Djokovic')->hasDj());
        $this->assertTrue(Word::fromString('DJOKOVIC')->hasDj());
        $this->assertTrue(Word::fromString('djordje')->hasDj());

        $this->assertFalse(Word::fromString('Marko')->hasDj());
        $this->assertFalse(Word::fromString('Đole')->hasDj());
    }

    /** @test */
    public function it_can_return_if_word_has_potential_digraph()
    {
        $this->assertTrue(Word::fromString('pronadjen')->hasPotentialDigraph());
        $this->assertTrue(Word::fromString('Djavo')->hasPotentialDigraph());
        $this->assertTrue(Word::fromString('injekcija')->hasPotentialDigraph());
        $this->assertTrue(Word::fromString('ODZACAR')->hasPotentialDigraph());
        $this->assertTrue(Word::fromString('Džeronimo')->hasPotentialDigraph());

        $this->assertFalse(Word::fromString('beznađe')->hasPotentialDigraph());
        $this->assertFalse(Word::fromString('vreme')->hasPotentialDigraph());
    }

    /** @test */
    public function it_can_return_if_word_is_restoration_candidate()
    {
        // ASCII word
        $this->assertFalse(Word::fromString('patika')->isRestorationCandidate());

        // Word with diacritics
        $this->assertFalse(Word::fromString('čizma')->isRestorationCandidate());
        $this->assertFalse(Word::fromString('praćka')->isRestorationCandidate());
        $this->assertFalse(Word::fromString('žuto')->isRestorationCandidate());
        $this->assertFalse(Word::fromString('opraštanje')->isRestorationCandidate());

        // Word with diacritics containing digraph
        $this->assertFalse(Word::fromString('njuška')->isRestorationCandidate());

        // Words with stripped diacritics
        $this->assertTrue(Word::fromString('prilicno')->isRestorationCandidate());
        $this->assertTrue(Word::fromString('pasteta')->isRestorationCandidate());
        $this->assertTrue(Word::fromString('dzak')->isRestorationCandidate());
        $this->assertTrue(Word::fromString('zeleno')->isRestorationCandidate());

        // Words with digraphs
        $this->assertTrue(Word::fromString('injekcija')->isRestorationCandidate());
        $this->assertTrue(Word::fromString('nadziveti')->isRestorationCandidate());
        $this->assertTrue(Word::fromString('Ljubica')->isRestorationCandidate());
        $this->assertTrue(Word::fromString('DJOKOVIC')->isRestorationCandidate());
    }
}
