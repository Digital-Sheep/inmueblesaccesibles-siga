<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

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
     * Aprobar la aprobación
     */
    public function aprobar(?int $userId = null, ?string $comentarios = null): void
    {
        $user = Auth::user();

        $this->update([
            'estatus' => 'APROBADO',
            'aprobador_id' => $userId ?? $user->id,
            'fecha_respuesta' => now(),
            'comentarios' => $comentarios,
        ]);
    }

    /**
     * Rechazar la aprobación con precio sugerido alternativo
     */
    public function rechazar(
        ?int $userId = null,
        ?string $comentarios = null,
        ?float $precioSugeridoAlternativo = null
    ): void {
        $user = Auth::user();

        $this->update([
            'estatus' => 'RECHAZADO',
            'aprobador_id' => $userId ?? $user->id,
            'fecha_respuesta' => now(),
            'comentarios' => $comentarios,
            'precio_sugerido_alternativo' => $precioSugeridoAlternativo,
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
     * Obtener color del badge según el estatus
     */
    public function getBadgeColorAttribute(): string
    {
        return match ($this->estatus) {
            'APROBADO' => 'success',
            'RECHAZADO' => 'danger',
            'PENDIENTE' => 'warning',
            default => 'gray',
        };
    }
}
