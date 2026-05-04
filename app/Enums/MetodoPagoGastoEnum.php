<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum MetodoPagoGastoEnum: string implements HasColor, HasIcon, HasLabel
{
    case EFECTIVO      = 'EFECTIVO';
    case TRANSFERENCIA = 'TRANSFERENCIA';
    case CHEQUE        = 'CHEQUE';

    public function getLabel(): string
    {
        return match ($this) {
            self::EFECTIVO      => 'Efectivo',
            self::TRANSFERENCIA => 'Transferencia',
            self::CHEQUE        => 'Cheque',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::EFECTIVO      => 'success',
            self::TRANSFERENCIA => 'info',
            self::CHEQUE        => 'warning',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::EFECTIVO      => 'heroicon-o-banknotes',
            self::TRANSFERENCIA => 'heroicon-o-arrow-path',
            self::CHEQUE        => 'heroicon-o-document',
        };
    }
}
