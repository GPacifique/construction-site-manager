<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use App\Enums\ProjectPermission;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'system_role',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)
            ->withPivot(['role', 'status'])
            ->withTimestamps();
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Determine whether the user has an active role in the given company.
     *
     * @param  array<UserRole|string>  $roles
     */
    public function hasCompanyRole(Company $company, array $roles): bool
    {
        $membership = $this->companies()
            ->whereKey($company->getKey())
            ->wherePivot('status', 'active')
            ->first();

        if (! $membership) {
            return false;
        }

        $roleValues = array_map(
            static fn (UserRole|string $role): string => $role instanceof UserRole ? $role->value : $role,
            $roles,
        );

        return in_array($membership->pivot->role, $roleValues, true);
    }

    /**
     * Check a project role only while both company membership and assignment are active.
     *
     * @param  array<UserRole|string>  $roles
     */
    public function hasProjectRole(Project $project, array $roles): bool
    {
        if (! $this->companies()
            ->whereKey($project->company_id)
            ->wherePivot('status', 'active')
            ->exists()) {
            return false;
        }

        $membership = $this->projects()
            ->whereKey($project->getKey())
            ->first();

        if (! $membership) {
            return false;
        }

        $roleValues = array_map(
            static fn (UserRole|string $role): string => $role instanceof UserRole ? $role->value : $role,
            $roles,
        );

        return in_array($membership->pivot->role, $roleValues, true);
    }

    public function hasProjectPermission(Project $project, ProjectPermission $permission): bool
    {
        if ($this->isSystemAdministrator()) {
            return true;
        }

        if ($this->hasCompanyRole($project->company, [UserRole::CompanyOwner])) {
            return UserRole::CompanyOwner->allows($permission);
        }

        $role = $this->projects()
            ->whereKey($project->getKey())
            ->first()?->pivot?->role;

        if (! $role || ! $this->companies()
            ->whereKey($project->company_id)
            ->wherePivot('status', 'active')
            ->exists()) {
            return false;
        }

        $projectRole = UserRole::tryFrom($role);

        return $projectRole?->allows($permission) ?? false;
    }

    public function isSystemAdministrator(): bool
    {
        return $this->system_role === UserRole::SystemAdministrator->value;
    }
}
