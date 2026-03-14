<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum NivelPrioridadJuicioEnum: string implements HasColor, HasIcon, HasLabel
{
    case PRIORIDAD_ALTA      = 'PRIORIDAD_ALTA';
    case REVISADO            = 'REVISADO';
    case SIN_REVISAR         = 'SIN_REVISAR';
    case NULO_NO_LITIGABLE   = 'NULO_NO_LITIGABLE';

    public function getLabel(): string
    {
        return match ($this) {
            self::PRIORIDAD_ALTA    => 'Prioridad Alta',
            self::REVISADO          => 'Revisado',
            self::SIN_REVISAR       => 'Sin Revisar',
            self::NULO_NO_LITIGABLE => 'Nulo / No Litigable',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PRIORIDAD_ALTA    => 'danger',
            self::REVISADO          => 'success',
            self::SIN_REVISAR       => 'warning',
            self::NULO_NO_LITIGABLE => 'gray',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PRIORIDAD_ALTA    => 'heroicon-m-fire',
            self::REVISADO          => 'heroicon-m-check-circle',
            self::SIN_REVISAR       => 'heroicon-m-clock',
            self::NULO_NO_LITIGABLE => 'heroicon-m-x-circle',
        };
    }
}
