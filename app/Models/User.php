<?php

namespace App\Models;

use App\Enums\UserProfile;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory;
    use Notifiable;
    use TwoFactorAuthenticatable;

    protected $attributes = [
        'perfil' => UserProfile::Usuario->value,
        'ativo' => true,
        'must_change_password' => false,
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'perfil',
        'ativo',
        'must_change_password',
        'password_changed_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    public function isAdministrator(): bool
    {
        return $this->perfil === UserProfile::Administrador;
    }

    public function atividadeCategorias(): HasMany
    {
        return $this->hasMany(AtividadeCategoria::class);
    }

    public function pits(): HasMany
    {
        return $this->hasMany(Pit::class);
    }

    public function planosTrabalho(): HasManyThrough
    {
        return $this->hasManyThrough(PlanoTrabalho::class, Pit::class);
    }

    public function atividades(): HasMany
    {
        return $this->hasMany(Atividade::class);
    }

    /**
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        if (! $this->ativo) {
            return;
        }

        $this->notify(new ResetPasswordNotification($token));
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'perfil' => UserProfile::class,
            'ativo' => 'boolean',
            'must_change_password' => 'boolean',
            'password_changed_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
}
