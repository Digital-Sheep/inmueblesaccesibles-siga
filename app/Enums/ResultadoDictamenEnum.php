<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ResultadoDictamenEnum: string implements HasColor, HasIcon, HasLabel
{
    case POSITIVO   = 'POSITIVO';
    case NEGATIVO   = 'NEGATIVO';
    case EN_ESPERA  = 'EN_ESPERA';

    public function getLabel(): string
    {
        return match ($this) {
            self::POSITIVO  => 'Positivo',
            self::NEGATIVO  => 'Negativo',
            self::EN_ESPERA => 'En Espera',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::POSITIVO  => 'success',
            self::NEGATIVO  => 'danger',
            self::EN_ESPERA => 'warning',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::POSITIVO  => 'heroicon-m-check-circle',
            self::NEGATIVO  => 'heroicon-m-x-circle',
            self::EN_ESPERA => 'heroicon-m-clock',
        };
    }
}
