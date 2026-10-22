<?php

namespace App\Http\Requests;

use App\Enums\ProjectStatus;
use App\Enums\UserRole;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project instanceof Project
            && $this->user()->can('update', $project);
    }

    public function rules(): array
    {
        /** @var Project $project */
        $project = $this->route('project');
        $canManageCompany = $this->user()->can('manage', $project->company);
        $canManageFinancials = $this->user()->can('manageFinance', $project)
            || $this->user()->can('approveBudget', $project);

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('projects', 'code')
                    ->where('company_id', $project->company_id)
                    ->ignore($project->id),
            ],
            'client_name' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'construction_type' => ['nullable', 'string', 'max:100'],
            'start_date' => ['nullable', 'date'],
            'expected_completion_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],
            'status' => ['required', Rule::in(ProjectStatus::values())],
            'owner_id' => [
                Rule::prohibitedIf(! $canManageCompany),
                'sometimes',
                'integer',
                Rule::exists('company_user', 'user_id')
                    ->where('company_id', $project->company_id)
                    ->where('status', 'active')
                    ->where('role', UserRole::CompanyOwner->value),
            ],
            'project_manager_id' => [
                'required',
                'integer',
                Rule::exists('company_user', 'user_id')
                    ->where('company_id', $project->company_id)
                    ->where('status', 'active')
                    ->whereIn('role', [
                        UserRole::CompanyOwner->value,
                        UserRole::ProjectManager->value,
                    ]),
            ],
            'contract_value' => [
                Rule::prohibitedIf(! $canManageFinancials),
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
                'decimal:0,2',
            ],
            'payment_terms' => [
                Rule::prohibitedIf(! $canManageFinancials),
                'sometimes',
                'nullable',
                'string',
                'max:5000',
            ],
            'showcase_public' => [
                Rule::prohibitedIf(! $canManageCompany),
                'sometimes',
                'boolean',
            ],
        ];
    }
}
