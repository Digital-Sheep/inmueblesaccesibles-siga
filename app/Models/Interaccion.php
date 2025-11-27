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
        'entidad_type', // Prospecto, Cliente, etc.
        'entidad_id',
        'tipo',        // LLAMADA, WHATSAPP, etc.
        'resultado',   // CONTESTO, BUZON...
        'comentario',
        'fecha_programada',
        'fecha_realizada',
        'usuario_id',  // Quien hizo la interacción
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fecha_programada' => 'datetime',
        'fecha_realizada' => 'datetime',
    ];

    public function getResumenInteraccionAttribute(): string
    {
        // Formatea la fecha de interacción (o de registro) y trunca el comentario
        $fecha = $this->fecha_realizada ?? $this->created_at;
        $fechaFormateada = $fecha ? $fecha->format('d/M H:i') : 'PNDT';
        $comentario = Str::limit($this->comentario, 30);

        return "{$this->tipo} ({$fechaFormateada}): {$comentario}";
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
