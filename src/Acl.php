<?php

namespace Laravolt\Acl;

use Illuminate\Support\Facades\DB;
use Laravolt\Acl\Repository\PermissionRepo;

class Acl
{

    /**
     * All of the registered permissions.
     *
     * @var array
     */
    protected $permissions = [];

    public function permissions()
    {
        return $this->permissions;
    }

    public function registerPermission($permission)
    {
        $this->permissions = array_unique(array_merge($this->permissions, (array)$permission));

        return $this;
    }

    public function syncPermission($clean = false)
    {
        if ($clean) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
            config('laravolt.acl.models.permission')::truncate();
        }

        $items = collect();
        foreach ($this->permissions() as $name) {
            $permission = (new PermissionRepo())->firstOrNewName($name);
            $status = 'No Change';

            if (!$permission->exists) {
                $permission->save();
                $status = 'New';
            }

            $items->push(['id' => $permission->getKey(), 'name' => $name, 'status' => $status]);
        }

        // delete unused permissions
        $unusedPermissions = (new PermissionRepo())->whereNotInName($this->permissions());
        foreach ($unusedPermissions as $permission) {
            $items->push(['id' => $permission->getKey(), 'name' => $permission->name, 'status' => 'Deleted']);
            $permission->delete();
        }

        $items = $items->sortBy('name');

        return $items;
    }
}
