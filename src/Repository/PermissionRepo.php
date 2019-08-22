<?php

class PermissionRepo
{
    public function all()
    {
        return config('laravolt.acl.models.permission')::all();
    }

    public function updateAll($key, $description)
    {
        return config('laravolt.epicentrum.models.permission')::whereId($key)->update(['description' => $description]);
    }
}