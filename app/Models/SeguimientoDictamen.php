<?php

namespace App\Models;

use App\Enums\EstatusDictamenEnum;
use App\Enums\ResultadoDictamenEnum;
use App\Enums\TipoProcesoDictamenEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SeguimientoDictamen extends Model
{
    use SoftDeletes;

    protected $table = 'seguimientos_dictamen';

    protected $fillable = [
        'propiedad_id',
        'cliente_id',
        'numero_credito',
        'tipo_proceso',
        'solicitante_id',
        'administradora_id',
        'numero_juicio',
        'numero_expediente',
        'jurisdiccion',
        'via_procesal',
        'direccion',
        'estado_garantia',
        'dictamen_juridico_archivo',
        'dictamen_juridico_resultado',
        'disponibilidad',
        'carta_intencion_archivo',
        'tiene_cofinavit',
        'valor_cofinavit',
        'dictamen_registral_archivo',
        'dictamen_registral_resultado',
        'valor_garantia',
        'valor_catastral',
        'valor_comercial_aproximado',
        'valor_venta',
        'valor_sin_remodelacion',
        'estatus',
        'etapa_actual',
        'notas',
        'activo',
        'ultima_actuacion_at',
    ];

    protected $casts = [
        'tipo_proceso'                 => TipoProcesoDictamenEnum::class,
        'dictamen_juridico_resultado'  => ResultadoDictamenEnum::class,
        'dictamen_registral_resultado' => ResultadoDictamenEnum::class,
        'estatus'                      => EstatusDictamenEnum::class,
        'tiene_cofinavit'              => 'boolean',
        'activo'                       => 'boolean',
        'ultima_actuacion_at'          => 'datetime',
        'valor_cofinavit'              => 'decimal:2',
        'valor_garantia'               => 'decimal:2',
        'valor_catastral'              => 'decimal:2',
        'valor_comercial_aproximado'   => 'decimal:2',
        'valor_venta'                  => 'decimal:2',
        'valor_sin_remodelacion'       => 'decimal:2',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Propiedad::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    public function catAdministradora(): BelongsTo
    {
        return $this->belongsTo(CatAdministradora::class, 'administradora_id');
    }

    public function actuaciones(): HasMany
    {
        return $this->hasMany(ActuacionDictamen::class)
                    ->orderByDesc('fecha_actuacion');
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    /**
     * Título descriptivo para breadcrumb.
     */
    public function getTituloAttribute(): string
    {
        $tipo   = $this->tipo_proceso instanceof TipoProcesoDictamenEnum ? $this->tipo_proceso->getLabel() : null;
        $partes = array_filter([$tipo, $this->numero_credito ?? $this->numero_expediente]);

        return ! empty($partes)
            ? implode(' — ', $partes)
            : "Dictamen #{$this->id}";
    }

    public function getDiasSinActuacionAttribute(): int
    {
        return (int) $this->ultima_actuacion_at->diffInDays(now());
    }

    public function getEstaRezagadoAttribute(): bool
    {
        return $this->ultima_actuacion_at->diffInDays(now()) > 7;
    }

    /**
     * Indica si el dictamen está completado (ambos resultados positivos).
     * Se usa en el Observer para bloquear nuevas actuaciones.
     */
    public function getEsCompletadoAttribute(): bool
    {
        return $this->dictamen_juridico_resultado === ResultadoDictamenEnum::POSITIVO
            && $this->dictamen_registral_resultado === ResultadoDictamenEnum::POSITIVO;
    }
}
