<?php

namespace Laravolt\Acl\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'acl_roles';

    protected $fillable = ['name', 'description'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'acl_permission_role');
    }

    public function users()
    {
        return $this->belongsToMany(config('auth.model'), 'acl_role_user');
    }

    public function addPermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }

        return $this->permissions()->attach($permission);
    }

    public function removePermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }

        return $this->permissions()->detach($permission);
    }

    public function syncPermission(array $permissions)
    {
        $ids = collect($permissions)->filter(function ($id) {
            return $id > 0;
        });

        return $this->permissions()->sync($ids->toArray());
    }

}
