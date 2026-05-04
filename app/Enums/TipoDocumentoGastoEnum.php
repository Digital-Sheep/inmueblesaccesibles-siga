<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TipoDocumentoGastoEnum: string implements HasColor, HasIcon, HasLabel
{
    case COMPROBANTE = 'COMPROBANTE';
    case FACTURA     = 'FACTURA';
    case RECIBO      = 'RECIBO';

    public function getLabel(): string
    {
        return match ($this) {
            self::COMPROBANTE => 'Comprobante',
            self::FACTURA     => 'Factura',
            self::RECIBO      => 'Recibo',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::COMPROBANTE => 'info',
            self::FACTURA     => 'success',
            self::RECIBO      => 'warning',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::COMPROBANTE => 'heroicon-o-document-check',
            self::FACTURA     => 'heroicon-o-receipt-percent',
            self::RECIBO      => 'heroicon-o-document-text',
        };
    }
}
