<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cotizacion extends Model
{
    protected $table = 'cotizaciones';

    protected $fillable = [
        'propiedad_id',
        'version',
        'activa',
        'precio_base',
        'tamano_propiedad',
        'etapa_procesal_id',
        'costo_remodelacion',
        'costo_luz',
        'costo_agua',
        'costo_predial',
        'costo_gastos_juridicos',
        'total_costos',
        'porcentaje_inversion',
        'monto_inversion',
        'precio_sin_remodelacion',
        'precio_venta_sugerido',
        'porcentaje_descuento',
        'precio_venta_con_descuento',
        'porcentaje_utilidad',
        'calculada_por_id',
        'notas',
    ];

    protected $casts = [
        'version' => 'integer',
        'activa' => 'boolean',
        'precio_base' => 'decimal:2',
        'costo_remodelacion' => 'decimal:2',
        'costo_luz' => 'decimal:2',
        'costo_agua' => 'decimal:2',
        'costo_predial' => 'decimal:2',
        'costo_gastos_juridicos' => 'decimal:2',
        'total_costos' => 'decimal:2',
        'porcentaje_inversion' => 'decimal:2',
        'monto_inversion' => 'decimal:2',
        'precio_sin_remodelacion' => 'decimal:2',
        'precio_venta_sugerido' => 'decimal:2',
        'porcentaje_descuento' => 'decimal:2',
        'precio_venta_con_descuento' => 'decimal:2',
        'porcentaje_utilidad' => 'decimal:2',
    ];

    // --- RELACIONES ---

    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Propiedad::class);
    }

    public function etapaProcesal(): BelongsTo
    {
        return $this->belongsTo(CatEtapaProcesal::class, 'etapa_procesal_id');
    }

    public function calculadaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculada_por_id');
    }

    public function aprobaciones(): HasMany
    {
        return $this->hasMany(AprobacionPrecio::class, 'cotizacion_id');
    }

    // --- SCOPES ---

    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    public function scopePorPropiedad($query, int $propiedadId)
    {
        return $query->where('propiedad_id', $propiedadId);
    }

    // --- MÉTODOS HELPER ---

    /**
     * Obtener la cotización activa de una propiedad
     */
    public static function getActiva(int $propiedadId): ?self
    {
        return self::where('propiedad_id', $propiedadId)
            ->where('activa', true)
            ->first();
    }

    /**
     * Desactivar todas las cotizaciones anteriores de esta propiedad
     */
    public function desactivarAnteriores(): void
    {
        self::where('propiedad_id', $this->propiedad_id)
            ->where('id', '!=', $this->id)
            ->update(['activa' => false]);
    }

    /**
     * Verificar si todas las aprobaciones están completas
     */
    public function tieneAprobacionesCompletas(): bool
    {
        return $this->aprobaciones()
            ->whereIn('tipo_aprobador', ['COMERCIAL', 'CONTABILIDAD'])
            ->where('estatus', 'APROBADO')
            ->count() === 2;
    }

    /**
     * Verificar si hay algún rechazo
     */
    public function tieneRechazos(): bool
    {
        return $this->aprobaciones()
            ->where('estatus', 'RECHAZADO')
            ->exists();
    }

    /**
     * Copiar precios a la propiedad
     */
    public function sincronizarConPropiedad(): void
    {
        $this->propiedad->update([
            'precio_sin_remodelacion' => $this->precio_sin_remodelacion,
            'precio_venta_sugerido' => $this->precio_venta_sugerido,
            'precio_venta_con_descuento' => $this->precio_venta_con_descuento,
            'porcentaje_descuento' => $this->porcentaje_descuento,
            'porcentaje_utilidad' => $this->porcentaje_utilidad,
            'cotizacion_activa_id' => $this->id,
        ]);
    }
}
