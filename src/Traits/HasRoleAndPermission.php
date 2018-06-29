<?php
namespace Laravolt\Acl\Traits;

use Laravolt\Acl\Models\Role;
use Laravolt\Acl\Models\Permission;

trait HasRoleAndPermission
{
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'acl_role_user', 'user_id', 'role_id');
    }

    public function hasRole($role, $checkAll = false)
    {
        if (is_array($role)) {
            $match = 0;
            foreach ($role as $r) {
                $match += (int)$this->hasRole($r);
            }

            if ($checkAll) {
                return $match == count($role);
            } else {
                return $match > 0;
            }
        }

        if (is_string($role)) {
            $role = Role::where('name', $role)->first();
        }

        if (is_integer($role)) {
            $role = Role::find($role);
        }

        if (!$role instanceof Role) {
            return false;
        }

        foreach ($this->roles as $assignedRole) {
            if ($assignedRole->id == $role->id) {
                return true;
            }
        }

        return false;
    }

    public function assignRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->first();
        }

        return $this->roles()->attach($role);
    }

    public function revokeRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->first();
        }

        return $this->roles()->detach($role);
    }

    public function syncRoles($roles)
    {
        $ids = collect($roles)->transform(function ($role) {
            if (is_numeric($role)) {
                return (int)$role;
            } elseif (is_string($role)) {
                $role = Role::firstOrCreate(['name' => $role]);

                return $role->getKey();
            }
        })->filter(function ($id) {
            return $id > 0;
        });

        return $this->roles()->sync($ids);
    }

    public function hasPermission($permission, $checkAll = false)
    {
        if (is_array($permission)) {
            $match = 0;
            foreach ($permission as $perm) {
                $match += (int)$this->hasPermission($perm);
            }

            if ($checkAll) {
                return $match == count($permission);
            } else {
                return $match > 0;
            }
        }

        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }

        if (is_integer($permission)) {
            $permission = Permission::find($permission);
        }

        if (!$permission instanceof Permission) {
            throw new \InvalidArgumentException('Argument must be integer, string, or an instance of '.Permission::class);
        }

        foreach ($this->roles as $assignedRole) {
            foreach ($assignedRole->permissions as $assignedPermission) {
                if ($permission->id === $assignedPermission->id) {
                    return true;
                }
            }
        }

        return false;
    }
}
