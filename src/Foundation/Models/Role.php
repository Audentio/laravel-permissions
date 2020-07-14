<?php

namespace Audentio\LaravelPermissions\Foundation\Models;

use Audentio\LaravelBase\Foundation\AbstractModel;
use Audentio\LaravelPermissions\Foundation\Models\Interfaces\RoleModelInterface;
use Audentio\LaravelPermissions\Foundation\Models\Traits\RoleModelTrait;

class Role extends AbstractModel implements RoleModelInterface
{
    use RoleModelTrait;
}