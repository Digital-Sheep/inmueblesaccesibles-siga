<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatSucursal extends Model
{
    use SoftDeletes;

    protected $table = 'cat_sucursales';

    protected $fillable = [
        'nombre',
        'abreviatura',
        'activo',
        'created_by',
        'updated_by',
    ];

    /**
     * Convierte automáticamente el 1/0 de la BD a true/false
     */
    protected $casts = [
        'activo' => 'boolean',
    ];

    // --- RELACIONES PRINCIPALES ---

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'sucursal_id');
    }

    public function propiedades(): HasMany
    {
        return $this->hasMany(Propiedad::class, 'sucursal_id');
    }

    public function prospectos(): HasMany
    {
        return $this->hasMany(Prospecto::class, 'sucursal_id');
    }

        public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class, 'sucursal_id');
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
