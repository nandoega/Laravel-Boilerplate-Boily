<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory, HasAuditLog;

    protected $fillable = [
        'client_id',
        'project_id',
        'invoice_number',
        'status',
        'amount',
        'tax',
        'total',
        'notes',
        'due_date',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status'   => InvoiceStatus::class,
            'amount'   => 'decimal:2',
            'tax'      => 'decimal:2',
            'total'    => 'decimal:2',
            'due_date' => 'date',
            'paid_at'  => 'datetime',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', InvoiceStatus::Sent)
                     ->where('due_date', '<', now());
    }
}
