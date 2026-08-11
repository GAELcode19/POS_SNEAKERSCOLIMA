<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'employee_id',
        'total',
        'payment_method',
        'status',
        'is_online',
        'customer_name',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'is_online' => 'boolean',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'completada' => 'Listo',
            'en_proceso' => 'En proceso',
            'pendiente' => 'Pendiente',
            'cancelada' => 'Cancelada',
            default => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'completada' => 'green',
            'en_proceso' => 'yellow',
            'pendiente' => 'orange',
            'cancelada' => 'red',
            default => 'gray',
        };
    }
}
