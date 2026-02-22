<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory, HasAuditLog;

    protected $fillable = [
        'client_id',
        'owner_id',
        'name',
        'description',
        'status',
        'budget',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'status'     => ProjectStatus::class,
            'budget'     => 'decimal:2',
            'start_date' => 'date',
            'end_date'   => 'date',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'project_user')->withTimestamps();
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function timeEntries()
    {
        return $this->hasManyThrough(TimeEntry::class, Task::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', ProjectStatus::Active);
    }

    public function scopeForClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }
}
