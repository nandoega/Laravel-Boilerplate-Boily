<?php

namespace App\Models;

use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory, HasAuditLog;

    protected $fillable = [
        'invoice_id',
        'amount',
        'method',
        'reference',
        'notes',
        'paid_at',
        'is_refunded',
        'refunded_at',
        'refund_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount'      => 'decimal:2',
            'paid_at'     => 'datetime',
            'is_refunded' => 'boolean',
            'refunded_at' => 'datetime',
        ];
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function scopeRefunded($query)
    {
        return $query->where('is_refunded', true);
    }
}
