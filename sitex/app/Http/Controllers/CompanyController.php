<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $companies = $request->user()->isSystemAdministrator()
            ? Company::query()
                ->withCount('projects')
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'currency'])
                ->map(fn (Company $company): array => [
                    ...$company->only('id', 'name', 'slug', 'currency'),
                    'role' => 'system_admin',
                    'projects_count' => $company->projects_count,
                ])
            : $request->user()->companies()
                ->wherePivot('status', 'active')
                ->withCount(['projects as projects_count' => function ($query) use ($request): void {
                    $query->where(function ($projects) use ($request): void {
                        $projects->whereHas(
                            'company.users',
                            fn ($users) => $users
                                ->whereKey($request->user()->id)
                                ->wherePivot('status', 'active')
                                ->wherePivot('role', UserRole::CompanyOwner->value),
                        )->orWhereHas(
                            'members',
                            fn ($members) => $members->whereKey($request->user()->id),
                        );
                    });
                }])
                ->orderBy('companies.name')
                ->get(['companies.id', 'companies.name', 'companies.slug', 'companies.currency'])
                ->map(fn (Company $company): array => [
                    ...$company->only('id', 'name', 'slug', 'currency'),
                    'role' => $company->pivot->role,
                    'projects_count' => $company->projects_count,
                ]);

        return Inertia::render('Companies/Index', [
            'companies' => $companies,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Company $company): Response
    {
        $this->authorize('view', $company);

        $user = $request->user();
        $canSeeAllProjects = $user->isSystemAdministrator()
            || $user->hasCompanyRole($company, [UserRole::CompanyOwner]);

        $projects = $company->projects()
            ->when(! $canSeeAllProjects, fn ($query) => $query->whereHas(
                'members',
                fn ($members) => $members->whereKey($user->id),
            ))
            ->orderBy('name')
            ->get(['id', 'company_id', 'name', 'code', 'location', 'status']);

        $canSeeMemberEmails = $user->isSystemAdministrator()
            || $user->hasCompanyRole($company, [UserRole::CompanyOwner]);

        return Inertia::render('Companies/Show', [
            'company' => $company->only('id', 'name', 'currency'),
            'canCreateProject' => $user->can('createProject', $company),
            'canManage' => $user->can('manage', $company),
            'members' => $company->users()
                ->wherePivot('status', 'active')
                ->orderBy('name')
                ->get(['users.id', 'users.name', 'users.email'])
                ->map(fn ($member): array => [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $canSeeMemberEmails ? $member->email : null,
                    'role' => $member->pivot->role,
                ]),
            'projects' => $projects,
        ]);
    }

}
