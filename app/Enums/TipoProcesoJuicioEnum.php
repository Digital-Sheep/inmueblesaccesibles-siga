<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TipoProcesoJuicioEnum: string implements HasColor, HasLabel
{
    case VENTA     = 'VENTA';
    case CAMBIO    = 'CAMBIO';
    case INVERSION = 'INVERSION';

    public function getLabel(): string
    {
        return match ($this) {
            self::VENTA     => 'Venta',
            self::CAMBIO    => 'Cambio',
            self::INVERSION => 'Inversión',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::VENTA     => 'primary',
            self::CAMBIO    => 'info',
            self::INVERSION => 'warning',
        };
    }
}
