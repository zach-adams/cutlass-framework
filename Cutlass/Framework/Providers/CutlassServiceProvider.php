<?php namespace Cutlass\Framework\Providers;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\ServiceProvider;

class CutlassServiceProvider extends ServiceProvider {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerEloquent();

        $this->app->instance(
            'env',
            defined('CUTLASS_ENV') ? CUTLASS_ENV
                : (defined('WP_DEBUG') ? 'local'
                    : 'production')
        );

        $this->app->instance(
            'http',
            \Cutlass\Framework\Http::capture()
        );

        $this->app->alias(
            'http',
            'Cutlass\Framework\Http'
        );

        $this->app->instance(
            'router',
            $this->app->make('Cutlass\Framework\Router', ['app' => $this->app])
        );

        $this->app->bind(
            'route',
            'Cutlass\Framework\Route'
        );

        $this->app->instance(
            'enqueue',
            $this->app->make('Cutlass\Framework\Enqueue', ['app' => $this->app])
        );

        $this->app->alias(
            'enqueue',
            'Cutlass\Framework\Enqueue'
        );

        $this->app->instance(
            'panel',
            $this->app->make('Cutlass\Framework\Panel', ['app' => $this->app])
        );

        $this->app->alias(
            'panel',
            'Cutlass\Framework\Panel'
        );

        $this->app->instance(
            'shortcode',
            $this->app->make('Cutlass\Framework\Shortcode', ['app' => $this->app])
        );

        $this->app->alias(
            'shortcode',
            'Cutlass\Framework\Shortcode'
        );

        $this->app->instance(
            'widget',
            $this->app->make('Cutlass\Framework\Widget', ['app' => $this->app])
        );

        $this->app->alias(
            'widget',
            'Cutlass\Framework\Widget'
        );

        $this->app->instance(
            'session',
            $this->app->make('Cutlass\Framework\Session', ['app' => $this->app])
        );

        $this->app->alias(
            'session',
            'Cutlass\Framework\Session'
        );

        $this->app->instance(
            'notifier',
            $this->app->make('Cutlass\Framework\Notifier', ['app' => $this->app])
        );

        $this->app->alias(
            'notifier',
            'Cutlass\Framework\Notifier'
        );

        $this->app->singleton(
            'errors',
            function ()
            {
                return session_flashed('__validation_errors', []);
            }
        );

        $_GLOBALS['errors'] = $this->app['errors'];
    }

    /**
     * Registers Eloquent.
     *
     * @return void
     */
    protected function registerEloquent()
    {
        global $wpdb;

        $capsule = new Capsule($this->app);

        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => DB_HOST,
            'database' => DB_NAME,
            'username' => DB_USER,
            'password' => DB_PASSWORD,
            'charset' => DB_CHARSET,
            'collation' => DB_COLLATE ?: $wpdb->collate,
            'prefix' => $wpdb->prefix
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }

    /**
     * Boots the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['session']->start();
    }

}
