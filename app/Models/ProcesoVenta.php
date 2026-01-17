<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcesoVenta extends Model
{
    use SoftDeletes;

    protected $table = 'procesos_venta';

    protected $fillable = [
        'interesado_type',
        'interesado_id',
        'propiedad_id',
        'vendedor_id',
        'estatus', // ACTIVO, APARTADO_POR_VALIDAR, DICTAMINADO_R2...
        'folio_apartado',
        'motivo_cancelacion',
        'created_by',
        'updated_by',
    ];

    // --- RELACIONES ---

    /**
     * Quién está comprando (Puede ser Prospecto o Cliente).
     */
    public function interesado(): MorphTo
    {
        return $this->morphTo();
    }

    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Propiedad::class, 'propiedad_id');
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    /**
     * Pagos asociados a este intento de venta (Apartado, Enganche).
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'proceso_venta_id');
    }

    /**
     * Solicitudes de Dictamen que nacieron de esta venta.
     */
    public function dictamenes(): HasMany
    {
        return $this->hasMany(Dictamen::class, 'proceso_venta_id');
    }

    /**
     * Si se formalizó, aquí está el expediente legal.
     */
    public function expedienteJuridico(): HasMany // Podría ser hasOne, pero hasMany es más seguro por si hay reintentos
    {
        return $this->hasMany(ExpedienteJuridico::class, 'proceso_venta_id');
    }

    /**
     * Solicitudes de contrato (Borradores).
     */
    public function solicitudesContrato(): HasMany
    {
        return $this->hasMany(SolicitudContrato::class, 'proceso_venta_id');
    }

    public function archivos(): MorphMany
    {
        return $this->morphMany(Archivo::class, 'entidad');
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
