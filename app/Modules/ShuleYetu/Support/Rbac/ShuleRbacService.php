<?php

namespace App\Modules\ShuleYetu\Support\Rbac;

use App\Models\User;
use App\Modules\ShuleYetu\Models\ShulePermission;
use App\Modules\ShuleYetu\Models\ShuleRole;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShuleRbacService
{
    public function __construct(private readonly SchoolContext $schoolContext)
    {
    }

    public function assignRole(User $user, string $roleName, ?string $schoolId = null): void
    {
        $schoolId = $schoolId ?? $this->schoolContext->requireId();

        $role = ShuleRole::query()->firstOrCreate(
            ['school_id' => $schoolId, 'name' => $roleName],
            ['guard_name' => 'web']
        );

        DB::table('shule_model_has_roles')->insertOrIgnore([
            'id' => (string) Str::uuid(),
            'role_id' => $role->id,
            'user_id' => $user->id,
            'school_id' => $schoolId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function givePermissionToRole(string $roleName, string $permissionName, ?string $schoolId = null): void
    {
        $schoolId = $schoolId ?? $this->schoolContext->requireId();

        $role = ShuleRole::query()->firstOrCreate(
            ['school_id' => $schoolId, 'name' => $roleName],
            ['guard_name' => 'web']
        );

        $permission = ShulePermission::query()->firstOrCreate(
            ['name' => $permissionName],
            ['guard_name' => 'web']
        );

        DB::table('shule_role_has_permissions')->insertOrIgnore([
            'id' => (string) Str::uuid(),
            'role_id' => $role->id,
            'permission_id' => $permission->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function giveDirectPermission(User $user, string $permissionName, ?string $schoolId = null): void
    {
        $schoolId = $schoolId ?? $this->schoolContext->requireId();

        $permission = ShulePermission::query()->firstOrCreate(
            ['name' => $permissionName],
            ['guard_name' => 'web']
        );

        DB::table('shule_model_has_permissions')->insertOrIgnore([
            'id' => (string) Str::uuid(),
            'permission_id' => $permission->id,
            'user_id' => $user->id,
            'school_id' => $schoolId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function userHasRole(User $user, string $roleName): bool
    {
        $schoolId = $this->schoolContext->requireId();

        if ($user->is_system_admin) {
            return true;
        }

        return DB::table('shule_model_has_roles as mhr')
            ->join('shule_roles as r', 'r.id', '=', 'mhr.role_id')
            ->where('mhr.user_id', $user->id)
            ->where('mhr.school_id', $schoolId)
            ->where('r.school_id', $schoolId)
            ->where('r.name', $roleName)
            ->exists();
    }

    public function userHasPermission(User $user, string $permissionName): bool
    {
        $schoolId = $this->schoolContext->requireId();

        if ($user->is_system_admin) {
            return true;
        }

        $hasDirectPermission = DB::table('shule_model_has_permissions as mhp')
            ->join('shule_permissions as p', 'p.id', '=', 'mhp.permission_id')
            ->where('mhp.user_id', $user->id)
            ->where('mhp.school_id', $schoolId)
            ->where('p.name', $permissionName)
            ->exists();

        if ($hasDirectPermission) {
            return true;
        }

        return DB::table('shule_model_has_roles as mhr')
            ->join('shule_roles as r', 'r.id', '=', 'mhr.role_id')
            ->join('shule_role_has_permissions as rhp', 'rhp.role_id', '=', 'r.id')
            ->join('shule_permissions as p', 'p.id', '=', 'rhp.permission_id')
            ->where('mhr.user_id', $user->id)
            ->where('mhr.school_id', $schoolId)
            ->where('r.school_id', $schoolId)
            ->where('p.name', $permissionName)
            ->exists();
    }
}

