<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cartera extends Model
{
    use SoftDeletes;

    protected $table = 'carteras';

    protected $fillable = [
        'nombre',
        'administradora_id',
        'sucursal_id',
        'archivo_path', // Ruta del Excel original
        'fecha_recepcion',
        'estatus', // BORRADOR, PROCESADA, PUBLICADA
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_recepcion' => 'date',
    ];

    // --- RELACIONES ---

    /**
     * Una Cartera pertenece a un Banco/Administradora.
     */
    public function administradora(): BelongsTo
    {
        return $this->belongsTo(CatAdministradora::class, 'administradora_id');
    }

    /**
     * Relación con la Sucursal
     * Esto permite hacer: $cartera->sucursal->nombre
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(CatSucursal::class, 'sucursal_id');
    }

    /**
     * Una Cartera contiene muchas Propiedades (las filas del Excel).
     */
    public function propiedades(): HasMany
    {
        return $this->hasMany(Propiedad::class, 'cartera_id');
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
}
