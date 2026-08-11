<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand',
        'category',
        'colorway',
        'image',
        'price',
        'cost',
        'stock',
        'sku',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost' => 'decimal:2',
            'stock' => 'integer',
        ];
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function cartCancellations()
    {
        return $this->hasMany(CartCancellation::class);
    }

    public function sizes()
    {
        return $this->hasMany(ProductSize::class);
    }

    public function stockLabel(): string
    {
        if ($this->stock <= 0) return 'Agotado';
        if ($this->stock <= 2) return 'Ultimas ' . $this->stock;
        return $this->stock . ' disp.';
    }

    public function stockUrgency(): string
    {
        if ($this->stock <= 0) return 'out';
        if ($this->stock <= 2) return 'low';
        return 'ok';
    }
}
