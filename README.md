# laravolt/acl

## Installation

### Composer
`composer require laravolt/acl`

### Service Provider
`Laravolt\Acl\ServiceProvider::class,`

### Migrations
Publish migration file:

`php artisan vendor:publish --provider="Laravolt\Acl\ServiceProvider" --tag=migrations`

Run migration:

`php artisan migrate`

### Publish Configuration
`php artisan vendor:publish --provider="Laravolt\Acl\ServiceProvider" --tag=config`

### Command
`php artisan laravolt:acl:sync-permission`
