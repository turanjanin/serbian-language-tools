<?php

declare(strict_types=1);

namespace Turanjanin\SerbianLanguageTools;

use Turanjanin\SerbianLanguageTools\Tokens\Word;

class IsSerbianCyrillic
{
    public function __invoke(Text $text): bool
    {
        $cyrillicWords = 0;
        $totalWords = 0;

        foreach ($text as $token) {
            if (!($token instanceof Word)) {
                continue;
            }

            $totalWords++;

            if ($token->containsOnlySerbianCyrillic()) {
                $cyrillicWords++;
            }
        }

        if ($totalWords === 0) {
            return false;
        }

        return $cyrillicWords >= ($totalWords / 2);
    }
}
