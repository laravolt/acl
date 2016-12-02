<?php

namespace Laravolt\Acl;

use Illuminate\Support\Facades\Schema;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Laravolt\Acl\Commands\SyncPermission;
use Laravolt\Acl\Contracts\HasRoleAndPermission;
use Laravolt\Acl\Models\Permission;

/**
 * Class PackageServiceProvider
 * @package Laravolt\Acl
 * @see http://laravel.com/docs/master/packages#service-providers
 * @see http://laravel.com/docs/master/providers
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     * @see http://laravel.com/docs/master/providers#deferred-providers
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     * @see http://laravel.com/docs/master/providers#the-register-method
     * @return void
     */
    public function register()
    {
    }

    /**
     * Application is booting
     * @see http://laravel.com/docs/master/providers#the-boot-method
     * @param Gate $gate
     */
    public function boot(Gate $gate)
    {

        $this->registerMigrations();
        $this->registerConfigurations();
        $this->registerEnum();

        $this->registerAcl($gate);

        $this->registerCommands();
    }

    protected function registerAcl($gate)
    {
        $gate->before(function ($user) {

            $isRootConfig = config('laravolt.acl.is_root');

            $isRoot = false;
            if ($isRootConfig instanceof \Closure) {
                $isRoot = call_user_func($isRootConfig, $user);
            } elseif (is_string($isRootConfig) && method_exists($user, $isRootConfig)) {
                $isRoot = $user->$isRootConfig();
            }

            if ($isRoot) {
                return true;
            }
        });

        if ($this->hasPermissionTable()) {
            $this->definePermission($gate);
        }
    }

    protected function hasPermissionTable()
    {
        try {
            $table_permissions_name = app('Laravolt\Acl\Models\Permission')->getTable();

            return Schema::hasTable($table_permissions_name);
        } catch (\PDOException $e) {
            return false;
        }
    }

    protected function definePermission(Gate $gate)
    {
        $permissions = Permission::all();
        foreach ($permissions as $permission) {
            $gate->define($permission->name, function (HasRoleAndPermission $user) use ($permission) {
                return $user->hasPermission($permission);
            });
        }
    }

    /**
     * Register the package migrations
     * @see http://laravel.com/docs/master/packages#publishing-file-groups
     * @return void
     */
    protected function registerMigrations()
    {
        $this->loadMigrationsFrom($this->packagePath('database/migrations'));
    }

    /**
     * Register the package configurations
     * @see http://laravel.com/docs/master/packages#configuration
     * @return void
     */
    protected function registerConfigurations()
    {
        $this->mergeConfigFrom(
            $this->packagePath('config/config.php'), 'laravolt.acl'
        );
        $this->publishes([
            $this->packagePath('config/config.php') => config_path('laravolt/acl.php'),
        ], 'config');
    }

    protected function registerCommands()
    {
        $this->app->singleton('command.laravolt.acl.sync-permission', function ($app) {

            return new SyncPermission($app['config']);
        });

        $this->commands('command.laravolt.acl.sync-permission');
    }

    protected function registerEnum()
    {
        $this->publishes([
            $this->packagePath('stubs/app/Enum/Permission.php') => app_path('Enum/Permission.php'),
        ], 'enum');
    }

    /**
     * Loads a path relative to the package base directory
     * @param string $path
     * @return string
     */
    protected function packagePath($path = '')
    {
        return sprintf("%s/../%s", __DIR__, $path);
    }

}
