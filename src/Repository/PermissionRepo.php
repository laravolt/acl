<?php
namespace Laravolt\Acl\Repository;

class PermissionRepo
{
    protected $model;

    public function __construct()
    {
        $this->model = config('laravolt.acl.models.permission');
    }

    public function all()
    {
        return $this->model::all();
    }

    public function updateAll($key, string $description)
    {
        return $this->model::whereId($key)->update(['description' => $description]);
    }

    public function firstOrCreateName(string $name)
    {
        return $this->model::firstOrCreate(['name' => $name]);
    }

    public function firstOrNewName(string $name)
    {
        return $this->model::firstOrNew(['name' => $name]);
    }

    public function whereNotInName(array $name)
    {
        return $this->model::whereNotIn('name', $name)->get();
    }

    public function whereNameFirst(string $permission)
    {
        return $this->model::where('name', $permission)->first();
    }

    public function find($permission)
    {
        return $this->model::find($permission);
    }
}