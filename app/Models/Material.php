<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Material extends Model
{
    use HasUlids;

    protected $fillable = [
        'job_id',
        'name',
        'cost'
    ];

    protected $casts = [
        'cost' => 'float',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }
}
