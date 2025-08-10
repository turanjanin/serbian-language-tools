<?php

declare(strict_types=1);

namespace Turanjanin\SerbianLanguageTools\Transformers;

use Turanjanin\SerbianLanguageTools\Dictionary\Dictionary;
use Turanjanin\SerbianLanguageTools\Dictionary\SqliteDictionary;
use Turanjanin\SerbianLanguageTools\Text;
use Turanjanin\SerbianLanguageTools\Tokens\MultipleRestoredWord;
use Turanjanin\SerbianLanguageTools\Tokens\RestoredWord;
use Turanjanin\SerbianLanguageTools\Tokens\Whitespace;
use Turanjanin\SerbianLanguageTools\Tokens\Word;

class DiacriticRestorer
{
    protected Dictionary $dictionary;

    public function __construct(?Dictionary $dictionary = null)
    {
        if ($dictionary === null) {
            $dictionary = new SqliteDictionary();
        }

        $this->dictionary = $dictionary;
    }

    public function __invoke(Text $text): Text
    {
        $newText = $this->processPhrases($text);
        $newText = $this->processIndividualWords($newText);

        return $newText;
    }

    /**
     * This is the first step in our algorithm in order to give advantage to some word variants
     * based on the context in the sentence. It will search for specific phrases from the dictionary
     * and it will restore diacritics based on the found phrase.
     *
     * It uses trie data structure in order to find the longest possible phrase.
     */
    protected function processPhrases(Text $text): Text
    {
        $newText = new Text();
        $phrases = $this->dictionary->getPhrases();
        $trie = $this->getTrie($phrases);

        for ($i = 0, $length = count($text); $i < $length; $i++) {
            if (!($text[$i] instanceof Word)) {
                $newText[$i] = $text[$i];
                continue;
            }

            $currentWord = mb_strtolower($text[$i]->__toString());
            if (!isset($trie[$currentWord])) {
                $newText[$i] = $text[$i];
                continue;
            }

            $currentNode = $trie[$currentWord];
            $currentTrieDepth = 0;
            $matchedPhrase = '';

            // Search trie for the longest possible phrase.
            while (true) {
                if (isset($currentNode['value'])) {
                    $matchedPhrase = $currentNode['value'];
                }

                $tokenOffset = $currentTrieDepth * 2;

                // Find two consecutive words separated only by whitespace.
                $currentNodePlus1 = $text[$i + $tokenOffset + 1] ?? null;
                if (!($currentNodePlus1 instanceof Whitespace)) {
                    break;
                }

                $currentNodePlus2 = $text[$i + $tokenOffset + 2] ?? null;
                if (!($currentNodePlus2 instanceof Word)) {
                    break;
                }

                $nextWord = mb_strtolower($currentNodePlus2->__toString());
                if (!isset($currentNode[$nextWord])) {
                    break;
                }

                $currentTrieDepth++;
                $currentNode = $currentNode[$nextWord];
            }

            if ($matchedPhrase === '') {
                $newText[$i] = $text[$i];
                continue;
            }

            $words = explode(' ', $matchedPhrase);

            for ($j = 0, $wordCount = count($words); $j < $wordCount; $j++) {
                if ($text[$i]->isRestorationCandidate()) {
                    $frequency = 100000; // We are really sure that this is the most appropriate variant.
                    $preferredVariant = RestoredWord::fromStringWithMatchingCase($words[$j], $frequency, $text[$i]);
                    $newText[$i] = $this->restoreDiacritics($text[$i], $preferredVariant);
                } else {
                    $newText[$i] = $text[$i];
                }

                if ($j + 1 < $wordCount) {
                    // Add whitespace between phrase words.
                    $i++;
                    $newText[$i] = $text[$i];
                    $i++;
                }
            }
        }

        return $newText;
    }

    private function getTrie(array $phrases): array
    {
        $trie = [];
        foreach ($phrases as $phrase) {
            $words = explode(' ', $phrase);

            $trie = $this->mergeArray($trie, $this->buildTrie($words, $phrase));
        }

        return $trie;
    }

    private function mergeArray(array $array1, array $array2): array
    {
        foreach ($array2 as $key => $value) {
            if (!isset($array1[$key])) {
                $array1[$key] = $value;
                continue;
            }

            if (is_array($value)) {
                $array1[$key] = $this->mergeArray($array1[$key], $value);
                continue;
            }

            $array1[$key] = $value;
        }

        return $array1;
    }

    private function buildTrie(array $words, string $term): array
    {
        if (empty($words)) {
            return ['value' => $term];
        }

        $latinToAscii = [
            'š' => 's',
            'č' => 'c',
            'ć' => 'c',
            'đ' => 'dj',
            'ž' => 'z',
        ];

        $nextWord = mb_strtolower($words[0]);
        $key = strtr($nextWord, $latinToAscii);
        $words = array_slice($words, 1);

        return [
            $key => $this->buildTrie($words, $term)
        ];
    }

    /**
     * This is the second step in diacritic restoration process. This method will consider only non-restored words
     * which are restoration candidates (the words with c, z, s or dj characters).
     * All such candidates are looked-up in the dictionary and replaced with all potential candidates,
     * sorted by frequency of their occurrence in Serbian language.
     */
    protected function processIndividualWords(Text $text): Text
    {
        $newText = new Text();

        foreach ($text as $token) {
            $tokenClass = get_class($token);
            if ($tokenClass !== Word::class) {
                $newText[] = $token;
                continue;
            }

            if (!$token->isRestorationCandidate()) {
                $newText[] = $token;
                continue;
            }

            $newText[] = $this->restoreDiacritics($token);

            $isStartOfSentence = false;
        }

        return $newText;
    }

    protected function restoreDiacritics(Word $word, ?RestoredWord $preferredVariant = null): Word
    {
        $variants = $this->getVariants($word, $preferredVariant);
        if (empty($variants)) {
            return $word;
        }

        if (count($variants) === 1) {
            if ($variants[0]->getCharacters() === $word->getCharacters()) {
                return $word;
            }

            return $variants[0];
        }

        return new MultipleRestoredWord($variants);
    }

    /** @return \Turanjanin\SerbianLanguageTools\Tokens\RestoredWord[] */
    protected function getVariants(Word $token, ?RestoredWord $preferredVariant = null): array
    {
        $lowercaseString = strtolower($token->__toString());

        $dictionaryVariants = $this->dictionary->getAsciiVariants($lowercaseString);

        // Sort dictionary variants by frequency, in descending order.
        usort($dictionaryVariants, function ($a, $b) {
            if ($a->frequency === $b->frequency) {
                return 0;
            }

            return $a->frequency > $b->frequency ? -1 : 1;
        });


        $variants = [];
        foreach ($dictionaryVariants as $dictionaryVariant) {
            $restoredWord = RestoredWord::fromStringWithMatchingCase($dictionaryVariant->word, $dictionaryVariant->frequency, $token);
            $variants[] = $restoredWord;
        }

        if ($preferredVariant) {
            array_unshift($variants, $preferredVariant);
        }

        return array_unique($variants);
    }
}
