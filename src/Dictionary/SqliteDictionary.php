<?php

declare(strict_types=1);

namespace Turanjanin\SerbianLanguageTools\Dictionary;

use Turanjanin\SerbianLanguageTools\Exceptions\InvalidDatabaseException;

class SqliteDictionary implements Dictionary
{
    private \SQLite3Stmt $asciiVariantStatement;
    private \SQLite3Stmt $digraphExceptionStatement;
    private array $phrases;

    public function __construct(string $database = null)
    {
        if ($database === null) {
            $database = __DIR__ . '/../../resources/dictionary.sqlite';
        }

        try {
            $sqlite = new \SQLite3($database, SQLITE3_OPEN_READONLY);
            $sqlite->enableExceptions(true);
            $this->asciiVariantStatement = $sqlite->prepare('select serbian_latin, frequency from ascii_variants where ascii = :ascii order by frequency desc');
            $this->digraphExceptionStatement = $sqlite->prepare('select separated_digraph from digraph_exceptions where serbian_latin = :serbian_latin');
        } catch (\Exception $exception) {
            throw InvalidDatabaseException::forFilename($database, $exception);
        }

        $this->phrases = [];

        $phrasesQuery = $sqlite->query('select serbian_latin from phrases');
        while ($row = $phrasesQuery->fetchArray(SQLITE3_ASSOC)) {
            $this->phrases[] = $row['serbian_latin'];
        }
    }

    public function getPhrases(): array
    {
        return $this->phrases;
    }

    public function getAsciiVariants(string $word): array
    {
        $this->asciiVariantStatement->bindValue('ascii', $word);
        $results = $this->asciiVariantStatement->execute();

        $variants = [];
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            $variants[] = new Variant($row['serbian_latin'], $row['frequency']);
        }

        return $variants;
    }

    public function getDigraphException(string $word): ?string
    {
        $this->digraphExceptionStatement->bindValue('serbian_latin', $word);
        $results = $this->digraphExceptionStatement->execute();

        return $results->fetchArray(SQLITE3_ASSOC)['separated_digraph'] ?? null;
    }
}
