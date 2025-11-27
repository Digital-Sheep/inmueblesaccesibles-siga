<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatEtapaProcesal extends Model
{
    use SoftDeletes;

    protected $table = 'cat_etapas_procesales';

    protected $fillable = [
        'nombre',
        'tipo_juicio_id',
        'dias_termino_legal',
        'orden',
        'created_by',
        'updated_by',
    ];

    // --- RELACIONES ---

    public function tipoJuicio(): BelongsTo
    {
        return $this->belongsTo(CatTipoJuicio::class, 'tipo_juicio_id');
    }

    /**
     * Esta etapa se usa en muchos seguimientos reales.
     */
    public function seguimientos(): HasMany
    {
        return $this->hasMany(SeguimientoJuridico::class, 'etapa_id');
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
