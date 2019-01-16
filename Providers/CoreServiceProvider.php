<?php

namespace Modules\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Route::namespace('Modules\Core\Http\Controllers')
            ->group(__DIR__ . '/../Routes/web.php');
        Route::namespace('Modules\Core\Http\Controllers')
            ->group(__DIR__ . '/../Routes/api.php');

        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');

        $this->publishes([
            __DIR__ . '/../Config/core.php' => config_path('core.php')
        ]);

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
