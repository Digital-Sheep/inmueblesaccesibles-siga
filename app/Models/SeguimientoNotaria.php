<?php

namespace App\Models;

use App\Enums\SedeJuicioEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SeguimientoNotaria extends Model
{
    use SoftDeletes;

    protected $table = 'seguimientos_notaria';

    protected $fillable = [
        'propiedad_id',
        'numero_credito',
        'id_garantia',
        'nombre_cliente',
        'notario',
        'numero_escritura',
        'fecha_escritura',
        'sede',
        'administradora',
        'hay_cesion_derechos',
        'cedente',
        'cesionario',
        'etapa_actual',
        'notas_director',
        'activo',
    ];

    protected $casts = [
        'sede'               => SedeJuicioEnum::class,
        'fecha_escritura'    => 'date',
        'hay_cesion_derechos' => 'boolean',
        'activo'             => 'boolean',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Propiedad::class);
    }

    public function actuaciones(): HasMany
    {
        return $this->hasMany(ActuacionNotaria::class)
                    ->orderByDesc('fecha_actuacion');
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    public function getTituloAttribute(): string
    {
        return $this->id_garantia
            ?? $this->numero_credito
            ?? "Notaría #{$this->id}";
    }
}
