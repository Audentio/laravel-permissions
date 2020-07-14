<?php

namespace Audentio\LaravelPermissions\Foundation\Models\Interfaces;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface RoleModelInterface
{
    public function permissions(): BelongsToMany;
    public function hasPermission($permissionId): int;
    public function rebuildPermissionCache(): void;
}