<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    public function view(User $user, Company $company): bool
    {
        return $user->companies()
            ->whereKey($company->getKey())
            ->wherePivot('status', 'active')
            ->exists();
    }

    public function createProject(User $user, Company $company): bool
    {
        return $user->hasCompanyRole($company, [
            UserRole::CompanyOwner,
            UserRole::ProjectManager,
        ]);
    }

    public function manage(User $user, Company $company): bool
    {
        return $user->hasCompanyRole($company, [UserRole::CompanyOwner]);
    }
}
