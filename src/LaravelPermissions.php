<?php

namespace Audentio\LaravelPermissions;

use Audentio\LaravelBase\Foundation\AbstractModel;
use Audentio\LaravelBase\Illuminate\Database\Schema\Blueprint;

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

    public static function addSchemaToRoleOwner(Blueprint $table) {
        $table->json('role_ids')->nullable();
        $table->json('permission_cache')->nullable();
    }
}