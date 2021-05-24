<?php

declare(strict_types=1);

namespace Turanjanin\SerbianLanguageTools\Tests;

use Turanjanin\SerbianLanguageTools\Text;
use Turanjanin\SerbianLanguageTools\Tokenizer;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function getTokens(string $text): Text
    {
        return (new Tokenizer)($text);
    }

    public function assertToken($expectedClass, $expectedContent, $actualToken): void
    {
        $this->assertSame($expectedClass, get_class($actualToken), get_class($actualToken) . " doesn't match expected {$expectedClass}");
        $this->assertSame($expectedContent, $actualToken->__toString());
    }

    protected function dumpTokens(Text $tokens): void
    {
        $i = 0;
        foreach ($tokens as $token) {
            echo $i++ . "\t" . str_pad(explode('\\', get_class($token))[3], 15) . "\t'" . ((string)$token) . "'\n";
        }
    }
}
