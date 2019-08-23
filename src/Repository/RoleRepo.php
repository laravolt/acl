<?php

namespace Laravolt\Acl\Repository;

class RoleRepo
{
    protected $model;

    public function __construct()
    {
        $this->model = app(config('laravolt.acl.models.role'));
    }

    public function whereNameFirst(string $name)
    {
        return $this->model->where('name', $name)->first();
    }

    public function firstOrCreateName(string $name)
    {
        return $this->model->firstOrCreate(['name' => $name]);
    }
}