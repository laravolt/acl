<?php
namespace Laravolt\Acl\Contracts;

interface HasRoleAndPermission
{
    public function roles();

    public function hasRole($role);

    public function assignRole($role);

    public function revokeRole($role);

    public function hasPermission($permission);
}
