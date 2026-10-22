<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Enums\ProjectPermission;
use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        if (! $user->companies()
            ->whereKey($project->company_id)
            ->wherePivot('status', 'active')
            ->exists()) {
            return false;
        }

        return $user->hasProjectPermission($project, ProjectPermission::ViewProject);
    }

    public function update(User $user, Project $project): bool
    {
        return $user->hasProjectPermission($project, ProjectPermission::ManageProject);
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->hasCompanyRole($project->company, [UserRole::CompanyOwner]);
    }

    public function manageTeam(User $user, Project $project): bool
    {
        return $user->hasProjectPermission($project, ProjectPermission::ManageTeam);
    }

    public function manageBoq(User $user, Project $project): bool
    {
        return $user->hasProjectPermission($project, ProjectPermission::ManageBoq);
    }

    public function recordSiteActivity(User $user, Project $project): bool
    {
        return $user->hasProjectPermission($project, ProjectPermission::RecordSiteActivity);
    }

    public function manageInventory(User $user, Project $project): bool
    {
        return $user->hasProjectPermission($project, ProjectPermission::ManageInventory);
    }

    public function manageProcurement(User $user, Project $project): bool
    {
        return $user->hasProjectPermission($project, ProjectPermission::ManageProcurement);
    }

    public function manageFinance(User $user, Project $project): bool
    {
        return $user->hasProjectPermission($project, ProjectPermission::ManageFinance);
    }

    public function viewFinancials(User $user, Project $project): bool
    {
        return $user->hasProjectPermission($project, ProjectPermission::ViewFinancials);
    }

    public function approveBudget(User $user, Project $project): bool
    {
        return $user->hasProjectPermission($project, ProjectPermission::ApproveBudget);
    }
}
