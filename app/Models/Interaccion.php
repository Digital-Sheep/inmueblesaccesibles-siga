<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Interaccion extends Model
{
    use SoftDeletes;

    protected $table = 'interacciones';

    protected $fillable = [
        'entidad_type',
        'entidad_id',
        'titulo',
        'tipo',
        'estatus',
        'resultado',
        'comentario',
        'evidencia',
        'fecha_programada',
        'fecha_realizada',
        'usuario_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_programada' => 'datetime',
        'fecha_realizada' => 'datetime',
        'evidencia' => 'array',
    ];

    public function getResumenInteraccionAttribute(): string
    {
        $fecha = $this->fecha_realizada ?? $this->fecha_programada;
        $fechaFmt = $fecha ? $fecha->format('d/M H:i') : 'S/F';

        $texto = $this->titulo ?? Str::limit($this->comentario, 40);
        $icono = $this->estatus === 'PENDIENTE' ? '⏳' : '✅';

        return "{$icono} {$this->tipo} ({$fechaFmt}): {$texto}";
    }

    // --- SCOPES (Filtros Rápidos) ---

    // Para mostrar en el Timeline (Lo que ya pasó)
    public function scopeHistorial($query)
    {
        return $query->where('estatus', 'COMPLETADA')
            ->orderBy('fecha_realizada', 'desc');
    }

    // Para mostrar en el Calendario/Agenda (Lo que viene)
    public function scopeAgenda($query)
    {
        return $query->where('estatus', 'PENDIENTE')
            ->whereNull('fecha_realizada')
            ->orderBy('fecha_programada', 'asc');
    }

    // --- RELACIONES ---

    /**
     * ¿Con quién fue la interacción? (Polimórfica)
     */
    public function entidad(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * El asesor que realizó la acción.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
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
