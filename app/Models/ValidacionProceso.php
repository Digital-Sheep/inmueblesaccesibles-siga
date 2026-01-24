<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Permission\Models\Role; // Importamos el modelo de Spatie

class ValidacionProceso extends Model
{
    // Definimos la tabla explícitamente
    protected $table = 'validaciones_proceso';

    protected $fillable = [
        'validable_type', // Qué se valida (Pago, Dictamen, Contrato)
        'validable_id',
        'accion_intentada',
        'rol_validador_id', // Qué rol debe aprobar (Ej. GAD)
        'usuario_validador_id', // Si es para alguien específico
        'estatus', // PENDIENTE, APROBADO, RECHAZADO
        'comentarios',
        'fecha_resolucion',
        'solicitante_id', // Quien pidió la validación
    ];

    protected $casts = [
        'fecha_resolucion' => 'datetime',
    ];

    // --- RELACIONES ---

    /**
     * El objeto que se está validando (Polimórfico).
     * Puede ser un Pago, un Dictamen o una SolicitudContrato.
     */
    public function validable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * El usuario que solicitó la acción (Ej. el Asesor).
     */
    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    /**
     * El usuario que finalmente dio el clic de Aprobado/Rechazado.
     */
    public function validador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_validador_id');
    }

    /**
     * El Rol que tiene permiso para validar esto.
     */
    public function rolRequerido(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'rol_validador_id');
    }
}
