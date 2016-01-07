<?php
/*
 * Set specific configuration variables here
 */
return [
    'permission_enum' => \App\Enum\Permission::class,
    'is_admin'        => function ($user) {
        return $user->id == 1;
    },
];
