<?php
namespace Wnsm;


class Migrate
{
    public static function migrate($migrationsPath, $codeVersion, $dbVersionOptionName)
    {
        $migrationsPath = self::checkNotEmptyString($migrationsPath, 'migrationsPath');
        $codeVersion = self::checkNotEmptyString($codeVersion, 'codeVersion');
        $dbVersionOptionName = self::checkNotEmptyString($dbVersionOptionName, 'dbVersionOptionName');

        $dbVersion = get_option($dbVersionOptionName);
        if (empty($dbVersion)) {
            $dbVersion = '0.0.0.0';
        }

        if (version_compare($dbVersion, $codeVersion, '<')) {

            $migrationFiles = self::loadSortedMigrationFiles($migrationsPath);

            foreach ($migrationFiles as $fromVersion => $file) {
                if (version_compare($dbVersion, $fromVersion, '<')) {
                    /** @noinspection PhpIncludeInspection */
                    include($file);
                }
            }

            update_option($dbVersionOptionName, $codeVersion);
        }
    }


    private static function loadSortedMigrationFiles($migrationsPath)
    {
        $files = glob($migrationsPath . '/*.php', GLOB_NOSORT);

        foreach ($files as $key => $file) {
            unset($files[$key]);
            $files[pathinfo($file, PATHINFO_FILENAME)] = $file;
        }

        uksort($files, 'version_compare');

        return $files;
    }

    private static function checkNotEmptyString($value, $param)
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException("'$param' must be a string");
        }

        $value = trim($value);
        if ($value === '') {
            throw new \InvalidArgumentException("'$param' must be a non-empty string");
        }

        return $value;
    }
}
