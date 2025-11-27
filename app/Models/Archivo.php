<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Archivo extends Model
{
    use SoftDeletes;

    protected $table = 'archivos';

    protected $fillable = [
        'entidad_type', // Cliente, Propiedad, Expediente
        'entidad_id',
        'categoria', // INE, SENTENCIA, FOTO
        'ruta_archivo',
        'nombre_original',
        'mime_type',
        'created_by', // En este caso actúa como "subido_por"
    ];

    // --- RELACIONES ---

    /**
     * ¿A qué pertenece este archivo?
     */
    public function entidad(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Usuario que subió el archivo.
     */
    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
