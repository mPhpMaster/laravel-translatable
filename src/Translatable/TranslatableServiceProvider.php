<?php

namespace mPhpMaster\Translatable;

use Illuminate\Support\ServiceProvider;

class TranslatableServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/laravel-translatable.php' => config_path('laravel-translatable.php'),
        ], 'laravel-translatable');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel-translatable.php', 'laravel-translatable'
        );

        $this->registerTranslatableHelper();
    }

    protected function registerTranslatableHelper()
    {
        $this->app->singleton('laravel-translatable.locales', Locales::class);
        $this->app->singleton(Locales::class);
    }
}
