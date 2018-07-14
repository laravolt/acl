<?php

namespace Laravolt\Acl;

use Illuminate\Foundation\Application;
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
        $this->app->singleton('laravolt.acl', function(Application $app){
            return new Acl();
        });
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

        $this->registerAcl($gate);

        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }
    }

    protected function registerAcl($gate)
    {
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
        $this->app->singleton('laravolt.acl.sync-permission', function ($app) {

            return new SyncPermission($app['config']);
        });

        $this->commands('laravolt.acl.sync-permission');
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
