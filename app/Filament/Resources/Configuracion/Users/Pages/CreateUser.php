<?php

namespace App\Filament\Resources\Configuracion\Users\Pages;

use App\Filament\Resources\Configuracion\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
