<?php
/*
 * Set specific configuration variables here
 */
return [
    'permission_enum' => \Laravolt\Acl\Enum\Permission::class,
    'is_admin'        => function ($user) {
        return false;
    },
];
