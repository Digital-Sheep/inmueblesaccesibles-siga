<?php

namespace App\Filament\Resources\Comercial\Propiedades\Pages;

use App\Filament\Resources\Comercial\Propiedades\PropiedadResource;
use App\Models\CatSucursal;
use App\Models\Propiedad;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

class ListPropiedades extends ListRecords
{
    protected static string $resource = PropiedadResource::class;

    public function getView(): string
    {
        return 'filament.resources.comercial.propiedades.pages.list-propiedades';
    }

    // -------------------------------------------------------
    // Estado de vista — persiste en URL para poder compartir
    // -------------------------------------------------------

    #[Url(as: 'v')]
    public string $vista = 'tabla'; // tabla | cards | mapa

    // -------------------------------------------------------
    // Filtros para vistas Cards y Mapa
    // -------------------------------------------------------

    #[Url(as: 'estatus')]
    public string $filtroEstatus = '';

    #[Url(as: 'tipo')]
    public string $filtroTipo = '';

    #[Url(as: 'sucursal')]
    public int $filtroSucursal = 0;

    public int $perPage = 12;

    // -------------------------------------------------------
    // Header actions
    // -------------------------------------------------------

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Agregar garantía')
                ->closeModalByClickingAway(false)
                ->modalWidth('5xl')
                ->slideOver()
                ->visible(function (): bool {
                    /** @var \App\Models\User $user */
                    $user = Auth::user();
                    return $user->can('propiedades_crear');
                })
                ->using(function (array $data, string $model): \Illuminate\Database\Eloquent\Model {
                    // Extraer fotos antes de crear — no son columnas de propiedades
                    $fotos = $data['fotos_repeater'] ?? [];
                    unset($data['fotos_repeater']);

                    $record = new $model();
                    $record->fill($data);
                    $record->save();

                    foreach ($fotos as $foto) {
                        if (empty($foto['ruta_archivo'])) {
                            continue;
                        }

                        $record->archivos()->create([
                            'categoria'       => $foto['categoria'] ?? 'FACHADA',
                            'ruta_archivo'    => $foto['ruta_archivo'],
                            'nombre_original' => basename($foto['ruta_archivo']),
                            'descripcion'     => $foto['descripcion'] ?? null,
                        ]);
                    }

                    return $record;
                }),
        ];
    }



    // -------------------------------------------------------
    // Query para Cards y Mapa (independiente de la Tabla)
    // -------------------------------------------------------

    public function getPropiedades(): LengthAwarePaginator
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return Propiedad::query()
            ->with(['archivos', 'municipio', 'sucursal', 'estado'])
            ->when(
                ! $user->can('propiedades_ver_todos'),
                fn(Builder $q) => $q->where('estatus_comercial', '!=', 'BORRADOR')
            )
            ->when(
                $this->filtroEstatus,
                fn(Builder $q) => $q->where('estatus_comercial', $this->filtroEstatus)
            )
            ->when(
                $this->filtroTipo,
                fn(Builder $q) => $q->where('tipo_inmueble', $this->filtroTipo)
            )
            ->when(
                $this->filtroSucursal,
                fn(Builder $q) => $q->where('sucursal_id', $this->filtroSucursal)
            )
            ->whereNotIn('estatus_comercial', ['BAJA'])
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    // -------------------------------------------------------
    // Datos para el mapa — solo propiedades con coordenadas
    // -------------------------------------------------------

    #[Computed]
    public function propiedadesParaMapa(): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return Propiedad::query()
            ->with(['archivos'])
            ->whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->when(
                ! $user->can('propiedades_ver_todos'),
                fn(Builder $q) => $q->where('estatus_comercial', '!=', 'BORRADOR')
            )
            ->when(
                $this->filtroEstatus,
                fn(Builder $q) => $q->where('estatus_comercial', $this->filtroEstatus)
            )
            ->when(
                $this->filtroTipo,
                fn(Builder $q) => $q->where('tipo_inmueble', $this->filtroTipo)
            )
            ->when(
                $this->filtroSucursal,
                fn(Builder $q) => $q->where('sucursal_id', $this->filtroSucursal)
            )
            ->whereNotIn('estatus_comercial', ['BAJA'])
            ->get()
            ->map(function (Propiedad $p) {
                $foto = $p->archivos->firstWhere('categoria', 'FACHADA')
                    ?? $p->archivos->first();

                return [
                    'id'             => $p->id,
                    'lat'            => (float) $p->latitud,
                    'lng'            => (float) $p->longitud,
                    'numero_credito' => $p->numero_credito,
                    'direccion'      => $p->direccion_completa,
                    'estatus'        => $p->estatus_comercial,
                    'precio'         => $p->precio_venta_con_descuento,
                    'habitaciones'   => $p->habitaciones,
                    'banos'          => $p->banos,
                    'construccion'   => $p->construccion_m2,
                    'imagen'         => $foto?->ruta_archivo,
                    'url'            => PropiedadResource::getUrl('view', ['record' => $p->id]),
                ];
            })
            ->values()
            ->toArray();
    }

    // -------------------------------------------------------
    // Opciones para selects de filtros
    // -------------------------------------------------------

    #[Computed]
    public function sucursales(): array
    {
        return CatSucursal::orderBy('nombre')->pluck('nombre', 'id')->toArray();
    }

    // -------------------------------------------------------
    // Cambiar vista — resetea paginación
    // -------------------------------------------------------

    public function cambiarVista(string $vista): void
    {
        $this->vista   = $vista;
        $this->perPage = 12;
        $this->resetPage();
        unset($this->propiedadesParaMapa);

        if ($vista === 'mapa') {
            $this->dispatch('vista-mapa-activada');
        }
    }

    public function updatedFiltroEstatus(): void
    {
        $this->perPage = 12;
        $this->resetPage();
        unset($this->propiedadesParaMapa);
    }

    public function updatedFiltroTipo(): void
    {
        $this->perPage = 12;
        $this->resetPage();
        unset($this->propiedadesParaMapa);
    }

    public function updatedFiltroSucursal(): void
    {
        $this->perPage = 12;
        $this->resetPage();
        unset($this->propiedadesParaMapa);
    }

    public function cargarMas(): void
    {
        $this->perPage += 12;
    }
}
