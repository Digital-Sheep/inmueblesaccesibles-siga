<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EsquemaPagoDetalle extends Model
{
    protected $table = 'esquemas_pago_detalles';

    protected $fillable = [
        'esquema_pago_id',
        'numero_pago',
        'descripcion',
        'porcentaje',
        'monto_calculado',
        'orden',
    ];

    protected $casts = [
        'numero_pago' => 'integer',
        'porcentaje' => 'decimal:2',
        'monto_calculado' => 'decimal:2',
        'orden' => 'integer',
    ];

    // --- RELACIONES ---

    public function esquemaPago(): BelongsTo
    {
        return $this->belongsTo(EsquemaPago::class);
    }

    // --- MÉTODOS HELPER ---

    /**
     * Calcular el monto según el precio de venta y si es el último pago
     */
    public function calcularMonto(float $precioVenta, bool $esUltimoPago, float $apartado): float
    {
        $monto = ($precioVenta * $this->porcentaje) / 100;

        if ($esUltimoPago) {
            $monto -= $apartado;
        }

        return round($monto, 2);
    }

    /**
     * Obtener el label formateado para el UI
     */
    public function getLabelAttribute(): string
    {
        return sprintf(
            'Pago %d: %s (%s%%)',
            $this->numero_pago,
            $this->descripcion,
            number_format($this->porcentaje, 2)
        );
    }

    /**
     * Obtener el monto formateado
     */
    public function getMontoFormateadoAttribute(): string
    {
        if (!$this->monto_calculado) {
            return 'Pendiente de calcular';
        }

        return '$' . number_format($this->monto_calculado, 2);
    }
}
