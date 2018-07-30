<?php

namespace Hostville\Dorcas\LaravelCompat;


use Hostville\Dorcas\LaravelCompat\Auth\DorcasUser;
use Hostville\Dorcas\LaravelCompat\Auth\DorcasUserProvider;
use Hostville\Dorcas\Sdk;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\ServiceProvider;

class DorcasServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap required services
     */
    public function boot()
    {
        // publish the config file
        $this->publishes([
            __DIR__.'/config/dorcas-api.php' => config_path('dorcas-api.php'),
        ]);

        // check if the Sdk has already been added to the container
        if (!$this->app->has(Sdk::class)) {
            $tokenStoreId = Cookie::get('store_id');
            /**
             * Dorcas SDK
             */
            $this->app->singleton(Sdk::class, function ($app) use ($tokenStoreId) {
                $token = !empty($tokenStoreId) ? Cache::get('dorcas.auth_token.'.$tokenStoreId, null) : null;
                # get the token from the cache, if available
                $config = $app->make('config');
                # get the configuration object
                $config = [
                    'environment' => $config->get('dorcas-api.env'),
                    'credentials' => [
                        'id' => $config->get('dorcas-api.client.id'),
                        'secret' => $config->get('dorcas-api.client.secret'),
                        'token' => $token
                    ]
                ];
                return new Sdk($config);
            });
        }
        // add the Dorcas API user provider
        $this->app->when(DorcasUser::class)
                    ->needs(Sdk::class)
                    ->give(function () {
                        return $this->app->make(Sdk::class);
                    });
        # provide the requirement
        Auth::provider('dorcas', function ($app, array $config) {
            return new DorcasUserProvider($app->make(Sdk::class), $config);
        });
    }
}