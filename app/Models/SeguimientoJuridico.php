<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeguimientoJuridico extends Model
{
    protected $table = 'seguimientos_juridicos';

    protected $fillable = [
        'expediente_id',
        'etapa_id', // Catálogo de Etapas
        'fecha_inicio',
        'fecha_vencimiento',
        'estatus_semaforo',
        'observaciones',
        'documento_evidencia_url',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_vencimiento' => 'date',
    ];

    public function expediente(): BelongsTo
    {
        return $this->belongsTo(ExpedienteJuridico::class, 'expediente_id');
    }

    public function etapa(): BelongsTo
    {
        return $this->belongsTo(CatEtapaProcesal::class, 'etapa_id');
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
