<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SolicitudContrato extends Model
{
    use SoftDeletes;

    protected $table = 'solicitudes_contrato';

    protected $fillable = [
        'proceso_venta_id',
        'tipo_contrato', // APARTADO, PRESTACION_SERVICIOS
        'estatus', // SOLICITADO, BORRADOR, FIRMADO
        'fecha_solicitud',
        'fecha_firma_programada',
        'elaborado_por_id',
        'aprobado_por_id',
        'notas_legales',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_solicitud' => 'datetime',
        'fecha_firma_programada' => 'datetime',
    ];

    // --- RELACIONES ---

    public function procesoVenta(): BelongsTo
    {
        return $this->belongsTo(ProcesoVenta::class, 'proceso_venta_id');
    }

    public function elaboradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'elaborado_por_id');
    }

    public function aprobadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_por_id');
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
