<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatEstado extends Model
{
    protected $table = 'cat_estados';

    protected $fillable = [
        'nombre',
        'abreviatura',
        'created_by',
        'updated_by',
    ];

    /**
     * Relación: Un Estado tiene muchos Municipios.
     */
    public function municipios(): HasMany
    {
        return $this->hasMany(CatMunicipio::class, 'estado_id');
    }

    /**
     * Auditoría: Usuario que creó el registro.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Auditoría: Usuario que actualizó el registro por última vez.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
