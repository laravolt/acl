<?php
namespace Laravolt\Acl\Traits;

use Laravolt\Acl\Models\Role;
use Laravolt\Acl\Models\Permission;

trait HasRoleAndPermission
{
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'acl_role_user');
    }

    public function hasRole($role)
    {
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

    public function hasPermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }

        if (is_integer($permission)) {
            $permission = Permission::find($permission);
        }

        if (!$permission instanceof Permission) {
            throw new \InvalidArgumentException('Argument must be integer, string, or an instance of ' . Permission::class);
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
