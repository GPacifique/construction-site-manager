<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConstructionActivity extends Model
{
    protected $fillable = [
        'project_id',
        'code',
        'name',
        'description',
        'unit',
        'planned_quantity',
        'completed_quantity',
        'planned_start_date',
        'planned_end_date',
        'actual_start_date',
        'actual_end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'planned_quantity' => 'decimal:4',
            'completed_quantity' => 'decimal:4',
            'planned_start_date' => 'date',
            'planned_end_date' => 'date',
            'actual_start_date' => 'date',
            'actual_end_date' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
