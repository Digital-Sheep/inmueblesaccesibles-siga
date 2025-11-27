<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Juicio extends Model
{
    use SoftDeletes;

    protected $table = 'juicios';

    protected $fillable = [
        'expediente_id',
        'tipo_juicio_id', // Catálogo
        'no_expediente_juzgado',
        'juzgado',
        'distrito_judicial',
        'created_by',
        'updated_by',
    ];

    public function expediente(): BelongsTo
    {
        return $this->belongsTo(ExpedienteJuridico::class, 'expediente_id');
    }

    public function tipoJuicio(): BelongsTo
    {
        return $this->belongsTo(CatTipoJuicio::class, 'tipo_juicio_id');
    }

    // Auditoría...
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
