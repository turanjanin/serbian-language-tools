<?php

declare(strict_types=1);

namespace Turanjanin\SerbianLanguageTools;

class Text implements \ArrayAccess, \Countable, \Iterator
{
    private int $position = 0;
    /** @var \Turanjanin\SerbianLanguageTools\Tokens\Token[] */
    private array $tokens;

    public function __construct(array $tokens = [])
    {
        $this->tokens = $tokens;
    }

    public function offsetExists($offset)
    {
        return isset($this->tokens[$offset]);
    }

    /** @return \Turanjanin\SerbianLanguageTools\Tokens\Token */
    public function offsetGet($offset)
    {
        if (!isset($this->tokens[$offset])) {
            return null;
        }

        return $this->tokens[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->tokens[] = $value;
        } else {
            $this->tokens[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->tokens[$offset]);
    }

    public function count()
    {
        return count($this->tokens);
    }

    /** @return \Turanjanin\SerbianLanguageTools\Tokens\Token */
    public function current()
    {
        return $this->tokens[$this->position];
    }

    public function next()
    {
        ++$this->position;
    }

    public function key()
    {
        return $this->position;
    }

    public function valid()
    {
        return isset($this->tokens[$this->position]);
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public static function fromString(string $text): self
    {
        return (new Tokenizer)($text);
    }

    public function isLatin(): bool
    {
        if ($this->count() === 0) {
            return false;
        }

        return !$this->isCyrillic();
    }

    public function isCyrillic(): bool
    {
        return (new IsSerbianCyrillic)($this);
    }

    public function toString(): string
    {
        return $this->__toString();
    }

    public function __toString()
    {
        return implode('', $this->tokens);
    }
}
