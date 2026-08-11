<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LayawayPayment extends Model
{
    protected $fillable = [
        'layaway_id',
        'employee_id',
        'amount',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function layaway()
    {
        return $this->belongsTo(Layaway::class);
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
