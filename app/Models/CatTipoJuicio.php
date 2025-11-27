<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatTipoJuicio extends Model
{
    use SoftDeletes;

    protected $table = 'cat_tipos_juicios';

    protected $fillable = [
        'nombre',
        'activo',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Un Tipo de Juicio tiene muchas Etapas Procesales específicas.
     */
    public function etapas(): HasMany
    {
        return $this->hasMany(CatEtapaProcesal::class, 'tipo_juicio_id');
    }

    /**
     * Un Tipo de Juicio se usa en muchos Juicios reales.
     */
    public function juicios(): HasMany
    {
        return $this->hasMany(Juicio::class, 'tipo_juicio_id');
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
