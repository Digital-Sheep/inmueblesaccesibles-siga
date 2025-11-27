<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpedienteJuridico extends Model
{
    use SoftDeletes;

    protected $table = 'expedientes_juridicos';

    protected $fillable = [
        'codigo_expediente',
        'dictamen_origen_id',
        'proceso_venta_id',
        'propiedad_id',
        'cliente_id',
        'etapa_global',
        'abogado_responsable_id',
        'created_by',
        'updated_by',
    ];

    // --- RELACIONES ---

    public function dictamen(): BelongsTo
    {
        return $this->belongsTo(Dictamen::class, 'dictamen_origen_id');
    }

    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Propiedad::class, 'propiedad_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function abogado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'abogado_responsable_id');
    }

    /**
     * El juicio (datos del juzgado) asociado.
     */
    public function juicio(): HasOne
    {
        return $this->hasOne(Juicio::class, 'expediente_id');
    }

    /**
     * La bitácora de movimientos (El Excel de Seguimiento).
     */
    public function seguimientos(): HasMany
    {
        return $this->hasMany(SeguimientoJuridico::class, 'expediente_id');
    }

    /**
     * Cálculos financieros legales.
     */
    public function liquidacion(): HasOne
    {
        return $this->hasOne(LiquidacionJudicial::class, 'expediente_id');
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
