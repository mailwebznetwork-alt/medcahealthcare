<?php

namespace App\Support;

final class SqliteDatabaseFile
{
    private const string MAGIC_HEADER = "SQLite format 3\x00";

    /**
     * Resolved filesystem path for the default SQLite connection, or null when not applicable.
     */
    public static function defaultConnectionFilesystemPath(): ?string
    {
        if (config('database.default') !== 'sqlite') {
            return null;
        }

        $database = config('database.connections.sqlite.database');
        if (! is_string($database) || $database === '' || $database === ':memory:') {
            return null;
        }

        return $database;
    }

    public static function startsWithSqliteMagic(string $path): bool
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return false;
        }

        $header = fread($handle, 16);
        fclose($handle);

        return is_string($header) && $header === self::MAGIC_HEADER;
    }
}
