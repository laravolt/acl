<?php

namespace Laravolt\Acl;

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
}
