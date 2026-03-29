<?php

namespace App\Filament\Resources\Juridico\SeguimientoNotarias\Schemas;

use App\Models\CatCarpetaJuridica;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Facades\Auth;

class SeguimientoNotariaInfolist
{
    public static function schema(): array
    {
        return [
            Tabs::make('tabs')
                ->tabs(array_merge(
                    [
                        Tab::make('📋 Información')
                            ->schema([
                                Section::make('Identificación')
                                    ->schema([
                                        Grid::make(3)->schema([
                                            TextEntry::make('id_garantia')
                                                ->label('ID Garantía')
                                                ->default('—'),

                                            TextEntry::make('numero_credito')
                                                ->label('Núm. Crédito')
                                                ->default('—'),

                                            TextEntry::make('nombre_cliente')
                                                ->label('Cliente')
                                                ->default('Sin cliente'),

                                            TextEntry::make('sede')
                                                ->label('Sede')
                                                ->badge(),

                                            TextEntry::make('notario')
                                                ->label('Notario')
                                                ->default('—'),

                                            TextEntry::make('numero_escritura')
                                                ->label('Núm. Escritura')
                                                ->default('—'),

                                            TextEntry::make('fecha_escritura')
                                                ->label('Fecha Escritura')
                                                ->formatStateUsing(
                                                    fn ($state, $record) => $record->fecha_escritura
                                                        ? $record->fecha_escritura->format('d/m/Y')
                                                        : '—'
                                                ),

                                            TextEntry::make('administradora_label')
                                                ->label('Administradora')
                                                ->getStateUsing(
                                                    fn ($record) => $record->nombre_administradora ?? '—'
                                                ),

                                            IconEntry::make('activo')
                                                ->label('Activo')
                                                ->boolean(),
                                        ]),
                                    ]),
                            ]),

                        Tab::make('📍 Seguimiento')
                            ->schema([
                                Section::make('Estado del seguimiento')
                                    ->schema([
                                        TextEntry::make('etapa_actual')
                                            ->label('Etapa Actual')
                                            ->default('Sin información')
                                            ->columnSpanFull(),

                                        TextEntry::make('notas_director')
                                            ->label('Notas Director / UCP')
                                            ->default('—')
                                            ->columnSpanFull()
                                            ->visible(function () {
                                                /** @var \App\Models\User $user */
                                                $user = Auth::user();

                                                return $user->can('seguimientonotarias_ver_todos');
                                            }),
                                    ]),
                            ]),
                    ],
                    self::tabsCarpetas('seguimientonotarias_editar')
                ))
                ->columnSpanFull(),
        ];
    }

    // ── Tabs dinámicos de carpetas documentales ────────────────────────────────

    /**
     * Genera un Tab por cada carpeta activa en el catálogo.
     * Si se agregan nuevas carpetas en BD, aparecen automáticamente.
     *
     * @return Tab[]
     */
    private static function tabsCarpetas(string $permisoEditar): array
    {
        return CatCarpetaJuridica::activas()
            ->get()
            ->map(function (CatCarpetaJuridica $carpeta) use ($permisoEditar) {
                return Tab::make('📁 ' . $carpeta->nombre)
                    ->schema([
                        ViewEntry::make('carpeta_' . $carpeta->slug)
                            ->label('')
                            ->columnSpanFull()
                            ->view('filament.juridico.documentos-carpeta')
                            ->viewData([
                                'carpetaId'     => $carpeta->id,
                                'carpetaSlug'   => $carpeta->slug,
                                'permisoEditar' => $permisoEditar,
                            ]),
                    ]);
            })
            ->toArray();
    }
}
