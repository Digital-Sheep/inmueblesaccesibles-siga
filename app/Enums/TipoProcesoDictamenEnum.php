<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TipoProcesoDictamenEnum: string implements HasColor, HasLabel
{
    case VENTA     = 'VENTA';
    case INVERSION = 'INVERSION';

    public function getLabel(): string
    {
        return match ($this) {
            self::VENTA     => 'Venta',
            self::INVERSION => 'Inversión',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::VENTA     => 'primary',
            self::INVERSION => 'warning',
        };
    }
}
