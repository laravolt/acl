# laravolt/acl

## Installation

### Composer
`composer require laravolt/acl`

### Service Provider
**Skip this step if you are using Laravel 5.5 or above.**

`Laravolt\Acl\ServiceProvider::class,`

### Migrations
Publish migration file **(optional)**:

`php artisan vendor:publish --provider="Laravolt\Acl\ServiceProvider" --tag=migrations`

Run migration:

`php artisan migrate`

### Publish Configuration (Optional)
`php artisan vendor:publish --provider="Laravolt\Acl\ServiceProvider" --tag=config`

### Usage
Add `Laravolt\Acl\Traits\HasRoleAndPermission` trait to `User` model:

```php
<?php

namespace App;

use Laravolt\Acl\Traits\HasRoleAndPermission;

class User
{
    use HasRoleAndPermission;
}
```
After that, `User` will have following methods:

#### `$user->roles()`, 
Relationships that defines `User` has many `Laravolt\Acl\Models\Role`.

#### `$user->hasRole($role, $checkAll = false)`
Check if specific `User` has one or many roles. Return boolean true or false.

#### `$user->assignRole($role)`
Assign one or many roles to specific `User`. Possible values for `$role` are: `id`, array of `id`, role name, `Role` object, or array of `Role` object.

#### `$user->revokeRole($role)`
Revoke/remove one or many roles from specific `User`. Possible values for `$role` are: `id`, array of `id`, role name, `Role` object, or array of `Role` object.

#### `$user->hasPermission($permission, $checkAll = false)`
Check if specific `User` has one or many permissions. Return boolean true or false.
    
### Command
`php artisan laravolt:acl:sync-permission`

### Bypass Authorization
You can bypass authorization checking using Laravel built-in method:
```php
// Place it somewhere before application running, e.g. in `AppServiceProvider`.
Gate::before(function($user){
    // check if $user superadmin
    // and then return true to skip all authorization checking
});
```
