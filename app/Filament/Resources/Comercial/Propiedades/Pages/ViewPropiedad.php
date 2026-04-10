<?php

namespace App\Filament\Resources\Comercial\Propiedades\Pages;

use App\Filament\Actions\AprobarPrecioAction;
use App\Filament\Actions\CalcularCotizacionAction;
use App\Filament\Actions\DecisionDGEAction;
use App\Filament\Actions\DecisionFinalPrecioAction;
use App\Filament\Actions\EliminarCotizacionAction;
use App\Filament\Actions\RechazarPrecioAction;
use App\Filament\Actions\RecotizarAction;
use App\Filament\Actions\ValidarYPublicarPropiedadAction;
use App\Filament\Clusters\Comercial\ComercialCluster;
use App\Filament\Resources\Comercial\Propiedades\PropiedadResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewPropiedad extends ViewRecord
{
    protected static string $resource = PropiedadResource::class;

    protected static ?string $cluster = ComercialCluster::class;

    public function getTitle(): string
    {
        return "Propiedad #{$this->record->numero_credito}";
    }

    public function getBreadcrumbs(): array
    {
        return [
            ComercialCluster::getUrl() => 'Comercial',
            PropiedadResource::getUrl('index') => 'Garantías',
            "#{$this->record->numero_credito}",
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Editar garantía')
                ->icon('heroicon-o-pencil')
                ->color('gray')
                ->slideOver()
                ->modalWidth('5xl')
                ->closeModalByClickingAway(false)
                ->visible(function () {
                    /** @var \App\Models\User $user */
                    $user = Auth::user();
                    return $user->can('propiedades_editar');
                })

                ->mutateRecordDataUsing(function (array $data): array {
                    $fotos = $this->record->archivos()
                        ->whereIn('categoria', ['FACHADA', 'INTERIOR', 'PATIO', 'PLANO', 'LEGAL', 'DAMAGE'])
                        ->get()
                        ->map(fn($archivo) => [
                            'ruta_archivo' => $archivo->ruta_archivo, // ← ruta completa, no basename
                            'categoria'    => $archivo->categoria,
                            'descripcion'  => $archivo->descripcion,
                        ])
                        ->toArray();

                    $data['fotos_repeater'] = $fotos;

                    return $data;
                })

                ->after(function ($record, array $data): void {
                    if (empty($data['fotos_repeater'])) {
                        return;
                    }

                    $record->archivos()
                        ->whereIn('categoria', ['FACHADA', 'INTERIOR', 'PATIO', 'PLANO', 'LEGAL', 'DAMAGE'])
                        ->delete();

                    foreach ($data['fotos_repeater'] as $foto) {
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
                }),

            // 2. CALCULAR PRECIO
            CalcularCotizacionAction::make(),

            RecotizarAction::make(),

            EliminarCotizacionAction::make(),

            // 3. VALIDAR Y PUBLICAR
            ValidarYPublicarPropiedadAction::make(),

            // 4. APROBAR PRECIO (Comercial o Contabilidad)
            AprobarPrecioAction::make(),

            // 5. RECHAZAR PRECIO (Comercial o Contabilidad)
            RechazarPrecioAction::make(),

            // 6. DECISIÓN FINAL (DGE)
            DecisionDGEAction::make(),
        ];
    }
}
