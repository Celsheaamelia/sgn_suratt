<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property string $role
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    const ROLE_ADMIN      = 'admin';
    const ROLE_SUPERVISOR = 'supervisor';
    const ROLE_SATPAM     = 'satpam';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
        protected $fillable = [
        'username',
        'email',
        'password',
        'google_id',
        'role',
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
            'password' => 'hashed',
        ];
    }

    public function patrolSessions()
    {
        return $this->hasMany(PatrolSession::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isSupervisor(): bool
    {
        return $this->role === self::ROLE_SUPERVISOR;
    }

    public function isSatpam(): bool
    {
        return $this->role === self::ROLE_SATPAM;
    }

    /**
     * True kalau user boleh masuk ke area monitoring/checkpoint (admin atau supervisor).
     * Berguna buat kondisi di Blade tanpa nulis array role berulang-ulang.
     */
    public function isPengawas(): bool
    {
        return $this->isAdmin() || $this->isSupervisor();
    }

    public function labelRole(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN      => 'Administrator',
            self::ROLE_SUPERVISOR => 'Supervisor',
            self::ROLE_SATPAM     => 'Satpam',
            default                => ucfirst((string) $this->role),
        };
    }
}
