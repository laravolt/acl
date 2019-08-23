<?php
namespace Laravolt\Acl\Traits;

use Illuminate\Database\Eloquent\Model;
use Laravolt\Acl\Models\Role;
use Laravolt\Acl\Repository\PermissionRepo;
use Laravolt\Acl\Repository\RoleRepo;

trait HasRoleAndPermission
{
    public function roles()
    {
        return $this->belongsToMany(config('laravolt.acl.models.role'), 'acl_role_user', 'user_id', 'role_id');
    }

    public function hasRole($role, $checkAll = false)
    {
        if (is_array($role)) {
            $match = 0;
            foreach ($role as $r) {
                $match += (int)$this->hasRole($r, $checkAll);
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
            $role = (new RoleRepo())->whereNameFirst($role);
        }

        return $this->roles()->syncWithoutDetaching($role);
    }

    public function revokeRole($role)
    {
        if (is_string($role)) {
            $role = (new RoleRepo())->whereNameFirst($role);
        }

        return $this->roles()->detach($role);
    }

    public function syncRoles($roles)
    {
        $ids = collect($roles)->transform(function ($role) {
            if (is_numeric($role)) {
                return (int)$role;
            } elseif (is_string($role)) {
                $role = (new RoleRepo())->firstOrCreateName($role);

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
            $permission = (new PermissionRepo())->whereNotInName($permission);
        }

        if (is_integer($permission)) {
            $permission = (new PermissionRepo())->find($permission);
        }

        if (!$permission instanceof Model) {
            throw new \InvalidArgumentException('Argument must be integer, string, or an instance of '.Model::class);
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
