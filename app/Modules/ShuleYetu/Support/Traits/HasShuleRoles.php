<?php

namespace App\Modules\ShuleYetu\Support\Traits;

use App\Modules\ShuleYetu\Support\Rbac\ShuleRbacService;

trait HasShuleRoles
{
    public function hasRole(string $roleName): bool
    {
        return app(ShuleRbacService::class)->userHasRole($this, $roleName);
    }

    public function hasPermission(string $permissionName): bool
    {
        return app(ShuleRbacService::class)->userHasPermission($this, $permissionName);
    }

    public function assignRole(string $roleName): void
    {
        app(ShuleRbacService::class)->assignRole($this, $roleName);
    }

    public function givePermissionTo(string $permissionName): void
    {
        app(ShuleRbacService::class)->giveDirectPermission($this, $permissionName);
    }
}

