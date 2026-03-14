<?php
namespace App\Filament\Resources\Juridico\SeguimientoNotarias\Pages;
use App\Filament\Resources\Juridico\SeguimientoNotarias\SeguimientoNotariaResource;
use Filament\Resources\Pages\CreateRecord;
class CreateSeguimientoNotaria extends CreateRecord {
    protected static string $resource = SeguimientoNotariaResource::class;
    protected function getRedirectUrl(): string {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
