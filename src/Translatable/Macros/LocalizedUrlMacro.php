<?php

namespace mPhpMaster\Translatable\Macros;

use mPhpMaster\Translatable\LocalizedUrlGenerator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

class LocalizedUrlMacro
{
    /**
     * Register the macro.
     *
     * @return void
     */
    public static function register()
    {
        Route::macro('localizedUrl', function ($locale = null, $parameters = null, $absolute = true) {
            return App::make(LocalizedUrlGenerator::class)->generateFromRequest($locale, $parameters, $absolute);
        });
    }
}
