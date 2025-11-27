<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'telefono',
        'avatar_url',
        'activo',
        'sucursal_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    // --- RELACIONES ---

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(CatSucursal::class, 'sucursal_id');
    }

    // --- FILAMENT (Control de Acceso) ---

    public function canAccessPanel(Panel $panel): bool
    {
        // Regla de Negocio: Solo usuarios activos pueden entrar.
        // Esto es vital para cuando un asesor se va de la empresa.
        return $this->activo;
    }

    // Opcional: Si quieres que el nombre en el panel incluya la sucursal
    public function getFilamentName(): string
    {
        return "{$this->name}";
    }
}
