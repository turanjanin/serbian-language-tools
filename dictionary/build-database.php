<?php

declare(strict_types=1);

$wordList = __DIR__ . '/words.txt';
$phrasesList = __DIR__ . '/phrases.txt';
$dictionaryFile = __DIR__ . '/../resources/dictionary.sqlite';

$characterSeparator = '!';

function convertLatinToAsciiLatin(string $text): string
{
    global $characterSeparator;

    $replacements = [
        'Č' => 'C',
        'Ć' => 'C',
        'Đ' => 'DJ',
        'Š' => 'S',
        'Ž' => 'Z',
        'č' => 'c',
        'ć' => 'c',
        'đ' => 'dj',
        'š' => 's',
        'ž' => 'z',
        'Đa' => 'Dja',
        'Đe' => 'Dje',
        'Đi' => 'Dji',
        'Đo' => 'Djo',
        'Đu' => 'Dju',
        "{$characterSeparator}" => '',
    ];

    return strtr($text, $replacements);
}

function isAsciiVariant(string $word): bool
{
    return preg_match('/[šđčćžscz]|dj/ui', $word) !== false;
}

function isDigraphException(string $word): bool
{
    global $characterSeparator;

    return strpos($word, $characterSeparator) !== false;
}


$startTime = microtime(true);

/**
 * Database structure of our dictionary.
 */
$ddl = <<<SQL
create table ascii_variants
(
	ascii text,
	serbian_latin text,
	frequency int,
	primary key (ascii, serbian_latin)
) without rowid;

create table digraph_exceptions
(
    serbian_latin text,
    separated_digraph text,
    primary key (serbian_latin, separated_digraph)
) without rowid;

create table phrases
(
    ascii text,
	serbian_latin text
);
SQL;

if (file_exists($dictionaryFile)) {
    echo "Deleting old dictionary...\n\n";
    unlink($dictionaryFile);
}

echo "Creating dictionary, it may take a while...\n";

$sqlite = new SQLite3($dictionaryFile);
$sqlite->enableExceptions(true);
$sqlite->exec($ddl);

$sqlite->exec('PRAGMA synchronous = OFF');
$sqlite->exec('PRAGMA journal_mode = MEMORY');

$sqlite->exec('begin transaction');

$insertVariant = $sqlite->prepare('insert into ascii_variants (ascii, serbian_latin, frequency) values (:ascii, :serbian_latin, :frequency)');
$insertDigraphException = $sqlite->prepare('insert into digraph_exceptions (serbian_latin, separated_digraph) values (:serbian_latin, :separated_digraph)');

$file = fopen($wordList, 'r');
while ($row = fgets($file)) {
    if (empty(trim($row))) {
        continue;
    }

    [$word, $frequency] = explode("\t", $row);

    if (isAsciiVariant($word)) {
        $insertVariant->bindValue(':ascii', strtolower(convertLatinToAsciiLatin($word)));
        $insertVariant->bindValue(':serbian_latin', $word);
        $insertVariant->bindValue(':frequency', $frequency, SQLITE3_INTEGER);
        $insertVariant->execute();
    }

    if (isDigraphException($word)) {
        $lowercaseWord = mb_strtolower($word);

        try {
            $insertDigraphException->bindValue(':serbian_latin', str_replace($characterSeparator, '', $lowercaseWord));
            $insertDigraphException->bindValue(':separated_digraph', $lowercaseWord);
            $insertDigraphException->execute();
        } catch (Throwable $exception) {
            // Probably a duplicated word.
            continue;
        }
    }
}

fclose($file);

$sqlite->exec('end transaction');

unset($rows);


$sqlite->exec('begin transaction');

$insertPhrase = $sqlite->prepare('insert into phrases (ascii, serbian_latin) values (:ascii, :serbian_latin)');

$rows = explode("\n", file_get_contents($phrasesList));
foreach ($rows as $row) {
    if (empty(trim($row))) {
        continue;
    }

    $insertPhrase->bindValue(':ascii', strtolower(convertLatinToAsciiLatin($row)));
    $insertPhrase->bindValue(':serbian_latin', $row);
    $insertPhrase->execute();
}

$sqlite->exec('end transaction');

$sqlite->close();

$seconds = round(microtime(true) - $startTime, 3);
echo "Dictionary has been created in {$seconds} seconds.\n";
