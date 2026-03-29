<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CatCarpetaJuridica extends Model
{
    protected $table = 'cat_carpetas_juridicas';

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'activo',
        'orden',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden'  => 'integer',
    ];

    // ── Boot — auto-generar slug si no se proporciona ──────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $carpeta) {
            if (empty($carpeta->slug)) {
                $carpeta->slug = Str::slug($carpeta->nombre);
            }
        });
    }

    // ── Relaciones ─────────────────────────────────────────────────────────────

    /**
     * Archivos clasificados bajo esta carpeta.
     * Aplica a cualquier modelo con morphMany archivos().
     */
    public function archivos(): HasMany
    {
        return $this->hasMany(Archivo::class, 'cat_carpeta_id');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    /**
     * Carpetas visibles en los tabs, ordenadas por configuración.
     */
    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activo', true)
            ->orderBy('orden')
            ->orderBy('nombre');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    /**
     * ¿Esta carpeta ya tiene archivos subidos?
     * Útil para bloquear cambio de slug en el futuro.
     */
    public function tieneArchivos(): bool
    {
        return $this->archivos()->exists();
    }
}
