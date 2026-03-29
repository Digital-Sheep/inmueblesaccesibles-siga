<?php

namespace App\Models;

use App\Enums\SedeJuicioEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SeguimientoNotaria extends Model
{
    use SoftDeletes;

    protected $table = 'seguimientos_notaria';

    protected $fillable = [
        'propiedad_id',
        'numero_credito',
        'id_garantia',
        'nombre_cliente',
        'notario',
        'numero_escritura',
        'fecha_escritura',
        'sede',
        'administradora',
        'hay_cesion_derechos',
        'cedente',
        'cesionario',
        'etapa_actual',
        'notas_director',
        'activo',
        'ultima_actuacion_at',
    ];

    protected $casts = [
        'sede'                => SedeJuicioEnum::class,
        'fecha_escritura'     => 'date',
        'hay_cesion_derechos' => 'boolean',
        'activo'              => 'boolean',
        'ultima_actuacion_at' => 'datetime',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Propiedad::class);
    }

    public function actuaciones(): HasMany
    {
        return $this->hasMany(ActuacionNotaria::class)
            ->orderByDesc('fecha_actuacion');
    }

    /**
     * Todos los archivos adjuntos a esta notaría.
     */
    public function archivos(): MorphMany
    {
        return $this->morphMany(Archivo::class, 'entidad')
            ->whereNotNull('cat_carpeta_id')
            ->orderByDesc('created_at');
    }

    /**
     * Documentos filtrados por carpeta específica.
     */
    public function archivosDeCarpeta(int $carpetaId): MorphMany
    {
        return $this->morphMany(Archivo::class, 'entidad')
            ->where('cat_carpeta_id', $carpetaId)
            ->orderByDesc('created_at');
    }

    /**
     * Path base para almacenamiento de documentos de esta notaría.
     *
     * Patrón: juridico/notarias/{identificador}
     */
    public function getPathBaseAttribute(): string
    {
        $identificador = $this->id_garantia
            ? $this->id_garantia
            : 'notaria-' . $this->id;

        return 'juridico/notarias/' . $identificador;
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    public function getTituloAttribute(): string
    {
        $sede   = $this->sede instanceof SedeJuicioEnum ? $this->sede->getLabel() : null;
        $partes = array_filter([$sede, $this->nombre_cliente ?? $this->id_garantia ?? $this->numero_credito]);

        return ! empty($partes)
            ? implode(' — ', $partes)
            : "Notaría #{$this->id}";
    }

    public function getDiasSinActuacionAttribute(): ?int
    {
        if (! $this->ultima_actuacion_at) {
            return null;
        }

        return (int) $this->ultima_actuacion_at->diffInDays(now());
    }

    public function getEstaRezagadoAttribute(): bool
    {
        if (! $this->ultima_actuacion_at) {
            return true;
        }

        return $this->ultima_actuacion_at->diffInDays(now()) > 7;
    }

    public function getNombreAdministradoraAttribute(): ?string
    {
        return $this->catAdministradora?->nombre ?? $this->administradora;
    }

    public function catAdministradora(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\CatAdministradora::class, 'administradora_id');
    }
}
