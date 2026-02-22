<?php

namespace App\Models;

use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeEntry extends Model
{
    use HasFactory, HasAuditLog;

    protected $fillable = [
        'task_id',
        'user_id',
        'description',
        'hours',
        'date',
        'is_billable',
        'hourly_rate',
    ];

    protected function casts(): array
    {
        return [
            'hours'       => 'decimal:2',
            'hourly_rate' => 'decimal:2',
            'date'        => 'date',
            'is_billable' => 'boolean',
        ];
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculated billable amount.
     */
    public function getBillableAmountAttribute(): float
    {
        return $this->is_billable ? (float) $this->hours * (float) $this->hourly_rate : 0;
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForDate($query, string $date)
    {
        return $query->where('date', $date);
    }

    public function scopeBillable($query)
    {
        return $query->where('is_billable', true);
    }
}
