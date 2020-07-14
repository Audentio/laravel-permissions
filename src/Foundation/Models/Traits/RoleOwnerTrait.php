<?php

namespace Audentio\LaravelPermissions\Foundation\Models\Traits;

use Audentio\LaravelBase\Foundation\AbstractModel;
use Audentio\LaravelBase\Utils\ContentTypeUtil;
use Audentio\LaravelPermissions\Foundation\Models\Interfaces\RoleModelInterface;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait RoleOwnerTrait
{
    public function roles(): MorphToMany
    {
        //
        return $this->morphToMany(config('audentioPermissions.roleModelClass'), 'owner', 'role_assignments', 'owner_id', 'role_id')
            ->withPivot([
                'content_type', 'content_id',
            ])->withTimestamps();
    }

    public function hasPermission($permissionId, $contentType = null, $contentId = null): int
    {
        if ($this->rebuild_permissions) {
            $this->rebuildPermissionCache();
        }

        $permission = [
            'kind' => 'flag',
            'value' => false,
        ];

        $overrideValue = $this->hasPermissionOverride($permissionId, $contentType, $contentId);
        if ($overrideValue !== null) {
            return $overrideValue;
        }

        $contentKey = 'global';
        if ($contentType) {
            if ($contentType instanceof AbstractModel) {
                $contentId = $contentType->getKey();
                $contentType = $contentType->getContentType();
            }

            $contentKey = ContentTypeUtil::getFriendlyContentTypeName($contentType) . '__' . $contentId;
        }

        if (isset($this->permission_cache[$contentKey][$permissionId])) {
            $permission = $this->permission_cache[$contentKey][$permissionId];
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

    public function hasRole($role): bool
    {
        if ($role instanceof RoleModelInterface) {
            $role = $role->id;
        }

        return in_array($role, $this->role_ids);
    }

    public function addRoles(array $roles, bool $rebuildPermissionCache = true): void
    {
        foreach ($roles as $role) {
            $this->addRole($role, false);
        }

        if ($rebuildPermissionCache) {
            $this->rebuildPermissionCache();
        }
    }

    public function addRole(RoleModelInterface $role, bool $rebuildPermissionCache = true): void
    {
        if ($role->content_type) {
            return;
        }

        if (!$this->roles()->where('role_assignments.role_id', $role->id)->exists()) {
            $this->roles()->attach($role->id);
        }

        if ($rebuildPermissionCache) {
            $this->rebuildPermissionCache();
        }
    }

    public function addContentRoles(array $roles, AbstractModel $content, bool $rebuildPermissionCache = true): void
    {
        foreach ($roles as $role) {
            $this->addContentRole($role, $content, false);
        }

        if ($rebuildPermissionCache) {
            $this->rebuildPermissionCache();
        }
    }

    public function addContentRole(RoleModelInterface $role, AbstractModel $content, bool $rebuildPermissionCache = true): void
    {
        if (!$role->content_type || $role->content_type !== $content->getContentType()) {
            return;
        }

        if (!$this->roles()->where([
            ['role_assignments.role_id', $role->id],
            ['role_assignments.content_type', $content->getContentType()],
            ['role_assignments.content_id', $content->getKey()],
        ])->exists()) {
            $this->roles()->attach([
                $role->id => [
                    'content_type' => $content->getContentType(),
                    'content_id' => $content->getKey(),
                ],
            ]);
        }

        if ($rebuildPermissionCache) {
            $this->rebuildPermissionCache();
        }
    }

    public function removeRoles(array $roles, bool $rebuildPermissionCache = true): void
    {
        foreach ($roles as $role) {
            $this->removeRole($role, false);
        }

        if ($rebuildPermissionCache) {
            $this->rebuildPermissionCache();
        }
    }

    public function removeRole(RoleModelInterface $role, bool $rebuildPermissionCache = true): void
    {
        if ($role->content_type) {
            return;
        }

        if ($this->roles()->where('role_assignments.role_id', $role->id)->exists()) {
            $this->roles()->detach($role->id);
        }

        if ($rebuildPermissionCache) {
            $this->rebuildPermissionCache();
        }
    }

    public function removeContentRoles(array $roles, AbstractModel $content, bool $rebuildPermissionCache = true): void
    {
        foreach ($roles as $role) {
            $this->removeContentRole($role, $content, false);
        }

        if ($rebuildPermissionCache) {
            $this->rebuildPermissionCache();
        }
    }

    public function removeContentRole(RoleModelInterface $role, AbstractModel $content, bool $rebuildPermissionCache = true): void
    {
        if (!$role->content_type || $role->content_type !== $content->getContentType()) {
            return;
        }

        if ($this->roles()->where([
            ['role_assignments.role_id', $role->id],
            ['role_assignments.content_type', $content->getContentType()],
            ['role_assignments.content_id', $content->getKey()],
        ])->exists()) {
//            $this->roles()->detach($role->id);
        }

        if ($rebuildPermissionCache) {
            $this->rebuildPermissionCache();
        }
    }

    public function rebuildPermissionCache(): array
    {
        if (!$this->canRebuildPermissions()) {
            return [];
        }

        $permissionCache = [];
        $roleIds = [];
        $this->load('roles');

        foreach ($this->roles as $role) {
            $roleIds = [];
            $contentKey = 'global';
            if ($role->content_type) {
                $contentKey = ContentTypeUtil::getFriendlyContentTypeName($role->pivot->content_type) . '__' . $role->pivot->content_id;
            }

            if (!isset($permissionCache[$contentKey])) {
                $permissionCache[$contentKey] = [];
            }

            foreach ($role->permission_cache as $permissionId => $permission) {
                if (!isset($permissionCache[$contentKey][$permissionId])) {
                    $permissionCache[$contentKey][$permissionId] = $permission;
                    continue;
                }

                $current = $permissionCache[$contentKey][$permissionId];
                if ($current['value'] === -1) {
                    continue;
                }

                $replaceCurrent = false;
                switch(permission['kind']) {
                    case 'bool':
                    case 'int':
                        if ($permission['value'] > $current['value']) $replaceCurrent = true;
                        break;

                    case 'rint':
                        if ($permission['value'] < $current['value']) $replaceCurrent = false;
                        break;
                }
            }
        }

        foreach ($permissionCache as $contentKey => $permissions) {
            foreach ($permissions as $permissionId => $permission) {
                if ($permission['value'] === 0) {
                    unset($permissionCache[$contentKey][$permissionId]);
                }
            }
        }

        dump($permissionCache);die;
    }

    protected function canRebuildPermissions(): bool
    {
        return true;
    }

    protected function hasPermissionOverride($permissionId, $contentType, $contentId): ?int
    {
        return null;
    }
}