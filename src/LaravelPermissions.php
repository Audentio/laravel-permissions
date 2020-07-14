<?php

namespace Audentio\LaravelPermissions;

use Audentio\LaravelBase\Foundation\AbstractModel;

class LaravelPermissions
{
    protected static $runsMigrations = true;

    public static function ignoreMigrations(bool $ignore = true): void
    {
        self::$runsMigrations = !$ignore;
    }

    public static function runsMigrations(): bool
    {
        return self::$runsMigrations;
    }
}