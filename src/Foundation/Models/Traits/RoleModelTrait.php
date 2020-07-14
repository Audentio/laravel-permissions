<?php

namespace Audentio\LaravelPermissions\Foundation\Models\Traits;

use Audentio\LaravelPermissions\Foundation\Models\Permission;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait RoleModelTrait
{
    public function permissions(): BelongsToMany
    {
        $permissionModel = config('audentioPermissions.permissionModelClass');
        $permissionRoleModel = config('audentioPermissions.permissionRoleModelClass');

        return $this->belongsToMany($permissionModel)
            ->using($permissionRoleModel)
            ->withPivot(['value'])
            ->withTimestamps();
    }

    public function hasPermission($permissionId): int
    {
        $permission = [
            'kind' => 'flag',
            'value' => 0,
        ];

        if (isset($this->permission_cache[$permissionId])) {
            $permission = $this->permission_cache[$permissionId];
        }

        switch ($permission['kind']) {
            case 'flag':
                return $permission['value'] ? 1 : 0;
            case 'int':
            case 'rint':
                return (int) $permission['value'];
        }

        return 0;
    }

    public function syncPermissions(array $permissions, bool $rebuildPermissionCache = true): void
    {
        if (!$this->exists) {
            throw new \LogicException('Role must be saved to sync permissions.');
        }

        $formattedPermissions = [];

        foreach ($permissions as $id => $value) {
            $formattedPermissions[$id] = ['value' => (int) $value];
        }

        $this->permissions()->sync($formattedPermissions);

        if ($rebuildPermissionCache) {
            $this->rebuildPermissionCache();
        }
    }

    public function rebuildPermissionCache(): void
    {
        if ($this->content_type) {
            $permissions = Permission::where([
                ['content_type', $this->content_type],
            ])->get();
        } else {
            $permissions = Permission::whereNull('content_type')->get();
        }

        if (empty($permissions)) {
            $this->permission_cache = [];
            $this->save();

            return;
        }

        $permissionCache = [];

        /** @var PermissionModelTrait $permission */
        foreach ($permissions as $permission) {
            $permissionCache[$permission->id] = [
                'kind' => $permission->kind,
                'value' => 0,
            ];
        }

        /** @var PermissionModelTrait $permission */
        foreach ($this->permissions as $permission) {
            if (!isset($permissionCache[$permission->id])) {
                continue;
            }

            $permissionCache[$permission->id]['value'] = $permission->pivot->value;
        }

        foreach ($permissionCache as $permissionId => $permission) {
            if ($permission['value'] === 0) {
                unset($permissionCache[$permissionId]);
            }
        }

        $this->permission_cache = $permissionCache;
        $this->save();
    }

    public function initializeRoleModelTrait()
    {
        $this->casts['permission_cache'] = 'json';
    }
}