<?php

namespace Laravolt\Acl\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

trait HasRoleAndPermission
{
    public function roles()
    {
        return $this->belongsToMany(config('laravolt.acl.models.role'), 'acl_role_user', 'user_id', 'role_id');
    }

    public function permissions()
    {
        // save users permissions result for current request
        return Cache::driver('array')->rememberForever("users.{$this->getKey()}.permissions", function () {
            /** @var Permission $permissionModel */
            $permissionModel = app(config('laravolt.acl.models.permission'));

            return $permissionModel
                ->newModelQuery()
                ->selectRaw('acl_permissions.*')
                ->join('acl_permission_role', 'acl_permissions.id', '=', 'acl_permission_role.permission_id')
                ->join('acl_role_user', 'acl_role_user.role_id', '=', 'acl_permission_role.role_id')
                ->join('users', 'users.id', '=', 'acl_role_user.user_id')
                ->where('users.id', $this->getKey())
                ->get()->unique();
        });
    }    

    public function hasRole($role, $checkAll = false)
    {
        if (is_array($role)) {
            $match = 0;
            foreach ($role as $r) {
                $match += (int) $this->hasRole($r, $checkAll);
            }

            if ($checkAll) {
                return $match == count($role);
            } else {
                return $match > 0;
            }
        }

        if (is_string($role)) {
            $role = $this->roles->firstWhere('name', $role);
        }

        if (is_integer($role)) {
            $role = $this->roles->firstWhere('id', $role);
        }

        if (!$role instanceof Model) {
            return false;
        }

        foreach ($this->roles as $assignedRole) {
            if ($role->is($assignedRole)) {
                return true;
            }
        }

        return false;
    }

    public function assignRole($role)
    {
        if (is_string($role)) {
            $role = app(config('laravolt.acl.models.role'))->where('name', $role)->first();
        }

        return $this->roles()->syncWithoutDetaching($role);
    }

    public function revokeRole($role)
    {
        if (is_string($role)) {
            $role = app(config('laravolt.acl.models.role'))->where('name', $role)->first();
        }

        return $this->roles()->detach($role);
    }

    public function syncRoles($roles)
    {
        $ids = collect($roles)->transform(function ($role) {
            if (is_numeric($role)) {
                return (int) $role;
            } elseif (is_string($role)) {
                $role = app(config('laravolt.acl.models.role'))->firstOrCreate(['name' => $role]);

                return $role->getKey();
            }
        })->filter(function ($id) {
            return $id > 0;
        });

        return $this->roles()->sync($ids);
    }

    public function hasPermission($permission, $checkAll = false)
    {
        return once(function () use ($permission, $checkAll) {
            return $this->_hasPermission($permission, $checkAll);
        });
    }

    protected function _hasPermission($permission, $checkAll)
    {
        if (is_array($permission)) {
            $match = 0;
            foreach ($permission as $perm) {
                $match += (int) $this->hasPermission($perm);
            }

            if ($checkAll) {
                return $match == count($permission);
            } else {
                return $match > 0;
            }
        }

        if (is_string($permission)) {
            $permission = $this->permissions()->firstWhere('name', $permission);
        }

        if (is_integer($permission)) {
            $permission = $this->permissions()->firstWhere('id', $permission);
        }

        if (!$permission instanceof Model) {
            return false;
        }

        foreach ($this->roles as $assignedRole) {
            foreach ($assignedRole->permissions as $assignedPermission) {
                if ($permission->is($assignedPermission)) {
                    return true;
                }
            }
        }

        return false;
    }
}
