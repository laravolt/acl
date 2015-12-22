<?php

namespace Laravolt\Acl;

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
        $this->registerSeeds();
        $this->registerConfigurations();

        //if (!$this->app->runningInConsole()) {
            $this->registerAcl($gate);
        //}

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
     * Register the package database seeds
     *
     * @return void
     */
    protected function registerSeeds()
    {
        $this->publishes([
            $this->packagePath('database/seeds') => database_path('/seeds')
        ], 'seeds');
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
