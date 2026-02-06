<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Propiedad extends Model
{
    use SoftDeletes;

    protected $table = 'propiedades';

    protected $fillable = [
        // Identificación
        'numero_credito',
        'cartera_id',
        'sucursal_id',
        'administradora_id',

        // Ubicación
        'direccion_completa',
        'calle',
        'numero_exterior',
        'numero_interior',
        'colonia',
        'fraccionamiento',
        'codigo_postal',
        'estado_id',
        'municipio_id',
        'estado_borrador', // Dato sucio del Excel
        'municipio_borrador', // Dato sucio del Excel

        // Geo
        'google_maps_link',
        'latitud',
        'longitud',

        // Características
        'tipo_vivienda',
        'tipo_inmueble',
        'terreno_m2',
        'construccion_m2',
        'habitaciones',
        'banos',
        'estacionamientos',

        // Datos Legales Reportados (Informativos)
        'etapa_judicial_reportada',
        'fecha_corte_judicial',
        'avaluo_banco',
        'cofinavit_monto',
        'nombre_acreditado',

        // Precios
        'precio_lista',
        'precio_venta_sugerido',
        'precio_minimo',

        // Estatus
        'estatus_comercial', // DISPONIBLE, APARTADA...
        'estatus_legal',     // R1, R2...
        'interesado_principal_type', // Quién la tiene apartada
        'interesado_principal_id',

        // Datos para tablas de cotización y aprobaciones
        'tamano_propiedad',
        'etapa_procesal_id',
        'precio_sin_remodelacion',
        'precio_venta_con_descuento',
        'porcentaje_descuento',
        'porcentaje_utilidad',
        'cotizacion_activa_id',
        'precio_custom_solicitado',
        'precio_custom_justificacion',
        'precio_custom_solicitante_id',
        'precio_custom_fecha',
        'precio_calculado',
        'precio_aprobado',
        'precio_fecha_aprobacion',
        'precio_requiere_decision_dge',

        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'terreno_m2' => 'decimal:2',
        'construccion_m2' => 'decimal:2',
        'habitaciones' => 'integer',
        'banos' => 'integer',
        'estacionamientos' => 'integer',
        'avaluo_banco' => 'decimal:2',
        'cofinavit_monto' => 'decimal:2',
        'precio_lista' => 'decimal:2',
        'precio_venta_sugerido' => 'decimal:2',
        'precio_minimo' => 'decimal:2',
        'precio_sin_remodelacion' => 'decimal:2',
        'precio_venta_con_descuento' => 'decimal:2',
        'porcentaje_descuento' => 'decimal:2',
        'porcentaje_utilidad' => 'decimal:2',
        'cotizacion_activa_id' => 'integer',
        'precio_custom_solicitado' => 'decimal:2',
        'precio_custom_fecha' => 'datetime',
        'precio_calculado' => 'boolean',
        'precio_aprobado' => 'boolean',
        'precio_fecha_aprobacion' => 'datetime',
        'precio_requiere_decision_dge' => 'boolean',
    ];

    /**
     * Atributo Virtual para mostrar en selectores de Filament.
     * Ej: "21700... - Av. Puerto Bajania 16..."
     */
    public function getNombreCortoAttribute(): string
    {
        $id = $this->numero_credito ?? 'S/N';
        $dir = Str::limit($this->direccion_completa, 40);
        return "{$id} - {$dir}";
    }

    // --- RELACIONES ---

    public function cartera(): BelongsTo
    {
        return $this->belongsTo(Cartera::class, 'cartera_id');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(CatSucursal::class, 'sucursal_id');
    }

    public function administradora(): BelongsTo
    {
        return $this->belongsTo(CatAdministradora::class, 'administradora_id');
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(CatEstado::class, 'estado_id');
    }

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(CatMunicipio::class, 'municipio_id');
    }

    public function etapaProcesal(): BelongsTo
    {
        return $this->belongsTo(CatEtapaProcesal::class, 'etapa_procesal_id');
    }

    public function cotizacionActiva(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_activa_id');
    }

    public function cotizaciones(): HasMany
    {
        return $this->hasMany(Cotizacion::class);
    }

    public function aprobacionesPrecio(): HasMany
    {
        return $this->hasMany(AprobacionPrecio::class);
    }

    public function precioCustomSolicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'precio_custom_solicitante_id');
    }

    /**
     * Quién la tiene apartada o comprada actualmente (Prospecto o Cliente).
     */
    public function interesadoPrincipal(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Historial de todos los intentos de venta (activos y cancelados).
     */
    public function procesosVenta(): HasMany
    {
        return $this->hasMany(ProcesoVenta::class, 'propiedad_id');
    }

    /**
     * Fotos de la galería.
     */
    public function fotos(): MorphMany
    {
        return $this->morphMany(Archivo::class, 'entidad')
            ->where('categoria', 'GALERIA');
    }

    public function archivos(): MorphMany
    {
        return $this->morphMany(Archivo::class, 'entidad');
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

    /**
     * Calcular tamaño automáticamente basado en m²
     */
    public function calcularTamanoAutomatico(): ?string
    {
        $m2Total = ($this->construccion_m2 ?? 0) + ($this->terreno_m2 ?? 0);

        if ($m2Total == 0) {
            return null;
        }

        return match (true) {
            $m2Total <= 80 => 'CHICA',
            $m2Total <= 150 => 'MEDIANA',
            $m2Total <= 250 => 'GRANDE',
            default => 'MUY_GRANDE',
        };
    }

    /**
     * Verificar si la propiedad tiene cotización activa
     */
    public function tieneCotizacion(): bool
    {
        return $this->cotizacion_activa_id !== null;
    }

    /**
     * Verificar si el precio está en revisión
     */
    public function precioEnRevision(): bool
    {
        if (!$this->tieneCotizacion()) {
            return false;
        }

        return $this->aprobacionesPrecio()
            ->where('estatus', 'PENDIENTE')
            ->exists();
    }

    /**
     * Obtener el precio efectivo a mostrar
     */
    public function getPrecioEfectivoAttribute(): ?float
    {
        // Si hay precio custom aprobado, usar ese
        if ($this->precio_custom_solicitado) {
            return $this->precio_custom_solicitado;
        }

        // Si no, usar el precio con descuento de la cotización
        return $this->precio_venta_con_descuento;
    }

    /**
     * Verificar si la propiedad está lista para publicarse
     */
    public function estaListaParaPublicar(): bool
    {
        // Validar campos obligatorios
        $camposObligatorios = [
            $this->numero_credito,
            $this->direccion_completa,
            $this->estado_id,
            $this->municipio_id,
            $this->precio_lista,
        ];

        $camposCompletos = !in_array(null, $camposObligatorios, true);

        // Debe tener campos completos Y precio calculado
        return $camposCompletos && $this->precio_calculado;
    }

    /**
     * Obtener el estado descriptivo del precio
     */
    public function getEstadoPrecioAttribute(): string
    {
        if (!$this->precio_calculado) {
            return 'SIN_CALCULAR';
        }

        if ($this->precio_requiere_decision_dge) {
            return 'REQUIERE_DECISION_DGE';
        }

        if ($this->precio_aprobado) {
            return 'APROBADO';
        }

        return 'PENDIENTE_APROBACION';
    }

    /**
     * Obtener badge del estado del precio
     */
    public function getBadgeEstadoPrecioAttribute(): array
    {
        return match ($this->estado_precio) {
            'SIN_CALCULAR' => [
                'label' => 'Sin Calcular',
                'color' => 'gray',
                'icon' => 'heroicon-o-calculator',
            ],
            'PENDIENTE_APROBACION' => [
                'label' => '⏳ Pendiente Aprobación',
                'color' => 'warning',
                'icon' => 'heroicon-o-clock',
            ],
            'REQUIERE_DECISION_DGE' => [
                'label' => '⚠️ Requiere Decisión DGE',
                'color' => 'danger',
                'icon' => 'heroicon-o-exclamation-triangle',
            ],
            'APROBADO' => [
                'label' => '✅ Precio Aprobado',
                'color' => 'success',
                'icon' => 'heroicon-o-check-circle',
            ],
            default => [
                'label' => 'Desconocido',
                'color' => 'gray',
                'icon' => 'heroicon-o-question-mark-circle',
            ],
        };
    }

    /**
     * Obtener leyenda para mostrar en la propiedad publicada
     */
    public function getLeyendaPrecioAttribute(): ?string
    {
        if (!$this->precio_calculado) {
            return null;
        }

        if ($this->precio_aprobado) {
            return null; // Todo OK, no mostrar leyenda
        }

        if ($this->precio_requiere_decision_dge) {
            return '⚠️ Precio en revisión final';
        }

        return '⏳ Precio en revisión';
    }

    /**
     * Marcar precio como aprobado (cuando ambas áreas aprueban)
     */
    public function marcarPrecioComoAprobado(): void
    {
        $this->update([
            'precio_aprobado' => true,
            'precio_fecha_aprobacion' => now(),
            'precio_requiere_decision_dge' => false,
        ]);
    }

    /**
     * Marcar que requiere decisión de DGE (cuando hay rechazos)
     */
    public function marcarRequiereDecisionDGE(): void
    {
        $this->update([
            'precio_aprobado' => false,
            'precio_requiere_decision_dge' => true,
        ]);
    }
}
