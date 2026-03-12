<?php

namespace App\Providers;

use App\Models\Dictamen;
use App\Models\Interaccion;
use App\Models\ProcesoVenta;

use App\Observers\DictamenObserver;
use App\Observers\InteraccionObserver;
use App\Observers\ProcesoVentaObserver;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Interaccion::observe(InteraccionObserver::class);
        Dictamen::observe(DictamenObserver::class);
        ProcesoVenta::observe(ProcesoVentaObserver::class);
    }
}
