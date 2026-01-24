<?php

namespace App\Filament\Resources\Comercial\Clientes\Pages;

use App\Filament\Resources\Comercial\Clientes\ClienteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListClientes extends ListRecords
{
    protected static string $resource = ClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nuevo cliente')
                ->modalWidth('4xl')
                ->createAnother(false)
                ->visible(
                    function(): bool {
                        /** @var \App\Models\User $user */
                        $user = Auth::user();

                        return $user->can('clientes_crear');
                    }
                ),
        ];
    }
}
