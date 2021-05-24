<?php

declare(strict_types=1);

namespace Turanjanin\SerbianLanguageTools\Tests\Transformers;

use Turanjanin\SerbianLanguageTools\Tests\Fakes\InMemoryDictionary;
use Turanjanin\SerbianLanguageTools\Tests\TestCase;
use Turanjanin\SerbianLanguageTools\Text;
use Turanjanin\SerbianLanguageTools\Transformers\ToCyrillic;
use Turanjanin\SerbianLanguageTools\Tokens\Emoticon;
use Turanjanin\SerbianLanguageTools\Tokens\Html;
use Turanjanin\SerbianLanguageTools\Tokens\MultipleRestoredWord;
use Turanjanin\SerbianLanguageTools\Tokens\RestoredWord;
use Turanjanin\SerbianLanguageTools\Tokens\Uri;
use Turanjanin\SerbianLanguageTools\Tokens\Whitespace;
use Turanjanin\SerbianLanguageTools\Tokens\Word;

class ToCyrillicTest extends TestCase
{
    private InMemoryDictionary $dictionary;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dictionary = new InMemoryDictionary();
    }

    private function toCyrillic(Text $text): Text
    {
        $toCyrillic = new ToCyrillic($this->dictionary);

        return $toCyrillic($text);
    }

    /** @test */
    public function it_can_transliterate_latin_text_to_cyrillic()
    {
        $lowercaseLatin = 'brza vižljasta lija hoće da đipi preko lenjog flegmatičnog džukca.';
        $lowercaseCyrillic = 'брза вижљаста лија хоће да ђипи преко лењог флегматичног џукца.';

        $tokens = $this->getTokens($lowercaseLatin);
        $cyrillicText = $this->toCyrillic($tokens);
        $this->assertSame($lowercaseCyrillic, $cyrillicText->toString());


        $uppercaseLatin = 'LJUDI, JAZAVAC DŽEF TRČI PO ŠUMI GLOĐUĆI NEKO SUHO ŽBUNJE.';
        $uppercaseCyrillic = 'ЉУДИ, ЈАЗАВАЦ ЏЕФ ТРЧИ ПО ШУМИ ГЛОЂУЋИ НЕКО СУХО ЖБУЊЕ.';

        $tokens = $this->getTokens($uppercaseLatin);
        $cyrillicText = $this->toCyrillic($tokens);
        $this->assertSame($uppercaseCyrillic, $cyrillicText->toString());
    }

    /** @test */
    public function it_will_correctly_handle_transliteration_of_latin_digraphs()
    {
        $this->dictionary->addDigraphException('odjednom', 'od!jednom');
        $this->dictionary->addDigraphException('tanjug', 'tan!jug');
        $this->dictionary->addDigraphException('nadživeti', 'nad!živeti');
        $this->dictionary->addDigraphException('injekciju', 'in!jekciju');

        $latin = 'Odjednom Tanjug reče da će nadživeti injekciju. Dodjavola, džangrizava njuška je bila u pravu.';
        $cyrillic = 'Одједном Танјуг рече да ће надживети инјекцију. Дођавола, џангризава њушка је била у праву.';

        $tokens = $this->getTokens($latin);
        $cyrillicText = $this->toCyrillic($tokens);
        $this->assertSame($cyrillic, $cyrillicText->toString());

        $this->assertContains('odjednom', $this->dictionary->searchedExceptions);
        $this->assertContains('tanjug', $this->dictionary->searchedExceptions);
        $this->assertContains('dodjavola', $this->dictionary->searchedExceptions);
        $this->assertNotContains('reče', $this->dictionary->searchedExceptions);
    }

    /** @test */
    public function it_can_handle_cyrillic_text()
    {
        $cyrillic = 'брза вижљаста лија хоће да ђипи преко лењог флегматичног џукца.';

        $tokens = $this->getTokens($cyrillic);
        $cyrillicText = $this->toCyrillic($tokens);
        $this->assertSame($cyrillic, $cyrillicText->toString());
    }

    /** @test */
    public function it_wont_transliterate_to_cyrillic_words_with_foreign_characters()
    {
        $latin = 'Biografiju pošaljite kao Word dokument u docx formatu za Über Yahu.';
        $cyrillic = 'Биографију пошаљите као Word документ у docx формату за Über Yahu.';

        $tokens = $this->getTokens($latin);
        $cyrillicText = $this->toCyrillic($tokens);
        $this->assertSame($cyrillic, $cyrillicText->toString());
    }

    /** @test */
    public function it_will_transliterate_only_word_tokens()
    {
        $tokens = new Text([
            Html::fromString('<div>'),
            Word::fromString('Zdravo'),
            Whitespace::fromString(' '),
            Word::fromString('svete'),
            Whitespace::fromString("\t"),
            Emoticon::fromString(':D'),
            Whitespace::fromString(' '),
            Uri::fromString('mojdomen'),
            Html::fromString('</div>'),
        ]);

        $cyrillicText = $this->toCyrillic($tokens);
        $this->assertSame('<div>Здраво свете	:D mojdomen</div>', $cyrillicText->toString());
    }

    /** @test */
    public function it_can_transliterate_restored_word()
    {
        $text = new Text([
            new RestoredWord(mb_str_split('životinja'), 45, Word::fromString('zivotinja')),
        ]);

        $cyrillicText = $this->toCyrillic($text);

        $this->assertToken(RestoredWord::class, 'животиња', $cyrillicText[0]);
        $this->assertSame(45, $cyrillicText[0]->getFrequency());
        $this->assertToken(Word::class, 'zivotinja', $cyrillicText[0]->getOriginalWord());
    }

    /** @test */
    public function it_can_transliterate_multiple_restored_word()
    {
        $originalWord = Word::fromString('kuca');

        $variants = [
            new RestoredWord(mb_str_split('kuća'), 50, $originalWord),
            new RestoredWord(mb_str_split('kuca'), 20, $originalWord),
            new RestoredWord(mb_str_split('kuča'), 10, $originalWord),
        ];

        $text = new Text([
            new MultipleRestoredWord($variants),
        ]);

        $cyrillicText = $this->toCyrillic($text);
        $this->assertToken(MultipleRestoredWord::class, 'кућа', $cyrillicText[0]);
        $this->assertSame(50, $cyrillicText[0]->getFrequency());
        $this->assertToken(Word::class, 'kuca', $cyrillicText[0]->getOriginalWord());

        $cyrillicVariants = $cyrillicText[0]->getVariants();
        $this->assertToken(RestoredWord::class, 'кућа', $cyrillicVariants[0]);
        $this->assertToken(RestoredWord::class, 'куца', $cyrillicVariants[1]);
        $this->assertToken(RestoredWord::class, 'куча', $cyrillicVariants[2]);
    }
}
