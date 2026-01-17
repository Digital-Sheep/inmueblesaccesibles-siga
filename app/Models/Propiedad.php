<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Propiedad extends Model
{
    use SoftDeletes;

    protected $table = 'propiedades';

    protected $fillable = [
        // Identificación
        'numero_credito',
        'cartera_id',
        'sucursal_id',
        'administradora_id',

        // Ubicación
        'direccion_completa',
        'calle',
        'numero_exterior',
        'numero_interior',
        'colonia',
        'fraccionamiento',
        'codigo_postal',
        'estado_id',
        'municipio_id',
        'estado_borrador', // Dato sucio del Excel
        'municipio_borrador', // Dato sucio del Excel

        // Geo
        'google_maps_link',
        'latitud',
        'longitud',

        // Características
        'tipo_vivienda',
        'tipo_inmueble',
        'terreno_m2',
        'construccion_m2',
        'habitaciones',
        'banos',
        'estacionamientos',

        // Datos Legales Reportados (Informativos)
        'etapa_judicial_reportada',
        'fecha_corte_judicial',
        'avaluo_banco',
        'cofinavit_monto',

        // Precios
        'precio_lista',
        'precio_venta_sugerido',
        'precio_minimo',

        // Estatus
        'estatus_comercial', // DISPONIBLE, APARTADA...
        'estatus_legal',     // R1, R2...
        'interesado_principal_type', // Quién la tiene apartada
        'interesado_principal_id',

        'created_by',
        'updated_by',
    ];

    /**
     * Atributo Virtual para mostrar en selectores de Filament.
     * Ej: "21700... - Av. Puerto Bajania 16..."
     */
    public function getNombreCortoAttribute(): string
    {
        $id = $this->numero_credito ?? 'S/N';
        $dir = Str::limit($this->direccion_completa, 40);
        return "{$id} - {$dir}";
    }

    // --- RELACIONES ---

    public function cartera(): BelongsTo
    {
        return $this->belongsTo(Cartera::class, 'cartera_id');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(CatSucursal::class, 'sucursal_id');
    }

    public function administradora(): BelongsTo
    {
        return $this->belongsTo(CatAdministradora::class, 'administradora_id');
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(CatEstado::class, 'estado_id');
    }

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(CatMunicipio::class, 'municipio_id');
    }

    /**
     * Quién la tiene apartada o comprada actualmente (Prospecto o Cliente).
     */
    public function interesadoPrincipal(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Historial de todos los intentos de venta (activos y cancelados).
     */
    public function procesosVenta(): HasMany
    {
        return $this->hasMany(ProcesoVenta::class, 'propiedad_id');
    }

    /**
     * Fotos de la galería.
     */
    public function fotos(): MorphMany
    {
        return $this->morphMany(Archivo::class, 'entidad')
            ->where('categoria', 'GALERIA');
    }

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
