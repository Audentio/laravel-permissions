<?php

namespace Audentio\LaravelPermissions\Foundation\Models\Interfaces;

use Audentio\LaravelBase\Foundation\AbstractModel;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

interface RoleOwnerInterface
{
    public function roles(): MorphToMany;

    public function hasPermission($permissionId, $contentType = null, $contentId = null): int;
    public function hasRole($role): bool;

    public function addRoles(array $roles, bool $rebuildPermissionCache = true): void;
    public function addRole(RoleModelInterface $role, bool $rebuildPermissionCache = true): void;
    public function addContentRoles(array $roles, AbstractModel $content, bool $rebuildPermissionCache = true): void;
    public function addContentRole(RoleModelInterface $role, AbstractModel $content, bool $rebuildPermissionCache = true): void;

    public function removeRoles(array $roles, bool $rebuildPermissionCache = true): void;
    public function removeRole(RoleModelInterface $role, bool $rebuildPermissionCache = true): void;
    public function removeContentRoles(array $roles, AbstractModel $content, bool $rebuildPermissionCache = true): void;
    public function removeContentRole(RoleModelInterface $role, AbstractModel $content, bool $rebuildPermissionCache = true): void;

    public function rebuildPermissionCache(): array;
}