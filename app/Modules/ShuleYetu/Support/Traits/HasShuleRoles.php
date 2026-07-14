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

    public function getRoleNames(): \Illuminate\Support\Collection
    {
        return \Illuminate\Support\Facades\DB::table("shule_model_has_roles as mhr")
            ->join("shule_roles as r", "r.id", "=", "mhr.role_id")
            ->where("mhr.user_id", $this->id)
            ->where("mhr.school_id", app(\App\Modules\ShuleYetu\Support\Tenancy\SchoolContext::class)->requireId())
            ->where("r.school_id", app(\App\Modules\ShuleYetu\Support\Tenancy\SchoolContext::class)->requireId())
            ->pluck("r.name");
    }
}

