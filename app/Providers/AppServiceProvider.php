<?php

namespace App\Providers;

use App\Livewire\Juridico\DocumentosCarpetaComponent;
use App\Livewire\Juridico\GastosContabilidadComponent;
use App\Models\ActuacionDictamen;
use App\Models\ActuacionJuicio;
use App\Models\ActuacionNotaria;
use App\Models\Dictamen;
use App\Models\Interaccion;
use App\Models\ProcesoVenta;
use App\Observers\ActuacionDictamenObserver;
use App\Observers\ActuacionJuicioObserver;
use App\Observers\ActuacionNotariaObserver;
use App\Observers\DictamenObserver;
use App\Observers\InteraccionObserver;
use App\Observers\ProcesoVentaObserver;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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
        ActuacionJuicio::observe(ActuacionJuicioObserver::class);
        ActuacionNotaria::observe(ActuacionNotariaObserver::class);
        ActuacionDictamen::observe(ActuacionDictamenObserver::class);

        Livewire::component('juridico.documentos-carpeta', DocumentosCarpetaComponent::class);
        Livewire::component('juridico.gastos-contabilidad', GastosContabilidadComponent::class);

        // Morph map para documentos jurídicos
        // Evita guardar namespaces completos en archivos.entidad_type
        Relation::morphMap([
            'seguimiento_juicio'   => \App\Models\SeguimientoJuicio::class,
            'seguimiento_notaria'  => \App\Models\SeguimientoNotaria::class,
            'seguimiento_dictamen' => \App\Models\SeguimientoDictamen::class,
        ]);
    }
}
