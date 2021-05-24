<?php

declare(strict_types=1);

namespace Turanjanin\SerbianLanguageTools\Tests\Transformers;

use Turanjanin\SerbianLanguageTools\Tests\TestCase;
use Turanjanin\SerbianLanguageTools\Transformers\ToLatin;

class ToLatinTest extends TestCase
{
    /** @test */
    public function it_can_transliterate_cyrillic_to_latin()
    {
        $lowercaseCyrillic = 'брза вижљаста лија хоће да ђипи преко лењог флегматичног џукца.';
        $lowercaseLatin = 'brza vižljasta lija hoće da đipi preko lenjog flegmatičnog džukca.';

        $latinText = (new ToLatin)($this->getTokens($lowercaseCyrillic));
        $this->assertSame($lowercaseLatin, $latinText->toString());

        $uppercaseCyrillic = 'ЉУДИ, ЈАЗАВАЦ ЏЕФ ТРЧИ ПО ШУМИ ГЛОЂУЋИ НЕКО СУХО ЖБУЊЕ.';
        $uppercaseLatin = 'LJUDI, JAZAVAC DŽEF TRČI PO ŠUMI GLOĐUĆI NEKO SUHO ŽBUNJE.';

        $latinText = (new ToLatin)($this->getTokens($uppercaseCyrillic));
        $this->assertSame($uppercaseLatin, $latinText->toString());
    }

    /** @test */
    public function it_can_transliterate_latin_to_latin()
    {
        $latin = 'Ljubičasti jež iz fioke hoće da pecne rđavog miša džonjala.';

        $latinText = (new ToLatin)($this->getTokens($latin));
        $this->assertSame($latin, $latinText->toString());
    }

    /** @test */
    public function it_will_properly_transliterate_case_of_latin_digraphs()
    {
        $latinText = (new ToLatin)($this->getTokens('Љубичаста ЉОВИСНА је љанкасе'));
        $this->assertSame('Ljubičasta LJOVISNA je ljankase', $latinText->toString());

        $latinText = (new ToLatin)($this->getTokens('Њише се њопајуће ЊАЊАВО'));
        $this->assertSame('Njiše se njopajuće NJANJAVO', $latinText->toString());

        $latinText = (new ToLatin)($this->getTokens('Џангризави ЏУДИСТА џемпер оџаком Џоди даје.'));
        $this->assertSame('Džangrizavi DŽUDISTA džemper odžakom Džodi daje.', $latinText->toString());
    }
}
