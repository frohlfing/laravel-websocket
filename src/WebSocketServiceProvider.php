<?php

namespace FRohlfing\WebSocket;

use Illuminate\Support\ServiceProvider;
use FRohlfing\WebSocket\Console\Commands\WebSocketServeCommand;

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

        $this->app->singleton(MessageComponent::class, function (\Illuminate\Container\Container $app) {
            $controllerName = $app['config']['websocket']['controller'];
            if (!is_subclass_of($controllerName, BaseWebSocketController::class)) {
                throw new WebSocketException($controllerName . " has to extend BaseWebSocketController");
            }
            $controller = $app->make($controllerName);
            return new MessageComponent($controller);
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
        $this->publishes([__DIR__ . '/../public' => public_path('vendor/websocket/')], 'public');

        // commands
        if ($this->app->runningInConsole()) {
            $this->commands([WebSocketServeCommand::class]);
        }
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [MessageComponent::class, WebSocket::class];
	}

}
