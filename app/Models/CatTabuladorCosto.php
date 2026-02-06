<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatTabuladorCosto extends Model
{
    protected $table = 'cat_tabulador_costos';

    protected $fillable = [
        'tamano_propiedad',
        'costo_remodelacion',
        'costo_luz',
        'costo_agua',
        'costo_predial',
        'costo_gastos_juridicos',
        'activo',
        'updated_by',
    ];

    protected $casts = [
        'costo_remodelacion' => 'decimal:2',
        'costo_luz' => 'decimal:2',
        'costo_agua' => 'decimal:2',
        'costo_predial' => 'decimal:2',
        'costo_gastos_juridicos' => 'decimal:2',
        'activo' => 'boolean',
    ];

    // --- RELACIONES ---

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // --- MÉTODOS HELPER ---

    /**
     * Obtener costos para un tamaño específico
     */
    public static function getCostosPorTamano(string $tamano): ?self
    {
        return self::where('tamano_propiedad', $tamano)
            ->where('activo', true)
            ->first();
    }

    /**
     * Calcular total de costos
     */
    public function getTotalCostosAttribute(): float
    {
        return $this->costo_remodelacion +
            $this->costo_luz +
            $this->costo_agua +
            $this->costo_predial +
            $this->costo_gastos_juridicos;
    }

    /**
     * Calcular total sin remodelación
     */
    public function getTotalSinRemodelacionAttribute(): float
    {
        return $this->costo_luz +
            $this->costo_agua +
            $this->costo_predial +
            $this->costo_gastos_juridicos;
    }
}
