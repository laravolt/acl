<?php

namespace Laravolt\Acl\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'acl_permissions';

    protected $fillable = ['name'];
}
