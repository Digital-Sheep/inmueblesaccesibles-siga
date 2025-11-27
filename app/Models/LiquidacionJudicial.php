<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LiquidacionJudicial extends Model
{
    use SoftDeletes;

    protected $table = 'liquidaciones_judiciales';

    protected $fillable = [
        'expediente_id',
        'suerte_principal',
        'tasa_interes_anual',
        'fecha_inicio_mora',
        'fecha_corte',
        'total_calculado',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_inicio_mora' => 'date',
        'fecha_corte' => 'date',
        'suerte_principal' => 'decimal:2',
        'total_calculado' => 'decimal:2',
    ];

    public function expediente(): BelongsTo
    {
        return $this->belongsTo(ExpedienteJuridico::class, 'expediente_id');
    }

    // Auditoría...
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
