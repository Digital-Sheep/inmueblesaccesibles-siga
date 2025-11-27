<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dictamen extends Model
{
    use SoftDeletes;

    protected $table = 'dictamenes';

    protected $fillable = [
        'tipo_solicitud',
        'origen_solicitud',
        'proceso_venta_id',
        'propiedad_id',
        'cliente_id',
        'usuario_solicitante_id',

        // Datos técnicos del Excel
        'direccion_completa',
        'numero_credito',
        'numero_credito_anterior',
        'nombre_proveedor',
        'tipo_persona_proveedor',
        'es_dueno_real',
        'tiene_posesion',
        'fecha_ultimo_pago_deudor',
        'juzgado_origen',
        'tipo_juicio_preliminar',

        // Resolución
        'estatus',
        'resultado_final',
        'nomenclatura_generada',

        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'es_dueno_real' => 'boolean',
        'tiene_posesion' => 'boolean',
        'fecha_ultimo_pago_deudor' => 'date',
    ];

    // --- RELACIONES ---

    public function procesoVenta(): BelongsTo
    {
        return $this->belongsTo(ProcesoVenta::class, 'proceso_venta_id');
    }

    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Propiedad::class, 'propiedad_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_solicitante_id');
    }

    /**
     * Si el dictamen fue positivo, genera un Expediente.
     */
    public function expediente(): HasOne
    {
        return $this->hasOne(ExpedienteJuridico::class, 'dictamen_origen_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
