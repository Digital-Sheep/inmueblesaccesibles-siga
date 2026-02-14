<?php

namespace App\Filament\Resources\Configuracion\EtapasProcesales;

use App\Filament\Clusters\Configuracion\ConfiguracionCluster;
use App\Filament\Resources\Configuracion\EtapasProcesales\Pages\ListEtapasProcesales;
use App\Filament\Resources\Configuracion\EtapasProcesales\Schemas\EtapaProcesalForm;
use App\Filament\Resources\Configuracion\EtapasProcesales\Tables\EtapasProcesalesTable;
use App\Models\CatEtapaProcesal;
use BackedEnum;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class EtapaProcesalResource extends Resource
{
    protected static ?string $model = CatEtapaProcesal::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Etapas procesales';

    protected static ?string $modelLabel = 'Etapa procesal';

    protected static ?string $pluralModelLabel = 'Etapas procesales';

    protected static ?string $cluster = ConfiguracionCluster::class;

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->can('configuracion_ver');
    }

    public static function canViewAny(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user->can('configuracion_ver');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema(EtapaProcesalForm::schema());
    }

    public static function table(Table $table): Table
    {
        return EtapasProcesalesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEtapasProcesales::route('/'),
        ];
    }
}
