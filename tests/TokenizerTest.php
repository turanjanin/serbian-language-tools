<?php

declare(strict_types=1);

namespace Turanjanin\SerbianLanguageTools\Tests;

use Turanjanin\SerbianLanguageTools\Tests\TestCase;
use Turanjanin\SerbianLanguageTools\Tokens\Emoticon;
use Turanjanin\SerbianLanguageTools\Tokens\Html;
use Turanjanin\SerbianLanguageTools\Tokens\Interpunction;
use Turanjanin\SerbianLanguageTools\Tokens\Uri;
use Turanjanin\SerbianLanguageTools\Tokens\Whitespace;
use Turanjanin\SerbianLanguageTools\Tokens\Word;

class TokenizerTest extends TestCase
{
    /** @test */
    public function it_can_split_a_sentence()
    {
        $tokens = $this->getTokens('This is a simple sentence');

        $this->assertCount(9, $tokens);

        $wordsCount = 0;
        $whitespacesCount = 0;
        foreach ($tokens as $token) {
            if ($token instanceof Word) {
                $wordsCount++;
            }

            if ($token instanceof Whitespace) {
                $whitespacesCount++;
            }
        }

        $this->assertToken(Word::class, 'This', $tokens[0]);
        $this->assertToken(Whitespace::class, ' ', $tokens[1]);
    }

    /** @test */
    public function multiple_whitespace_characters_will_be_grouped_into_a_single_token()
    {
        $text = "Hello  My \t\r\n World";
        $tokens = $this->getTokens($text);

        $this->assertCount(5, $tokens);

        $this->assertToken(Whitespace::class, '  ', $tokens[1]);
        $this->assertToken(Whitespace::class, " \t\r\n ", $tokens[3]);
    }

    /** @test */
    public function interpunction_will_be_split_into_separate_token()
    {
        $text = 'Hello, "world" hey.';
        $tokens = $this->getTokens($text);

        $this->assertCount(9, $tokens);

        $this->assertToken(Interpunction::class, ',', $tokens[1]);
        $this->assertToken(Interpunction::class, '"', $tokens[3]);
        $this->assertToken(Interpunction::class, '.', $tokens[8]);
    }

    /** @test */
    public function words_with_dash_will_be_parsed_as_a_single_token()
    {
        $text = '-Hey, this is the best-in-class tokenizer';
        $tokens = $this->getTokens($text);

        $this->assertCount(13, $tokens);

        $this->assertToken(Interpunction::class, '-', $tokens[0]);
        $this->assertToken(Word::class, 'best-in-class', $tokens[10]);
    }

    /** @test */
    public function words_with_single_quotation_mark_will_be_parsed_as_a_single_token()
    {
        $text = "It's me, 'Mario'";
        $tokens = $this->getTokens($text);

        $this->assertToken(Word::class, "It's", $tokens[0]);
        $this->assertToken(Interpunction::class, "'", $tokens[5]);
    }

    /** @test */
    public function consecutive_interpunction_signs_will_be_parsed_as_a_single_token()
    {
        $tokens = $this->getTokens('Hey... how are you?!?');

        $this->assertToken(Interpunction::class, '...', $tokens[1]);
        $this->assertToken(Interpunction::class, '?!?', $tokens[8]);
    }

    /** @test */
    public function hashtags_will_be_parsed_as_a_single_token()
    {
        $tokens = $this->getTokens('#happiness is #green_unit, functional tests');

        $this->assertCount(10, $tokens);
        $this->assertToken(Uri::class, '#happiness', $tokens[0]);
        $this->assertToken(Uri::class, '#green_unit', $tokens[4]);
    }

    /** @test */
    public function at_mentions_will_be_parsed_as_a_single_token()
    {
        $tokens = $this->getTokens('This is for @serbian_php community');

        $this->assertToken(Uri::class, '@serbian_php', $tokens[6]);
    }

    /**
     * @test
     * @testWith  ["serbian@language.com"]
     *            ["test@example.ninja"]
     *            ["hello+123@m.second.third.45s.dev"]
     *
     *            ["988@ћирилица.срб"] // TODO: cover this
     */
    public function emails_will_be_parsed_as_a_single_token($email)
    {
        $tokens = $this->getTokens($email);

        $this->assertCount(1, $tokens);
        $this->assertToken(Uri::class, $email, $tokens[0]);
    }

