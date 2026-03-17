<?php
namespace App\Filament\Resources\Juridico\SeguimientoNotarias\Pages;
use App\Filament\Resources\Juridico\SeguimientoNotarias\SeguimientoNotariaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSeguimientoNotarias extends ListRecords {
    protected static string $resource = SeguimientoNotariaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label('Nuevo seguimiento'),
        ];
    }
}
