<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AprobacionPrecio extends Model
{
    protected $table = 'aprobaciones_precio';

    protected $fillable = [
        'propiedad_id',
        'cotizacion_id',
        'precio_propuesto',
        'tipo_aprobador',
        'estatus',
        'precio_sugerido_alternativo',
        'comentarios',
        'aprobador_id',
        'fecha_respuesta',
    ];

    protected $casts = [
        'precio_propuesto' => 'decimal:2',
        'precio_sugerido_alternativo' => 'decimal:2',
        'fecha_respuesta' => 'datetime',
    ];

    // --- RELACIONES ---

    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Propiedad::class);
    }

    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class);
    }

    public function aprobador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobador_id');
    }

    // --- SCOPES ---

    public function scopePendientes($query)
    {
        return $query->where('estatus', 'PENDIENTE');
    }

    public function scopeAprobadas($query)
    {
        return $query->where('estatus', 'APROBADO');
    }

    public function scopeRechazadas($query)
    {
        return $query->where('estatus', 'RECHAZADO');
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_aprobador', $tipo);
    }

    public function scopePorCotizacion($query, int $cotizacionId)
    {
        return $query->where('cotizacion_id', $cotizacionId);
    }

    // --- MÉTODOS HELPER ---

    /**
     * Aprobar el precio
     */
    public function aprobar(User $aprobador, ?string $comentarios = null): void
    {
        $this->update([
            'estatus' => 'APROBADO',
            'aprobador_id' => $aprobador->id,
            'comentarios' => $comentarios,
            'fecha_respuesta' => now(),
        ]);
    }

    /**
     * Rechazar el precio con sugerencia alternativa
     */
    public function rechazar(
        User $aprobador,
        ?float $precioAlternativo = null,
        ?string $comentarios = null
    ): void {
        $this->update([
            'estatus' => 'RECHAZADO',
            'precio_sugerido_alternativo' => $precioAlternativo,
            'aprobador_id' => $aprobador->id,
            'comentarios' => $comentarios,
            'fecha_respuesta' => now(),
        ]);
    }

    /**
     * Verificar si está pendiente
     */
    public function isPendiente(): bool
    {
        return $this->estatus === 'PENDIENTE';
    }

    /**
     * Verificar si está aprobada
     */
    public function isAprobada(): bool
    {
        return $this->estatus === 'APROBADO';
    }

    /**
     * Verificar si está rechazada
     */
    public function isRechazada(): bool
    {
        return $this->estatus === 'RECHAZADO';
    }

    /**
     * Obtener badge color según estatus
     */
    public function getBadgeColorAttribute(): string
    {
        return match ($this->estatus) {
            'PENDIENTE' => 'warning',
            'APROBADO' => 'success',
            'RECHAZADO' => 'danger',
            default => 'gray',
        };
    }
}