    /**
     * @test
     * @testWith  ["http://example.com"]
     *            ["https://www.something.rs/this-is-my?example=of&testing;hey=cool"]
     */
    public function urls_will_be_parsed_as_a_single_token($url)
    {
        $tokens = $this->getTokens($url);

        $this->assertCount(1, $tokens);
        $this->assertToken(Uri::class, $url, $tokens[0]);
    }

    /** @test */
    public function trailing_interpunction_wont_be_included_in_uri()
    {
        $tokens = $this->getTokens('Hello test@example.com, your login is: https://click.me/test?hey.');

        $this->assertToken(Uri::class, 'test@example.com', $tokens[2]);
        $this->assertToken(Interpunction::class, ',', $tokens[3]);
        $this->assertToken(Word::class, 'your', $tokens[5]);
        $this->assertToken(Whitespace::class, ' ', $tokens[11]);
        $this->assertToken(Uri::class, 'https://click.me/test?hey', $tokens[12]);
    }

    /** @test */
    public function incorrect_interpunction_will_be_properly_recognized()
    {
        $tokens = $this->getTokens('hey,Mike,can you visit???I need to ask');

        $this->assertCount(17, $tokens);
        $this->assertToken(Word::class, 'hey', $tokens[0]);
        $this->assertToken(Interpunction::class, ',', $tokens[1]);
        $this->assertToken(Word::class, 'Mike', $tokens[2]);
        $this->assertToken(Word::class, 'visit', $tokens[8]);
        $this->assertToken(Interpunction::class, '???', $tokens[9]);
    }

    /** @test */
    public function it_can_tokenize_html_tags()
    {
        $tokens = $this->getTokens('<p>Hey, <b><span>username</span>,</b> this is <strong>important</strong>.</p> <hr />');

        $this->assertToken(Html::class, '<p>', $tokens[0]);
        $this->assertToken(Html::class, '</span>', $tokens[7]);
        $this->assertToken(Html::class, '<hr />', $tokens[21]);
    }

    /** @test */
    public function it_can_tokenize_html_tags_with_attributes()
    {
        $tokens = $this->getTokens('This <table width=10> is <span style="color: rgb(77, 77, 77); padding:10"> <p:oem> <I>AM</I> <p class="test"></p>');

        $this->assertToken(Html::class, '<table width=10>', $tokens[2]);
        $this->assertToken(Html::class, '<span style="color: rgb(77, 77, 77); padding:10">', $tokens[6]);
        $this->assertToken(Html::class, '<p:oem>', $tokens[8]);
        $this->assertToken(Html::class, '</I>', $tokens[12]);
        $this->assertToken(Html::class, '</I>', $tokens[12]);
        $this->assertToken(Html::class, '<p class="test">', $tokens[14]);
    }

    /** @test */
    public function it_wont_recognize_lt_and_gt_symbols_as_html()
    {
        $tokens = $this->getTokens('It is <5 degrees and me > you </3');

        $this->assertToken(Interpunction::class, '<', $tokens[4]);
        $this->assertToken(Word::class, '5', $tokens[5]);
        $this->assertToken(Interpunction::class, '>', $tokens[13]);
    }

    /**
     * @test
     * @dataProvider emoticonDataProvider
     */
    public function it_can_tokenize_emoticons($emoticon)
    {
        $tokens = $this->getTokens($emoticon);

        $this->assertCount(1, $tokens);
        $this->assertToken(Emoticon::class, $emoticon, $tokens[0]);
    }

    /** @test */
    public function it_can_tokenize_emoticons_inside_text()
    {
        $tokens = $this->getTokens('Hey :), how are you :D? Are you doing ok?;)');

        $this->assertToken(Emoticon::class, ':)', $tokens[2]);
        $this->assertToken(Emoticon::class, ':D', $tokens[11]);

        // TODO: cover this scenario.
        //$this->assertToken(Emoticon::class, ';)', $tokens[22]);
    }

    public function emoticonDataProvider()
    {
        return [
            [':)'],
            [':D'],
            [':P'],
            [':p'],
            [':-P'],
            [':-p'],
            [':-('],
            [':-*'],
            ['<3'],
            ['</3'],
            ['<\3'],
            [':('],
        ];
    }

    /** @test */
    public function it_will_recognize_html_entities_as_html_token()
    {
        $tokens = $this->getTokens('You&nbsp;have 100&euro; & 20 &#163;.');

        $this->assertToken(Html::class, '&nbsp;', $tokens[1]);
        $this->assertToken(Html::class, '&euro;', $tokens[5]);
        $this->assertToken(Interpunction::class, '&', $tokens[7]);
        $this->assertToken(Html::class, '&#163;', $tokens[11]);
    }
}
