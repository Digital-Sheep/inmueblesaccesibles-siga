<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Archivo extends Model
{
    use SoftDeletes;

    protected $table = 'archivos';

    protected $fillable = [
        'entidad_type',
        'entidad_id',
        'categoria',
        'cat_carpeta_id',
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
     * Carpeta jurídica a la que pertenece este archivo.
     * NULL para archivos de otros módulos (propiedades, clientes, etc.)
     */
    public function catCarpeta(): BelongsTo
    {
        return $this->belongsTo(CatCarpetaJuridica::class, 'cat_carpeta_id');
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

    /**
     * URL temporal firmada para descargar desde disco private.
     * Válida 30 minutos.
     */
    public function getUrlTemporalAttribute(): ?string
    {
        if (! $this->ruta_archivo) {
            return null;
        }

        return Storage::disk('private')->temporaryUrl(
            $this->ruta_archivo,
            now()->addMinutes(30)
        );
    }

    /**
     * Solo el nombre del archivo para mostrarlo en la UI.
     */
    public function getNombreCortoAttribute(): string
    {
        return basename($this->nombre_original ?? $this->ruta_archivo);
    }

    /**
     * Peso legible para humanos (KB / MB).
     */
    public function getPesoLegibleAttribute(): string
    {
        if (! $this->peso_kb) {
            return '—';
        }

        return $this->peso_kb >= 1024
            ? round($this->peso_kb / 1024, 1) . ' MB'
            : $this->peso_kb . ' KB';
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
