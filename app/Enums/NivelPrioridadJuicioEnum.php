<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum NivelPrioridadJuicioEnum: string implements HasColor, HasIcon, HasLabel
{
    case PRIORIDAD_ALTA      = 'PRIORIDAD_ALTA';
    case MEDIA               = 'MEDIA';
    case BAJA                = 'BAJA';
    case REVISADO            = 'REVISADO';
    case SIN_REVISAR         = 'SIN_REVISAR';
    case NULO_NO_LITIGABLE   = 'NULO_NO_LITIGABLE';

    public function getLabel(): string
    {
        return match ($this) {
            self::PRIORIDAD_ALTA    => 'Prioridad Alta',
            self::MEDIA             => 'Prioridad Media',
            self::BAJA              => 'Prioridad Baja',
            self::REVISADO          => 'Revisado',
            self::SIN_REVISAR       => 'Sin Revisar',
            self::NULO_NO_LITIGABLE => 'Nulo / No Litigable',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PRIORIDAD_ALTA    => 'danger',
            self::MEDIA             => 'warning',
            self::BAJA              => 'info',
            self::REVISADO          => 'success',
            self::SIN_REVISAR       => 'gray',
            self::NULO_NO_LITIGABLE => 'gray',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PRIORIDAD_ALTA    => 'heroicon-m-fire',
            self::MEDIA             => 'heroicon-m-exclamation-triangle',
            self::BAJA              => 'heroicon-m-arrow-down-circle',
            self::REVISADO          => 'heroicon-m-check-circle',
            self::SIN_REVISAR       => 'heroicon-m-clock',
            self::NULO_NO_LITIGABLE => 'heroicon-m-x-circle',
        };
    }
}
