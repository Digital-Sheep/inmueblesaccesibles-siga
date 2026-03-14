<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EstatusAvanceEnum: string implements HasColor, HasLabel
{
    case SI                = 'SI';
    case NO                = 'NO';
    case EN_ESPERA_ACUERDO = 'EN_ESPERA_ACUERDO';

    public function getLabel(): string
    {
        return match ($this) {
            self::SI                => 'Sí',
            self::NO                => 'No',
            self::EN_ESPERA_ACUERDO => 'En Espera de Acuerdo',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::SI                => 'success',
            self::NO                => 'danger',
            self::EN_ESPERA_ACUERDO => 'warning',
        };
    }
}
