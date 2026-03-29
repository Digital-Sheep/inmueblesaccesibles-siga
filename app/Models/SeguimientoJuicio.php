<?php

namespace App\Models;

use App\Enums\NivelPrioridadJuicioEnum;
use App\Enums\SedeJuicioEnum;
use App\Enums\TipoProcesoJuicioEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SeguimientoJuicio extends Model
{
    use SoftDeletes;

    protected $table = 'seguimientos_juicio';

    protected $fillable = [
        'propiedad_id',
        'numero_credito',
        'id_garantia',
        'nombre_cliente',
        'administradora',        // deprecated — mantener para datos históricos
        'administradora_id',     // nuevo — FK a cat_administradoras
        'domicilio',
        'sede',
        'nivel_prioridad',
        'tipo_proceso',
        'actor',
        'demandado',
        'numero_expediente',
        'juzgado',
        'distrito_judicial',
        'tipo_juicio_materia',
        'via_procesal',
        'hay_cesion_derechos',
        'cedente',
        'cesionario',
        'etapa_actual',
        'estrategia_juridica',
        'estrategia_juridica_archivo',
        'notas_director',
        'sin_demanda',           // deprecated — usar con_demanda
        'con_demanda',           // nuevo
        'activo',
        'ultima_actuacion_at',
    ];

    protected $casts = [
        'nivel_prioridad'     => NivelPrioridadJuicioEnum::class,
        'tipo_proceso'        => TipoProcesoJuicioEnum::class,
        'sede'                => SedeJuicioEnum::class,
        'hay_cesion_derechos' => 'boolean',
        'sin_demanda'         => 'boolean',
        'con_demanda'         => 'boolean',
        'activo'              => 'boolean',
        'ultima_actuacion_at' => 'datetime',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Propiedad::class);
    }

    /**
     * Administradora del catálogo (nuevo campo).
     * Null-safe: registros históricos pueden no tener FK todavía.
     */
    public function catAdministradora(): BelongsTo
    {
        return $this->belongsTo(CatAdministradora::class, 'administradora_id');
    }

    /**
     * Abogados asignados — usuarios con rol 'abogado'.
     * Máximo 3 por juicio, validado a nivel de aplicación.
     */
    public function abogados(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'abogado_seguimiento_juicio')
            ->withPivot('orden')
            ->orderByPivot('orden');
    }

    /**
     * Actuaciones semanales, más reciente primero.
     */
    public function actuaciones(): HasMany
    {
        return $this->hasMany(ActuacionJuicio::class)
            ->orderByDesc('fecha_actuacion');
    }

    /**
     * Todos los archivos adjuntos a este juicio.
     * Usa el morph 'entidad' (entidad_type / entidad_id) ya existente en la tabla archivos.
     */
    public function archivos(): MorphMany
    {
        return $this->morphMany(Archivo::class, 'entidad')
            ->whereNotNull('cat_carpeta_id')  // solo documentos jurídicos categorizados
            ->orderByDesc('created_at');
    }

    /**
     * Documentos filtrados por carpeta específica.
     * Usado por DocumentosCarpetaComponent para cargar solo los de un tab.
     *
     * @param int $carpetaId ID de CatCarpetaJuridica
     */
    public function archivosDeCarpeta(int $carpetaId): MorphMany
    {
        return $this->morphMany(Archivo::class, 'entidad')
            ->where('cat_carpeta_id', $carpetaId)
            ->orderByDesc('created_at');
    }

    /**
     * Helper para construir el path base de almacenamiento de este juicio.
     * Usado por DocumentosCarpetaComponent al subir archivos.
     *
     * Patrón: juridico/juicios/{identificador}
     * Donde identificador = id_garantia si existe, sino "juicio-{id}"
     */
    public function getPathBaseAttribute(): string
    {
        $identificador = $this->id_garantia
            ? $this->id_garantia
            : 'juicio-' . $this->id;

        return 'juridico/juicios/' . $identificador;
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    /**
     * Título descriptivo para breadcrumb: "[Sede] — [Cliente]"
     */
    public function getTituloAttribute(): string
    {
        $sede   = $this->sede instanceof SedeJuicioEnum ? $this->sede->getLabel() : null;
        $partes = array_filter([$sede, $this->nombre_cliente ?? $this->id_garantia ?? $this->numero_credito]);

        return ! empty($partes)
            ? implode(' — ', $partes)
            : "Juicio #{$this->id}";
    }

    /**
     * Nombre de la administradora — prioriza el catálogo, fallback al texto libre histórico.
     */
    public function getNombreAdministradoraAttribute(): ?string
    {
        return $this->catAdministradora?->nombre ?? $this->administradora;
    }

    public function getDiasSinActuacionAttribute(): int
    {
        return (int) $this->ultima_actuacion_at->diffInDays(now());
    }

    public function getEstaRezagadoAttribute(): bool
    {
        return $this->ultima_actuacion_at->diffInDays(now()) > 7;
    }

    public function getUltimaActuacionAttribute(): ?ActuacionJuicio
    {
        return $this->actuaciones->first();
    }
}
