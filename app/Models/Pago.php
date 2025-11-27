<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pago extends Model
{
    use SoftDeletes;

    protected $table = 'pagos';

    protected $fillable = [
        'concepto', // APARTADO, ENGANCHE, LIQUIDACION, ABONO
        'proceso_venta_id',
        'pagable_type', // Quién paga (Prospecto/Cliente)
        'pagable_id',
        'monto',
        'metodo_pago', // EFECTIVO, TRANSFERENCIA
        'comprobante_url',
        'estatus', // PENDIENTE, VALIDADO
        'validado_por_id',
        'fecha_validacion',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_validacion' => 'datetime',
    ];

    // --- RELACIONES ---

    public function procesoVenta(): BelongsTo
    {
        return $this->belongsTo(ProcesoVenta::class, 'proceso_venta_id');
    }

    /**
     * ¿Quién realizó el pago? (Polimórfico)
     */
    public function pagable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Usuario de Finanzas (GAD) que validó.
     */
    public function validadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validado_por_id');
    }

    // --- AUDITORÍA ---

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
