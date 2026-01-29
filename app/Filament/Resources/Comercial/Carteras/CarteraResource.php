<?php

namespace App\Filament\Resources\Comercial\Carteras;

use App\Filament\Clusters\Comercial\ComercialCluster;
use App\Filament\Resources\Comercial\Carteras\Pages\CreateCartera;
use App\Filament\Resources\Comercial\Carteras\Pages\EditCartera;
use App\Filament\Resources\Comercial\Carteras\Pages\ListCarteras;
use App\Filament\Resources\Comercial\Carteras\Pages\ViewCartera;
use App\Filament\Resources\Comercial\Carteras\Schemas\CarteraForm;
use App\Filament\Resources\Comercial\Carteras\Schemas\CarteraInfolist;
use App\Filament\Resources\Comercial\Carteras\Tables\CarterasTable;
use App\Models\Cartera;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class CarteraResource extends Resource
{
    protected static ?string $model = Cartera::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentArrowUp;

    protected static ?string $modelLabel = 'Cartera';
    protected static ?string $pluralModelLabel = 'Carga de Carteras';
    protected static ?string $navigationLabel = 'Cargar Cartera';

    protected static ?string $cluster = ComercialCluster::class;

    protected static ?int $navigationSort = 7;

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->can('menu_cartera');
    }

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->can('carteras_ver');
    }

    public static function form(Schema $schema): Schema
    {
        return CarteraForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CarteraInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CarterasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCarteras::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPlantillaCSVAction(): Action
    {
        return Action::make('descargar_plantilla')
            ->label('Descargar Plantilla CSV')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('info')
            ->outlined()
            ->action(function () {
                $csv = [
                    [
                        'Codigo de la cartera',
                        'Número de crédito',
                        'Estado',
                        'Municipio',
                        'Fraccionamiento',
                        'Calle',
                        'Segunda dirección',
                        'Número exterior',
                        'Número interior',
                        'Código postal',
                        'Etapa judicial',
                        'Segunda etapa judicial',
                        'Fecha última etapa judicial',
                        'Tipo vivienda',
                        'M2 construcción',
                        'Tipo inmueble',
                        'Avalúo según administradora',
                        'Precio de lista',
                        'COFINAVIT',
                        'Nombre acreditado',
                    ],
                    [
                        'CARTERA-2025-01',
                        '123456789',
                        'Nuevo León',
                        'Monterrey',
                        'Lomas del Valle',
                        'Av. Principal',
                        'Col. Centro',
                        '123',
                        'A',
                        '64000',
                        'Sentencia Firme',
                        'Adjudicado',
                        '31/12/2024',
                        'Casa',
                        '120.5',
                        'Residencial',
                        '1500000',
                        '1200000',
                        '0',
                        'Juan Pérez García',
                    ],
                ];

                $filename = 'plantilla_propiedades_' . now()->format('Ymd_His') . '.csv';
                $handle = fopen('php://temp', 'r+');

                fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

                foreach ($csv as $row) {
                    fputcsv($handle, $row);
                }

                rewind($handle);
                $content = stream_get_contents($handle);
                fclose($handle);

                return response()->streamDownload(
                    fn() => print($content),
                    $filename,
                    [
                        'Content-Type' => 'text/csv; charset=UTF-8',
                        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    ]
                );
            });
    }
}
