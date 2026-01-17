<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ProcesoCompra extends Model
{
    use SoftDeletes;

    protected $table = 'procesos_compra';

    protected $fillable = [
        'proceso_venta_id',
        'propiedad_id',
        'dictamen_id',
        'tipo_compra',
        'estatus',
        'precio_compra_negociado',
        'gastos_notariales_presupuesto',
        'fecha_pago_proveedor',
        'fecha_firma_cesion',
        'notaria_numero',
        'notario_nombre',
        'numero_escritura',
        'responsable_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_pago_proveedor' => 'date',
        'fecha_firma_cesion' => 'date',
    ];

    // --- RELACIONES ---

    public function procesoVenta(): BelongsTo
    {
        return $this->belongsTo(ProcesoVenta::class, 'proceso_venta_id');
    }

    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Propiedad::class, 'propiedad_id');
    }

    public function dictamen(): BelongsTo
    {
        return $this->belongsTo(Dictamen::class, 'dictamen_id');
    }

    // Para subir la Cesión escaneada, fichas de pago al banco, etc.
    public function archivos(): MorphMany
    {
        return $this->morphMany(Archivo::class, 'entidad');
    }
}
