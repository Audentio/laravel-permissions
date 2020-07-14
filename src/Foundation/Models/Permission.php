<?php

namespace Audentio\LaravelPermissions\Foundation\Models;

use Audentio\LaravelBase\Foundation\AbstractModel;
use Audentio\LaravelPermissions\Foundation\Models\Interfaces\PermissionModelInterface;
use Audentio\LaravelPermissions\Foundation\Models\Traits\PermissionModelTrait;

class Permission extends AbstractModel implements PermissionModelInterface
{
    use PermissionModelTrait;

    protected $fillable = ['kind', 'content_type', 'content_kind'];
}