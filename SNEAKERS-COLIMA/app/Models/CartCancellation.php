<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartCancellation extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'product_id',
        'size',
        'price',
        'reason',
        'alert_level',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function alertColor(): string
    {
        return match ($this->alert_level) {
            'alto' => '#ef4444',
            'medio' => '#f59e0b',
            'bajo' => '#6b7280',
            default => '#6b7280',
        };
    }
}
