<?php

declare(strict_types=1);

namespace Turanjanin\SerbianLanguageTools\Tests\Transformers;

use Turanjanin\SerbianLanguageTools\Tests\TestCase;
use Turanjanin\SerbianLanguageTools\Transformers\DiacriticRestorer;
use Turanjanin\SerbianLanguageTools\Dictionary\Variant;
use Turanjanin\SerbianLanguageTools\Tests\Fakes\InMemoryDictionary;
use Turanjanin\SerbianLanguageTools\Text;
use Turanjanin\SerbianLanguageTools\Tokens\Interpunction;
use Turanjanin\SerbianLanguageTools\Tokens\MultipleRestoredWord;
use Turanjanin\SerbianLanguageTools\Tokens\RestoredWord;
use Turanjanin\SerbianLanguageTools\Tokens\Word;

class DiacriticRestorerTest extends TestCase
{
    private InMemoryDictionary $dictionary;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dictionary = new InMemoryDictionary();
    }

    private function restore(Text $text): Text
    {
        $restorer = new DiacriticRestorer($this->dictionary);

        return $restorer($text);
    }

    /** @test */
    public function it_can_restore_word_from_the_dictionary()
    {
        $this->dictionary->addAsciiVariant('cetiri', new Variant('četiri', 10));
        $this->dictionary->addAsciiVariant('curke', new Variant('ćurke', 15));

        $tokens = $this->getTokens('cetiri curke');
        $restored = $this->restore($tokens);

        $this->assertToken(RestoredWord::class, 'četiri', $restored[0]);
        $this->assertToken(RestoredWord::class, 'ćurke', $restored[2]);

        $this->assertSame(10, $restored[0]->getFrequency());
        $this->assertSame('cetiri', (string)$restored[0]->getOriginalWord());
        $this->assertSame(15, $restored[2]->getFrequency());
        $this->assertSame('curke', (string)$restored[2]->getOriginalWord());
    }

    /** @test */
    public function it_will_skip_words_that_are_not_present_in_the_dictionary()
    {
        $this->dictionary->addAsciiVariant('zuta', new Variant('žuta', 50));

        $tokens = $this->getTokens('zuta kuca');
        $restored = $this->restore($tokens);

        $this->assertToken(RestoredWord::class, 'žuta', $restored[0]);
        $this->assertToken(Word::class, 'kuca', $restored[2]);
    }

    /** @test */
    public function it_will_return_the_same_token_if_original_word_is_present_in_the_dictionary()
    {
        $this->dictionary->addAsciiVariant('staza', new Variant('staza', 50));
        $this->dictionary->addAsciiVariant('blaza', new Variant('blaza', 30));

        $tokens = $this->getTokens('Blaza staza');
        $restored = $this->restore($tokens);

        $this->assertToken(Word::class, 'Blaza', $restored[0]);
        $this->assertToken(Word::class, 'staza', $restored[2]);
    }

    /** @test */
    public function it_will_return_token_with_all_available_dictionary_variants()
    {
        $this->dictionary->addAsciiVariant('kuca', new Variant('kuca', 20));
        $this->dictionary->addAsciiVariant('kuca', new Variant('kuća', 50));
        $this->dictionary->addAsciiVariant('kuca', new Variant('kuča', 10));

        $tokens = $this->getTokens('kuca');
        $restored = $this->restore($tokens);

        $this->assertToken(MultipleRestoredWord::class, 'kuća', $restored[0]);

        $this->assertSame(50, $restored[0]->getFrequency());
        $this->assertCount(3, $restored[0]->getVariants());
    }

    /**
     * @test
     * @testWith  ["Ceska", "Češka"]
     *            ["cEsKa", "čEšKa"]
     *            ["CESKA", "ČEŠKA"]
     */
    public function it_will_return_restored_word_with_the_same_case($originalWord, $expectedWord)
    {
        $this->dictionary->addAsciiVariant('ceska', new Variant('češka', 30));

        $tokens = $this->getTokens($originalWord);
        $restored = $this->restore($tokens);

        $this->assertToken(RestoredWord::class, $expectedWord, $restored[0]);
    }

    /**
     * @test
     * @testWith  ["Djordjevic", "Đorđević"]
     *            ["DJORDJEVIC", "ĐORĐEVIĆ"]
     *            ["DJordjevic", "Đorđević"]
     *            ["DjordjEvic", "ĐorđEvić"]
     */
    public function it_will_preserve_case_when_restoring_word_with_digraph($originalWord, $expectedWord)
    {
        $this->dictionary->addAsciiVariant('djordjevic', new Variant('đorđević', 30));

        $tokens = $this->getTokens($originalWord);
        $restored = $this->restore($tokens);

        $this->assertToken(RestoredWord::class, $expectedWord, $restored[0]);
    }

    /** @test */
    public function it_will_preserve_cases_for_all_variants_in_multiple_restored_word()
    {
        $this->dictionary->addAsciiVariant('kuca', new Variant('kuca', 20));
        $this->dictionary->addAsciiVariant('kuca', new Variant('kuća', 50));
        $this->dictionary->addAsciiVariant('kuca', new Variant('kuča', 10));

        $tokens = $this->getTokens('KuCa');
        $restored = $this->restore($tokens);

        $variants = $restored[0]->getVariants();

        $this->assertToken(RestoredWord::class, 'KuĆa', $variants[0]);
        $this->assertToken(RestoredWord::class, 'KuCa', $variants[1]);
        $this->assertToken(RestoredWord::class, 'KuČa', $variants[2]);
    }

    /** @test */
    public function it_will_perform_dictionary_lookup_only_for_words_with_potential_for_restoration()
    {
        $tokens = new Text([
            new Word(mb_str_split('Zdravo')),
            new Word(mb_str_split('svete')),
            new Interpunction(mb_str_split(',')),
            new RestoredWord(mb_str_split('blista'), 5, new Word(mb_str_split('blista'))),
            new Word(mb_str_split('ova')),
            new Word(mb_str_split('cista')),
            new Word(mb_str_split('Djurina')),
            new Word(mb_str_split('počascena')),
            new Word(mb_str_split('dzezva')),
            new RestoredWord(mb_str_split('crvena'), 5, new Word(mb_str_split('crvena'))),
        ]);

        $this->restore($tokens);

        $this->assertCount(5, $this->dictionary->searchedVariants);
        $this->assertTrue(in_array('zdravo', $this->dictionary->searchedVariants));
        $this->assertTrue(in_array('svete', $this->dictionary->searchedVariants));
        $this->assertTrue(in_array('cista', $this->dictionary->searchedVariants));
        $this->assertTrue(in_array('djurina', $this->dictionary->searchedVariants));
        $this->assertTrue(in_array('dzezva', $this->dictionary->searchedVariants));
    }

    /** @test */
    public function it_will_restore_diacritics_based_on_the_recognized_phrase()
    {
        $this->dictionary->addPhrase('znaci pažnje');

        $tokens = $this->getTokens('mali Znaci PAZNJE puno znace');
        $restored = $this->restore($tokens);

        $this->assertToken(Word::class, 'Znaci', $restored[2]);
        $this->assertToken(RestoredWord::class, 'PAŽNJE', $restored[4]);

        $this->assertSame('mali Znaci PAŽNJE puno znace', $restored->toString());
    }

    /** @test */
    public function it_will_replace_tokens_only_for_restored_words_inside_recognized_phrase()
    {
        $this->dictionary->addPhrase('treba reći da');

        $tokens = $this->getTokens('posebno treba reci da je to');
        $restored = $this->restore($tokens);

        $this->assertToken(Word::class, 'treba', $restored[2]);
        $this->assertToken(RestoredWord::class, 'reći', $restored[4]);
        $this->assertToken(Word::class, 'da', $restored[6]);

        $this->assertSame('posebno treba reći da je to', $restored->toString());
    }

    /** @test */
    public function it_will_detect_and_replace_the_longest_possible_phrase()
    {
        $this->dictionary->addPhrase('reci da');
        $this->dictionary->addPhrase('treba reci da');
        $this->dictionary->addPhrase('posebno treba reci da');
        $this->dictionary->addPhrase('posebno treba reći da je'); // The only phrase with diacritics
        $this->dictionary->addPhrase('posebno treba reci da sada je');


        $tokens = $this->getTokens('sada posebno treba reci da je to tako');
        $restored = $this->restore($tokens);

        $this->assertSame('sada posebno treba reći da je to tako', $restored->toString());
    }

    /**
     * @test
     * @testWith  ["kaze nas izvor da je to pouzdano", "kaže naš izvor da je to pouzdano"]
     *            ["usput kaze nas izvor tu informaciju", "usput kaže naš izvor tu informaciju"]
     *            ["i tako kaze nas izvor", "i tako kaže naš izvor"]
     */
    public function it_will_detect_phrase_regardless_of_the_position_in_the_text($originalText, $expectedText)
    {
        $this->dictionary->addPhrase('kaže naš izvor');

        $tokens = $this->getTokens($originalText);
        $restored = $this->restore($tokens);

        $this->assertSame($expectedText, $restored->toString());
    }

    /** @test */
    public function it_will_detect_phrase_only_if_words_are_part_of_a_single_sentence()
    {
        $this->dictionary->addPhrase('je naš najbolji');

        $tokens = $this->getTokens('On je nas. Najbolji teniser na celom svetu je Nole.');
        $restored = $this->restore($tokens);

        $this->assertSame('On je nas. Najbolji teniser na celom svetu je Nole.', $restored->toString());
    }

    /** @test */
    public function it_will_mark_variants_from_phrases_as_preferred_regardless_of_frequency()
    {
        $this->dictionary->addAsciiVariant('sto', new Variant('sto', 50));
        $this->dictionary->addAsciiVariant('sto', new Variant('što', 150));
        $this->dictionary->addPhrase('sto hiljada');

        $tokens = $this->getTokens('sto hiljada dinara');
        $restored = $this->restore($tokens);

        $this->assertToken(MultipleRestoredWord::class, 'sto', $restored[0]);
        $this->assertToken(Word::class, 'hiljada', $restored[2]);

        $this->assertCount(2, $restored[0]->getVariants());

        $this->assertSame('sto hiljada dinara', $restored->toString());
    }
}
