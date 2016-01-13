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
 *
 * @package Laravolt\Acl
 * @see http://laravel.com/docs/5.1/packages#service-providers
 * @see http://laravel.com/docs/5.1/providers
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @see http://laravel.com/docs/5.1/providers#deferred-providers
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @see http://laravel.com/docs/5.1/providers#the-register-method
     * @return void
     */
    public function register()
    {
    }

    /**
     * Application is booting
     *
     * @see http://laravel.com/docs/5.1/providers#the-boot-method
     * @param Gate $gate
     */
    public function boot(Gate $gate)
    {

        $this->registerMigrations();
        $this->registerConfigurations();
        $this->registerEnum();

        if (!$this->skipAcl()) {
            $this->registerAcl($gate);
        }

        $this->registerCommands();
    }

    protected function registerAcl($gate)
    {
        $gate->before(function ($user) {
            $isAdmin = call_user_func(config('acl.is_admin'), $user);
            if ($isAdmin) {
                return true;
            }
        });

        $permissions = Permission::all();
        foreach ($permissions as $permission) {
            $gate->define($permission->name, function (HasRoleAndPermission $user) use ($permission) {
                return $user->hasPermission($permission);
            });
        }
    }

    protected function skipAcl()
    {
        $table_permissions_name = app('Laravolt\Acl\Models\Permission')->getTable();
        if (!Schema::hasTable($table_permissions_name)) {
            return true;
        }

        //if ($this->app->runningInConsole()) {
        //    return true;
        //}

        return false;
    }
    /**
     * Register the package migrations
     *
     * @see http://laravel.com/docs/5.1/packages#publishing-file-groups
     * @return void
     */
    protected function registerMigrations()
    {
        $this->publishes([
            $this->packagePath('database/migrations') => database_path('/migrations')
        ], 'migrations');
    }

    /**
     * Register the package configurations
     *
     * @see http://laravel.com/docs/5.1/packages#configuration
     * @return void
     */
    protected function registerConfigurations()
    {
        $this->mergeConfigFrom(
            $this->packagePath('config/config.php'), 'acl'
        );
        $this->publishes([
            $this->packagePath('config/config.php') => config_path('acl.php'),
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
     *
     * @param string $path
     * @return string
     */
    protected function packagePath($path = '')
    {
        return sprintf("%s/../%s", __DIR__, $path);
    }

}
