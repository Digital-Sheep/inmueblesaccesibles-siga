<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prospecto extends Model
{
    use SoftDeletes;

    protected $table = 'prospectos';

    protected $fillable = [
        'nombre_completo',
        'celular',
        'email',
        'origen',
        'estatus', // NUEVO, CONTACTADO, CITA, APARTADO, CLIENTE, DESCARTADO
        'motivo_descarte',
        'sucursal_id',
        'usuario_responsable_id',
        'convertido_a_cliente_id',
        'created_by',
        'updated_by',
    ];

    // --- RELACIONES ---

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(CatSucursal::class, 'sucursal_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_responsable_id');
    }

    /**
     * Si ya se convirtió, aquí sabemos quién es su "Yo Cliente".
     */
    public function clienteConvertido(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'convertido_a_cliente_id');
    }

    /**
     * Un prospecto puede tener interés en muchas propiedades (Procesos de Venta).
     */
    public function procesosVenta(): MorphMany
    {
        return $this->morphMany(ProcesoVenta::class, 'interesado');
    }

    /**
     * Historial de llamadas, visitas y mensajes.
     */
    public function interacciones(): MorphMany
    {
        return $this->morphMany(Interaccion::class, 'entidad');
    }

    /**
     * Documentos subidos (Aviso de privacidad, etc).
     */
    public function archivos(): MorphMany
    {
        return $this->morphMany(Archivo::class, 'entidad');
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
