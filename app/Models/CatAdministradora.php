<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatAdministradora extends Model
{
    use SoftDeletes;

    protected $table = 'cat_administradoras';

    protected $fillable = [
        'nombre',
        'contacto_principal',
        'activo',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // --- RELACIONES ---

    /**
     * Una Administradora nos entrega muchas Carteras (Exceles).
     */
    public function carteras(): HasMany
    {
        return $this->hasMany(Cartera::class, 'administradora_id');
    }

    /**
     * Relación directa con Propiedades (para filtros rápidos).
     */
    public function propiedades(): HasMany
    {
        return $this->hasMany(Propiedad::class, 'administradora_id');
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
