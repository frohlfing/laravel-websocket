<?php

namespace FRohlfing\WebSocket;

use FRohlfing\WebSocket\Console\Commands\WebSocketPushCommand;
use FRohlfing\WebSocket\Contracts\WebSocketHandler;
use FRohlfing\WebSocket\Services\WebSocket;
use Illuminate\Support\ServiceProvider;
use FRohlfing\WebSocket\Console\Commands\WebSocketServeCommand;
use RuntimeException;

class WebSocketServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * Wenn das Package Routen beinhaltet, muss hier false stehen!
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        // merge the custom config
        $this->mergeConfigFrom(__DIR__ . '/../config/websocket.php', 'websocket');

        // Register class

        $this->app->singleton(WebSocketHandler::class, function (\Illuminate\Container\Container $app, $parameters) {
            $handler = $app['config']['websocket']['handler'];
            if (!is_subclass_of($handler, WebSocketHandler::class)) {
                throw new RuntimeException($handler . ' has to implements ' . WebSocketHandler::class . '.');
            }
            return new $handler(...$parameters);
        });

        $this->app->singleton(WebSocket::class, function ($app) {
            return new WebSocket($app['config']['websocket']);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * This method is called after all other service providers have been registered, meaning you have access to all
     * other services that have been registered by the framework.
     *
     * @return void
     */
	public function boot()
	{
        // config
        $this->publishes([__DIR__ . '/../config/websocket.php' => config_path('websocket.php')], 'config');

        // assets
        $this->publishes([__DIR__ . '/../resources/js/websocket.js' => public_path('js/websocket.js')], 'public');

        // commands
        if ($this->app->runningInConsole()) {
            $this->commands([WebSocketServeCommand::class]);
            $this->commands([WebSocketPushCommand::class]);
        }
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [WebSocketHandler::class, WebSocket::class];
	}

}
