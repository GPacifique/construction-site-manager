<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncProjectTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project instanceof Project
            && $this->user()->can('manageTeam', $project);
    }

    public function rules(): array
    {
        /** @var Project $project */
        $project = $this->route('project');

        return [
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
            'members' => ['present', 'array'],
            'members.*.user_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('company_user', 'user_id')
                    ->where('company_id', $project->company_id)
                    ->where('status', 'active'),
            ],
            'members.*.role' => [
                'required',
                Rule::in(UserRole::projectValues()),
            ],
        ];
    }
}
