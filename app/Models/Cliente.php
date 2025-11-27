<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use SoftDeletes;

    protected $table = 'clientes';

    // OJO: No incluimos 'nombre_completo_virtual' aquí porque no se guarda, se calcula.
    protected $fillable = [
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'celular',
        'rfc',
        'curp',
        'ocupacion',
        'estado_civil',
        'direccion_fiscal', // Calle, número, etc.
        'direccion_colonia',
        'codigo_postal',
        'estado_id',
        'municipio_id',
        'sucursal_id',
        'usuario_responsable_id',
        'created_by',
        'updated_by',
    ];

    // --- RELACIONES ---

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(CatSucursal::class, 'sucursal_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_responsable_id');
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(CatEstado::class, 'estado_id');
    }

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(CatMunicipio::class, 'municipio_id');
    }

    /**
     * Un cliente también puede iniciar nuevos procesos de compra.
     */
    public function procesosVenta(): MorphMany
    {
        return $this->morphMany(ProcesoVenta::class, 'interesado');
    }

    public function expedientesJuridicos(): HasMany
    {
        return $this->hasMany(ExpedienteJuridico::class, 'cliente_id');
    }

    public function interacciones(): MorphMany
    {
        return $this->morphMany(Interaccion::class, 'entidad');
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
