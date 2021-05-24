<?php

declare(strict_types=1);

namespace Turanjanin\SerbianLanguageTools\Transformers;

use Turanjanin\SerbianLanguageTools\Text;
use Turanjanin\SerbianLanguageTools\Tokens\MultipleRestoredWord;
use Turanjanin\SerbianLanguageTools\Tokens\RestoredWord;
use Turanjanin\SerbianLanguageTools\Tokens\Word;

abstract class WordTransformer
{
    private ?array $trie = null;

    abstract protected function getMap(): array;

    public function __invoke(Text $text): Text
    {
        $newText = new Text();

        foreach ($text as $token) {
            if ($token instanceof Word) {
                if ($this->shouldBeReplaced($token)) {
                    $token = $this->replaceWord($token);
                }
            }

            $newText[] = $token;
        }

        return $newText;
    }

    protected function shouldBeReplaced(Word $word): bool
    {
        return true;
    }

    protected function replaceWord(Word $word): Word
    {
        if ($word instanceof MultipleRestoredWord) {
            $newVariants = array_map([$this, 'replaceWord'], $word->getVariants());

            return new MultipleRestoredWord($newVariants);
        }

        if ($word instanceof RestoredWord) {
            return new RestoredWord(
                $this->replaceCharacters($word->getCharacters()),
                $word->getFrequency(),
                $word->getOriginalWord()
            );
        }

        return new Word(
            $this->replaceCharacters($word->getCharacters())
        );
    }

    protected function replaceCharacters(array $characters): array
    {
        $newCharacters = [];

        $trie = $this->getTrie();

        for ($i = 0, $length = count($characters); $i < $length; $i++) {
            $currentChar = $characters[$i];

            $currentNode = $trie[$currentChar] ?? [];
            $currentTrieDepth = 0;
            $matchedReplacement = '';

            while (true) {
                if (isset($currentNode['value'])) {
                    $matchedReplacement = $currentNode['value'];
                }

                $nextChar = $characters[$i + $currentTrieDepth + 1] ?? '';
                if (!isset($currentNode[$nextChar])) {
                    break;
                }

                $currentTrieDepth++;
                $currentNode = $currentNode[$nextChar];
            }

            if ($matchedReplacement === '') {
                $newCharacters[] = $currentChar;
                continue;
            }

            $newCharacters = array_merge($newCharacters, mb_str_split($matchedReplacement));
            $i += $currentTrieDepth;
        }

        return $newCharacters;
    }

    private function getTrie(): array
    {
        if ($this->trie === null) {
            $this->trie = $this->buildTrie();
        }

        return $this->trie;
    }

    private function buildTrie(): array
    {
        $trie = [];

        foreach ($this->getMap() as $key => $value) {
            $characters = mb_str_split($key);

            $currentNode = &$trie;

            foreach ($characters as $char) {
                $currentNode[$char] ??= [];
                $currentNode = &$currentNode[$char];
            }

            $currentNode['value'] = $value;
        }

        return $trie;
    }
}
