<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class EsquemaPago extends Model
{
    protected $table = 'esquemas_pago';

    protected $fillable = [
        'propiedad_id',
        'apartado_monto',
        'total_porcentaje',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'apartado_monto' => 'decimal:2',
        'total_porcentaje' => 'decimal:2',
    ];

    // --- RELACIONES ---

    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Propiedad::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(EsquemaPagoDetalle::class)->orderBy('orden');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // --- MÉTODOS HELPER ---

    /**
     * Validar que la suma de porcentajes sea exactamente 100
     */
    public function validarPorcentajes(): bool
    {
        $totalPorcentaje = $this->detalles()->sum('porcentaje');
        return abs($totalPorcentaje - 100) < 0.01; // Tolerancia de 0.01
    }

    /**
     * Actualizar el total de porcentajes
     */
    public function actualizarTotalPorcentaje(): void
    {
        $this->total_porcentaje = $this->detalles()->sum('porcentaje');
        $this->save();
    }

    /**
     * Calcular montos de cada pago basado en el precio de venta
     */
    public function calcularMontos(float $precioVenta): void
    {
        $detalles = $this->detalles()->get();
        $ultimoDetalle = $detalles->last();

        foreach ($detalles as $detalle) {
            $monto = ($precioVenta * $detalle->porcentaje) / 100;

            // Si es el último pago, descontar el apartado
            if ($ultimoDetalle && $detalle->id === $ultimoDetalle->id) {
                $monto -= $this->apartado_monto;
            }

            $detalle->monto_calculado = $monto;
            $detalle->save();
        }
    }

    /**
     * Obtener el esquema por defecto (3 pagos)
     */
    public static function getEsquemaDefault(): array
    {
        return [
            [
                'numero_pago' => 1,
                'descripcion' => 'Estudio de garantía',
                'porcentaje' => 35.00,
                'orden' => 1,
            ],
            [
                'numero_pago' => 2,
                'descripcion' => 'Procesos de compra y desalojo',
                'porcentaje' => 60.00,
                'orden' => 2,
            ],
            [
                'numero_pago' => 3,
                'descripcion' => 'Gastos de desalojo y habilitación',
                'porcentaje' => 5.00,
                'orden' => 3,
            ],
        ];
    }

    /**
     * Crear o actualizar esquema con sus detalles
     */
    public static function crearOActualizar(int $propiedadId, float $apartado, array $detalles): self
    {
        $esquema = self::updateOrCreate(
            ['propiedad_id' => $propiedadId],
            [
                'apartado_monto' => $apartado,
                'updated_by' => Auth::id(),
            ]
        );

        // Eliminar detalles anteriores
        $esquema->detalles()->delete();

        // Crear nuevos detalles
        foreach ($detalles as $index => $detalle) {
            $esquema->detalles()->create([
                'numero_pago' => $index + 1,
                'descripcion' => $detalle['descripcion'],
                'porcentaje' => $detalle['porcentaje'],
                'monto_calculado' => $detalle['monto_calculado'] ?? null,
                'orden' => $index + 1,
            ]);
        }

        // Actualizar total de porcentajes
        $esquema->actualizarTotalPorcentaje();

        return $esquema;
    }
}
