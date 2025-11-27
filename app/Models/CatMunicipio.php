<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatMunicipio extends Model
{
    protected $table = 'cat_municipios';

    protected $fillable = [
        'estado_id',
        'nombre',
        'created_by',
        'updated_by',
    ];

    /**
     * Relación: Un Municipio pertenece a un Estado.
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(CatEstado::class, 'estado_id');
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
