<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Archivo extends Model
{
    use SoftDeletes;

    protected $table = 'archivos';

    protected $fillable = [
        'entidad_type',
        'entidad_id',
        'categoria',
        'ruta_archivo',
        'nombre_original',
        'tipo_mime',
        'peso_kb',
        'descripcion',
        'subido_por_id',
        'created_by',
        'updated_by',
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

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Archivo $archivo) {
            $userId = Auth::id();

            if (! $archivo->subido_por_id) {
                $archivo->subido_por_id = $userId;
            }

            if (! $archivo->created_by) {
                $archivo->created_by = $userId;
            }
        });

        static::updating(function (Archivo $archivo) {
            $archivo->updated_by = Auth::id();
        });
    }
}
