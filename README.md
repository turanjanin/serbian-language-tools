# Serbian Language Tools - PHP library for Transliteration & Diacritic Restoration

Serbian Language Tools is a PHP library for dealing with text written in Serbian language. It features:
- Tokenizer
- **Diacritic restoration tool**
- Transliterator between Serbian Cyrillic and Latin alphabets
- Alphabet detection

## Requirements

This library requires PHP 7.4 or greater with [sqlite3](https://www.php.net/manual/en/book.sqlite3.php), [intl](https://www.php.net/manual/en/book.intl.php) and [mbstring](https://www.php.net/manual/en/book.mbstring.php) extensions.


## Installation

You can install the package via composer:

```bash
composer require turanjanin/serbian-language-tools
```

## Usage

In order to use the library, you need to tokenize the string. Tokenization is a process of splitting the string into a series of related characters. This library can recognize the following tokens: Word, Whitespace, URI (which includes URLs, hashtags and at-mentions), Interpunction, HTML and Emoticon.

Tokenizing can be achieved by creating a new instance of `Text` class using the named constructor:

```php
use Turanjanin\SerbianLanguageTools\Text;

$text = Text::fromString('Zdravo svete, ovo je primer teksta!');
```

Text object will now contain an array of various tokens that can be processed. You can use this object as any other PHP array since it implements `ArrayAccess` interface.

```php
echo count($text) . "\n"; // 13
echo get_class($text[1]). "\n"; // Turanjanin\SerbianLanguageTools\Tokens\Whitespace
echo $text[9] . "\n"; // primer
```


### Diacritic Restoration / Diacritization

Serbian Latin alphabet includes a couple of specific characters that are not found in ASCII encoding table. These characters feature diacritics - č, ć, š, ž, dž, đ - which are often omitted in everyday communication (social media, emails and SMS), mainly due to the widespread usage of English keyboard layouts.

This degraded Latin alphabet can be easily understood by human readers but it poses significant challenge for search engines and natural language processing. Therefore, this library features an algorithm that allows automated restoration of ASCII text by using a [dictionary of Serbian words](dictionary/README.md) and phrases for context disambiguation.

The algorithm inspects all `Word` tokens and looks for restoration candidates - the words with s, c, z or dj characters. After that, the following two steps are applied:

1. The most common phrases are searched for inside the text and, if found, words are replaced with their diacritical equivalents. This step takes word context into consideration which allows us to give advantage to some less used variations. For example, `sto hiljada` won't be replaced with `što hiljada`, even though the form `što` *(why)* has much greater frequency compared to word `sto` *(hundred)*.

2. Every restoration candidate is looked up in the dictionary and, if there are known variations, token is replaced with `RestoredWord` (if there is only one possible variation) or `MultipleRestoredWord` (if there are more possible variations). In case of more than one variation, the one with the highest frequency will be marked as preferred.

Diacritic restoration can be performed by calling the invokable class:

```php
use Turanjanin\SerbianLanguageTools\Text;
use Turanjanin\SerbianLanguageTools\Transformers\DiacriticRestorer;

$text = Text::fromString('Cetiri cavke cuceci dzangrizavo cijucu u zeleznickoj skoli.');
echo (new DiacriticRestorer)($text); // Četiri čavke čučeći džangrizavo cijuču u železničkoj školi.
```

Dictionary needed for this algorithm is stored in custom-made SQLite database that is included with this library. You can extend this database or use different storage solution by providing custom implementation of `Turanjanin\SerbianLanguageTools\Dictionary\Dictionary` interface.


### Transliteration

Library supports transliteration of text between Cyrillic, Latin and ASCII alphabets. Transliteration can be performed by calling appropriate invokable class:

```php
use Turanjanin\SerbianLanguageTools\Text;
use Turanjanin\SerbianLanguageTools\Transformers\ToAsciiLatin;
use Turanjanin\SerbianLanguageTools\Transformers\ToCyrillic;
use Turanjanin\SerbianLanguageTools\Transformers\ToLatin;

$cyrillic = Text::fromString('Ово је ћирилични текст');
$latin = Text::fromString('Primer latiničnog teksta');

echo (new ToLatin)($cyrillic); // Ovo je ćirilični tekst

echo (new ToCyrillic)($latin); // Пример латиничног текста

echo (new ToAsciiLatin)($cyrillic); // Ovo je cirilicni tekst
```

If you need only transliteration between Latin and Cyrillic alphabets, take a look at the simpler library - [turanjanin/serbian-transliterator](https://github.com/turanjanin/serbian-transliterator).


### Alphabet Detection

Library can be used to detect if text is written in Serbian Cyrillic or Latin alphabet:

```php
use Turanjanin\SerbianLanguageTools\Text;

Text::fromString('Ovo je latinica')->isLatin(); // true
Text::fromString('Ovo je latinica')->isCyrillic(); // false
```


## Author

- [Jovan Turanjanin](https://github.com/turanjanin)


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
