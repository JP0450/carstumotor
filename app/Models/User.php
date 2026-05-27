<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'address', 'document'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_EXTERNO = 'externo';
    public const ROLE_JEFE = 'jefe';
    public const ROLE_NEGOCIOS = 'negocios_internacionales';
    public const ROLE_CONTADOR = 'contador';

    public static function roles(): array
    {
        return [
            self::ROLE_EXTERNO,
            self::ROLE_JEFE,
            self::ROLE_NEGOCIOS,
            self::ROLE_CONTADOR,
        ];
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function citas()
    {
        return $this->hasMany(Cita::class);
    }

    public function pedidosInternacionales()
    {
        return $this->hasMany(PedidoInternacional::class);
    }

    public function informesFinancieros()
    {
        return $this->hasMany(InformeFinanciero::class);
    }

    public function transaccionesFinancieras()
    {
        return $this->hasMany(TransaccionFinanciera::class);
    }

    public function hasRole(string $role): bool
    {
        return strtolower(trim((string) $this->role)) === strtolower(trim($role));
    }
}
