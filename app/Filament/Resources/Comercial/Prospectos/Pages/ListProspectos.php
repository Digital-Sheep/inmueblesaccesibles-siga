<?php

namespace App\Filament\Resources\Comercial\Prospectos\Pages;

use App\Filament\Resources\Comercial\Interaccions\InteraccionResource;
use App\Filament\Resources\Comercial\Prospectos\ProspectoResource;
use App\Models\Prospecto;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListProspectos extends ListRecords
{
    protected static string $resource = ProspectoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nuevo prospecto')
                ->modalHeading('Registrar nuevo prospecto')
                ->modalWidth('xl')
                ->createAnother(false)

                ->before(function (array $data, \Filament\Actions\CreateAction $action) {

                    $celularLimpio = preg_replace('/[^0-9]/', '', $data['celular']);

                    $duplicado = Prospecto::where('celular', 'LIKE', "%{$celularLimpio}%")
                        ->orWhere('email', $data['email'])
                        ->first();


                    if ($duplicado) {

                        $responsable = $duplicado->usuarioResponsable->name ?? 'Sin asignar';
                        $sucursal = $duplicado->sucursal->nombre ?? 'General';

                        Notification::make()
                            ->warning()
                            ->title('Prospecto ya registrado')
                            ->body("Este contacto ya pertenece a **{$responsable}** ({$sucursal}).\n\nPuedes apoyarlo a generar la cita y ganar el bono.")
                            ->persistent()
                            ->actions([
                                Action::make('venta_cruzada')
                                    ->label('📅 Agendar cita (Venta cruzada)')
                                    ->button()
                                    ->url(InteraccionResource::getUrl('index', [
                                        'tableFilters' => [
                                            'prospecto' => ['value' => $duplicado->id],
                                        ],
                                    ])),
                                Action::make('cerrar')
                                    ->label('Entendido')
                                    ->color('gray')
                                    ->close(),
                            ])
                            ->send();

                        $action->halt();
                    }
                })
                ->visible(ProspectoResource::canCreate()),
        ];
    }
}
