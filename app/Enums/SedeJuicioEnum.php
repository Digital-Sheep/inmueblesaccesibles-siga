<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SedeJuicioEnum: string implements HasLabel
{
    case MAZATLAN    = 'MAZATLAN';
    case GUADALAJARA = 'GUADALAJARA';
    case LA_PAZ      = 'LA_PAZ';
    case CDMX        = 'CDMX';
    case CULIACAN    = 'CULIACAN';

    public function getLabel(): string
    {
        return match ($this) {
            self::MAZATLAN    => 'Mazatlán',
            self::GUADALAJARA => 'Guadalajara',
            self::LA_PAZ      => 'La Paz',
            self::CDMX        => 'CDMX',
            self::CULIACAN    => 'Culiacán',
        };
    }
}
