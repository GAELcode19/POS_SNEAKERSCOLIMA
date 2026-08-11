<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'status',
        'hired_at',
        'weekly_hours_target',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'hired_at' => 'date',
            'weekly_hours_target' => 'decimal:1',
        ];
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'employee_id');
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class, 'employee_id');
    }

    public function cartCancellations()
    {
        return $this->hasMany(CartCancellation::class, 'employee_id');
    }

    public function currentShift()
    {
        return $this->hasOne(Shift::class, 'employee_id')->whereNull('ended_at')->latest();
    }

    public function weeklyHours(): float
    {
        return $this->shifts()
            ->where('started_at', '>=', now()->startOfWeek())
            ->sum('hours_logged');
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            'asesor_ventas' => 'Asesor de Ventas',
            'supervisor' => 'Supervisor',
            'cajero' => 'Cajero',
            'gerencia' => 'Gerencia',
            default => $this->role,
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'activo' => 'Activo',
            'descanso' => 'En descanso',
            'desconectado' => 'Desconectado',
            default => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'activo' => 'green',
            'descanso' => 'yellow',
            'desconectado' => 'gray',
            default => 'gray',
        };
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['gerencia', 'supervisor']);
    }
}
