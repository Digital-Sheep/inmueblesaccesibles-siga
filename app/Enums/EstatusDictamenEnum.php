<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EstatusDictamenEnum: string implements HasColor, HasIcon, HasLabel
{
    case ACTIVO     = 'ACTIVO';
    case COMPLETADO = 'COMPLETADO';

    public function getLabel(): string
    {
        return match ($this) {
            self::ACTIVO     => 'Activo',
            self::COMPLETADO => 'Completado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ACTIVO     => 'primary',
            self::COMPLETADO => 'success',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::ACTIVO     => 'heroicon-m-arrow-path',
            self::COMPLETADO => 'heroicon-m-check-badge',
        };
    }
}
