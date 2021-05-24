<?php

declare(strict_types=1);

namespace Turanjanin\SerbianLanguageTools;

use IntlChar;
use Turanjanin\SerbianLanguageTools\Tokens\Emoticon;
use Turanjanin\SerbianLanguageTools\Tokens\Html;
use Turanjanin\SerbianLanguageTools\Tokens\Interpunction;
use Turanjanin\SerbianLanguageTools\Tokens\Token;
use Turanjanin\SerbianLanguageTools\Tokens\Uri;
use Turanjanin\SerbianLanguageTools\Tokens\Whitespace;
use Turanjanin\SerbianLanguageTools\Tokens\Word;

class Tokenizer
{
    private const WHITESPACE = 0;
    private const INTERPUNCTION = 1;
    private const LTGT = 2;
    private const ALPHANUM = 3;


    public function __invoke(string $input): Text
    {
        $text = new Text();

        $characters = mb_str_split($input);
        if (empty($characters)) {
            return $text;
        }

        $characterTypes = array_map([$this, 'getCharacterType'], $characters);

        $currentTokenChars = [];

        for ($i = 0, $length = count($characters); $i < $length; $i++) {
            $currentTokenChars[] = $characters[$i];

            // Search for potential emoticons.
            // This section is so high in the list to avoid confusion with multiple interpunction characters.
            if (count($currentTokenChars) == 1 && in_array($currentTokenChars[0], [':', ';', '<', '^', '=', 'B', '8'])) {
                $emoticonSafeCharacters = ['(', ')', '-', '*', 'D', 'P', 'p', 'X', 'x', '_', '^', 'o', 'O', "'", '\\', '/', '3'];
                $allowedEmoticons = [
                    ':)', ':-)', ':(', ':-(', ':-|', ':*', ':-*', ':D', ':-D', ':P', ':p', ':-P', ':-p', ':X', ':-X',
                    '^_^', '8)', '8-)', ':o', ':O', '=D', ":'(", ":'‑(", ';D', 'B-)', '<3', '</3', '<\3',
                ];

                $potentialEmoticonChars = $currentTokenChars;
                for ($j = $i + 1; $j < $length; $j++) {
                    if (!in_array($characters[$j], $emoticonSafeCharacters)) {
                        break;
                    }

                    $potentialEmoticonChars[] = $characters[$j];
                }

                $string = implode('', $potentialEmoticonChars);
                if (in_array($string, $allowedEmoticons)) {
                    $text[] = $this->buildToken($potentialEmoticonChars, Emoticon::class);
                    $i += count($potentialEmoticonChars) - count($currentTokenChars);
                    $currentTokenChars = [];
                    continue;
                }
            }

            // HTML &entities;
            if ($currentTokenChars === ['&']) {
                $htmlCharacters = $currentTokenChars;

                for ($j = $i + 1; $j < $length; $j++) {
                    if ($characterTypes[$j] === self::ALPHANUM || $characters[$j] === '#') {
                        $htmlCharacters[] = $characters[$j];
                        continue;
                    }

                    if ($characters[$j] === ';') {
                        $htmlCharacters[] = $characters[$j];
                    }

                    break;
                }

                if (count($htmlCharacters) > 1) {
                    $text[] = $this->buildToken($htmlCharacters, Html::class);
                    $i += count($htmlCharacters) - count($currentTokenChars);
                    $currentTokenChars = [];
                    continue;
                }
            }

            $nextCharacter = $characters[$i + 1] ?? null;
            if ($nextCharacter === null) {
                $text[] = $this->buildToken($currentTokenChars);
                $currentTokenChars = [];
                continue;
            }

            $currentCharacterType = $characterTypes[$i];
            $nextCharacterType = $characterTypes[$i + 1];

            if ($currentCharacterType === $nextCharacterType) {
                continue;
            }

            // Treat words with dashes and apostrophes as a single token.
            $charactersAllowedInTheMiddleOfWord = ['-', "'"];
            if ($currentCharacterType === self::ALPHANUM && in_array($nextCharacter, $charactersAllowedInTheMiddleOfWord)) {
                $characterAfterNextType = $characterTypes[$i + 2] ?? null;
                if ($characterAfterNextType === self::ALPHANUM) {
                    $currentTokenChars[] = $characters[++$i]; // Dash or a single quotation mark
                    continue;
                }
            }

            // Detect HTML tags.
            if ($currentTokenChars === ['<'] && ($nextCharacter == '/' || $nextCharacterType === self::ALPHANUM)) {
                $htmlCharacters = $currentTokenChars;

                $tagFound = false;
                for ($j = $i + 1; $j < $length; $j++) {
                    $htmlCharacters[] = $characters[$j];

                    if ($characters[$j] === '>') {
                        if ($characterTypes[$j - 1] !== self::WHITESPACE) {
                            $tagFound = true;
                        }

                        break;
                    }
                }

                if ($tagFound) {
                    $text[] = $this->buildToken($htmlCharacters, Html::class);
                    $i += count($htmlCharacters) - count($currentTokenChars);
                    $currentTokenChars = [];
                    continue;
                }
            }

            if ($currentCharacterType === self::WHITESPACE) {
                $text[] = $this->buildToken($currentTokenChars, Whitespace::class);
                $currentTokenChars = [];
                continue;
            }

            // Fetch all characters until the next whitespace character. These might belong to URL or a hashtag.
            $continuousCharacters = $currentTokenChars;
            $uriSafeCharacters = ['-', '+', '.', ',', '/', ':', '&', ';', '=', '_', '?', '#', '~', '@', '[', ']', '%'];
            for ($j = $i + 1; $j < $length; $j++) {
                if ($characterTypes[$j] !== self::ALPHANUM && !in_array($characters[$j], $uriSafeCharacters)) {
                    break;
                }

                $continuousCharacters[] = $characters[$j];
            }

            // These characters might mark end of a sentence. Let's exclude them from the rest of the crowd.
            $endCharacters = ['.', ',', ':', ';', '?', '!'];
            while (count($continuousCharacters) > 1 && in_array(end($continuousCharacters), $endCharacters)) {
                array_pop($continuousCharacters);
            }

            // @mentions and #hashtags
            if (in_array($continuousCharacters[0], ['#', '@'])) {
                $text[] = $this->buildToken($continuousCharacters, Uri::class);
                $i += count($continuousCharacters) - count($currentTokenChars);
                $currentTokenChars = [];
                continue;
            }

            $potentialUri = implode('', $continuousCharacters);

            // E-mail addresses
            if (filter_var($potentialUri, FILTER_VALIDATE_EMAIL)) {
                $text[] = $this->buildToken($continuousCharacters, Uri::class);
                $i += count($continuousCharacters) - count($currentTokenChars);
                $currentTokenChars = [];
                continue;
            }

            // Urls
            if (filter_var($potentialUri, FILTER_VALIDATE_URL)) {
                $text[] = $this->buildToken($continuousCharacters, Uri::class);
                $i += count($continuousCharacters) - count($currentTokenChars);
                $currentTokenChars = [];
                continue;
            }


            $text[] = $this->buildToken($currentTokenChars);
            $currentTokenChars = [];
        }

        return $text;
    }

    private function buildToken(array $characters, string $class = null): Token
    {
        if ($class === null) {
            $class = $this->guessTokenClass($characters);
        }

        return new $class($characters);
    }

    private function guessTokenClass(array $characters): string
    {
        $firstCharType = $this->getCharacterType($characters[0]);

        if ($firstCharType === self::WHITESPACE) {
            return Whitespace::class;
        }

        if (in_array($firstCharType, [self::INTERPUNCTION, self::LTGT])) {
            return Interpunction::class;
        }

        return Word::class;
    }

    private function getCharacterType(string $char): int
    {
        if (IntlChar::isWhitespace($char)) {
            return self::WHITESPACE;
        }

        if (IntlChar::isalnum($char)) {
            return self::ALPHANUM;
        }

        if (in_array($char, ['<', '>'])) {
            return self::LTGT;
        }

        return self::INTERPUNCTION;
    }
}
