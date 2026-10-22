<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function create(Request $request, Company $company): Response
    {
        $this->authorize('createProject', $company);

        return Inertia::render('Projects/Create', [
            'company' => $company->only('id', 'name', 'currency'),
            'currentUserId' => $request->user()->id,
            'companyOwners' => $company->users()
                ->wherePivot('status', 'active')
                ->wherePivot('role', UserRole::CompanyOwner->value)
                ->orderBy('name')
                ->get(['users.id', 'users.name', 'users.email']),
            'companyUsers' => $company->users()
                ->wherePivot('status', 'active')
                ->orderBy('name')
                ->get(['users.id', 'users.name', 'users.email'])
                ->map(fn (User $member): array => [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'company_role' => $member->pivot->role,
                ]),
            'projectRoles' => array_values(array_map(
                static fn (UserRole $role): array => [
                    'value' => $role->value,
                    'label' => $role->label(),
                ],
                array_filter(
                    UserRole::cases(),
                    static fn (UserRole $role): bool => in_array(
                        $role->value,
                        UserRole::projectValues(),
                        true,
                    ),
                ),
            )),
        ]);
    }

    public function store(StoreProjectRequest $request, Company $company): RedirectResponse
    {
        $project = DB::transaction(function () use ($request, $company): Project {
            $ownerId = $request->ownerId()
                ?? ($request->user()->hasCompanyRole($company, [UserRole::CompanyOwner])
                    ? $request->user()->id
                    : $company->users()
                        ->wherePivot('status', 'active')
                        ->wherePivot('role', UserRole::CompanyOwner->value)
                        ->value('users.id'));

            if (! $ownerId) {
                throw ValidationException::withMessages([
                    'owner_id' => 'Select an active company owner for this project.',
                ]);
            }

            $project = $company->projects()->create([
                ...$request->safe()->except(['project_manager_id', 'members']),
                'owner_id' => $ownerId,
                'approved_revised_budget' => '0.00',
                'status' => 'planning',
                'showcase_public' => $request->boolean('showcase_public'),
            ]);

            $managerId = $request->projectManagerId()
                ?? ($request->user()->isSystemAdministrator()
                    ? $company->users()
                        ->wherePivot('status', 'active')
                        ->wherePivotIn('role', [
                            UserRole::CompanyOwner->value,
                            UserRole::ProjectManager->value,
                        ])
                        ->value('users.id')
                    : $request->user()->id);

            if (! $managerId) {
                throw ValidationException::withMessages([
                    'project_manager_id' => 'Select an active company owner or project manager.',
                ]);
            }

            $members = [];
            foreach ($request->validated('members', []) as $member) {
                $members[$member['user_id']] = [
                    'role' => $member['role'],
                ];
            }
            $members[(int) $managerId] = [
                'role' => UserRole::ProjectManager->value,
            ];

            $project->members()->sync($members);

            return $project;
        });

        return to_route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    public function edit(Request $request, Project $project): Response
    {
        $this->authorize('update', $project);

        $project->load('company:id,name,currency');
        $canManageFinancials = $request->user()->can('manageFinance', $project)
            || $request->user()->can('approveBudget', $project);

        return Inertia::render('Projects/Edit', [
            'project' => $project,
            'company' => $project->company->only('id', 'name', 'currency'),
            'canManageCompany' => $request->user()->can('manage', $project->company),
            'canManageFinancials' => $canManageFinancials,
            'owners' => $project->company->users()
                ->wherePivot('status', 'active')
                ->wherePivot('role', UserRole::CompanyOwner->value)
                ->orderBy('name')
                ->get(['users.id', 'users.name']),
            'projectManagers' => $project->company->users()
                ->wherePivot('status', 'active')
                ->wherePivotIn('role', [
                    UserRole::CompanyOwner->value,
                    UserRole::ProjectManager->value,
                ])
                ->orderBy('name')
                ->get(['users.id', 'users.name']),
            'statuses' => array_map(
                static fn ($status): array => [
                    'value' => $status->value,
                    'label' => $status->label(),
                ],
                \App\Enums\ProjectStatus::cases(),
            ),
        ]);
    }

    public function update(
        UpdateProjectRequest $request,
        Project $project,
    ): RedirectResponse {
        DB::transaction(function () use ($request, $project): void {
            $data = $request->validated();
            $oldManagerId = $project->project_manager_id;
            $newManagerId = $data['project_manager_id'] ?? $oldManagerId;

            $project->update($data);

            if ($newManagerId && (int) $newManagerId !== (int) $oldManagerId) {
                if ($oldManagerId && $project->members()
                    ->whereKey($oldManagerId)
                    ->wherePivot('role', UserRole::ProjectManager->value)
                    ->exists()) {
                    $project->members()->detach($oldManagerId);
                }

                $project->members()->syncWithoutDetaching([
                    (int) $newManagerId => ['role' => UserRole::ProjectManager->value],
                ]);
            }
        });

        return to_route('projects.show', $project)
            ->with('success', 'Project details updated successfully.');
    }

    public function show(Request $request, Project $project): Response
    {
        $this->authorize('view', $project);

        $project->load([
            'company:id,name,currency',
            'members:id,name,email',
        ]);

        $canViewFinancials = $request->user()->can('viewFinancials', $project);
        $projectData = $project->toArray();

        if (! $canViewFinancials) {
            unset(
                $projectData['original_budget'],
                $projectData['approved_revised_budget'],
                $projectData['contract_value'],
                $projectData['payment_terms'],
            );
        }

        $canManageTeam = $request->user()->can('manageTeam', $project);

        return Inertia::render('Projects/Show', [
            'project' => $projectData,
            'canViewFinancials' => $canViewFinancials,
            'canUpdateProject' => $request->user()->can('update', $project),
            'canManageTeam' => $canManageTeam,
            'canManageBoq' => $request->user()->can('manageBoq', $project),
            'canRecordSiteActivity' => $request->user()->can('recordSiteActivity', $project),
            'canManageInventory' => $request->user()->can('manageInventory', $project),
            'canManageFinance' => $request->user()->can('manageFinance', $project),
            'companyUsers' => $canManageTeam
                ? $project->company->users()
                    ->wherePivot('status', 'active')
                    ->orderBy('name')
                    ->get(['users.id', 'users.name', 'users.email'])
                    ->map(fn (User $member): array => [
                        'id' => $member->id,
                        'name' => $member->name,
                        'email' => $member->email,
                        'company_role' => $member->pivot->role,
                    ])
                : [],
            'projectRoles' => array_values(array_map(
                static fn (UserRole $role): array => [
                    'value' => $role->value,
                    'label' => $role->label(),
                ],
                array_filter(
                    UserRole::cases(),
                    static fn (UserRole $role): bool => in_array(
                        $role->value,
                        UserRole::projectValues(),
                        true,
                    ),
                ),
            )),
            'owner' => $project->owner?->only('id', 'name'),
            'projectManager' => $project->projectManager?->only('id', 'name'),
            'members' => $project->members->map(fn (User $member): array => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $canManageTeam ? $member->email : null,
                'role' => $member->pivot->role,
            ]),
            'activities' => $project->activities()->orderBy('planned_start_date')->orderBy('name')->get(),
            'milestones' => $project->milestones()->orderBy('due_date')->orderBy('name')->get(),
            'warehouses' => $project->warehouses()->orderBy('name')->get(),
            'documents' => $project->documents()
                ->when(! $canViewFinancials, fn ($query) => $query->where('visibility', 'project'))
                ->with('uploader:id,name')
                ->latest()
                ->get()
                ->map(fn ($document): array => [
                    'id' => $document->id,
                    'name' => $document->name,
                    'category' => $document->category,
                    'visibility' => $document->visibility,
                    'original_name' => $document->original_name,
                    'mime_type' => $document->mime_type,
                    'size' => $document->size,
                    'uploaded_by' => $document->uploader->name,
                    'created_at' => $document->created_at->toDateTimeString(),
                ]),
        ]);
    }
}
