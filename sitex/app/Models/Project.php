<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'company_id',
        'owner_id',
        'project_manager_id',
        'name',
        'code',
        'client_name',
        'location',
        'description',
        'construction_type',
        'start_date',
        'expected_completion_date',
        'original_budget',
        'approved_revised_budget',
        'contract_value',
        'payment_terms',
        'status',
        'showcase_public',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'expected_completion_date' => 'date',
            'original_budget' => 'decimal:2',
            'approved_revised_budget' => 'decimal:2',
            'contract_value' => 'decimal:2',
            'showcase_public' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ConstructionActivity::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class);
    }

    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class);
    }

    public function statusLabel(): string
    {
        return ProjectStatus::from($this->status)->label();
    }
}
