<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Job extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'job_type',
        'client_name',
        'invoice_amount',
        'labor_hours',
        'labor_rate',
        'status'
    ];

    protected $casts = [
        'invoice_amount' => 'float',
        'labor_rate' => 'float',
        'labor_hours' => 'integer',
    ];

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    public function getLaborCostAttribute(): float
    {
        return round($this->labor_hours * $this->labor_rate, 2);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeSearch($query, $search)
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('job_type', 'like', "{$search}%")
              ->orWhere('client_name', 'like', "{$search}%");
        });
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when($filters['status'] ?? null, function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($filters['job_type'] ?? null, function ($q, $jobType) {
                $q->where('job_type', $jobType);
            });
    }
}
