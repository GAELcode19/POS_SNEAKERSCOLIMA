<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Layaway extends Model
{
    protected $fillable = [
        'folio',
        'product_id',
        'product_name',
        'size',
        'employee_id',
        'customer_name',
        'customer_phone',
        'total_price',
        'paid_amount',
        'status',
        'due_at',
        'credit_expires_at',
        'liquidated_at',
    ];

    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_at' => 'datetime',
            'credit_expires_at' => 'datetime',
            'liquidated_at' => 'datetime',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function payments()
    {
        return $this->hasMany(LayawayPayment::class);
    }

    /** Cuánto falta por liquidar. */
    public function remaining(): float
    {
        return max(0, (float) $this->total_price - (float) $this->paid_amount);
    }

    /** ¿El saldo a favor sigue vigente para reclamar? */
    public function creditIsValid(): bool
    {
        return $this->status === 'vencido'
            && (float) $this->paid_amount > 0
            && $this->credit_expires_at->isFuture();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'activo' => 'Activo',
            'liquidado' => 'Liquidado',
            'vencido' => 'Vencido (saldo a favor)',
            'reclamado' => 'Saldo usado',
            'expirado' => 'Expirado',
            default => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'activo' => 'blue',
            'liquidado' => 'green',
            'vencido' => 'orange',
            'reclamado' => 'gray',
            'expirado' => 'red',
            default => 'gray',
        };
    }

    /**
     * Barre los apartados vencidos:
     * - activos que pasaron su fecha límite -> vencido (regresa el stock reservado)
     * - vencidos cuyo saldo a favor ya expiró -> expirado
     * Se llama de forma perezosa al cargar las pantallas relevantes.
     */
    public static function processExpirations(): void
    {
        $overdue = static::where('status', 'activo')
            ->where('due_at', '<', now())
            ->get();

        foreach ($overdue as $layaway) {
            DB::transaction(function () use ($layaway) {
                // Regresar la unidad reservada al stock disponible
                if ($layaway->product_id) {
                    Product::where('id', $layaway->product_id)->increment('stock', 1);
                    if ($layaway->size) {
                        ProductSize::where('product_id', $layaway->product_id)
                            ->where('size', $layaway->size)
                            ->increment('stock', 1);
                    }
                }
                $layaway->update(['status' => 'vencido']);
            });
        }

        static::where('status', 'vencido')
            ->where('credit_expires_at', '<', now())
            ->update(['status' => 'expirado']);
    }
}
