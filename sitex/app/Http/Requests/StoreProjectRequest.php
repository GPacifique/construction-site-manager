<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        $company = $this->route('company');

        return $company instanceof Company
            && $this->user()->can('createProject', $company);
    }

    public function rules(): array
    {
        /** @var Company $company */
        $company = $this->route('company');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('projects', 'code')->where('company_id', $company->id),
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
            'original_budget' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'contract_value' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'owner_id' => [
                'nullable',
                'integer',
                Rule::exists('company_user', 'user_id')
                    ->where('company_id', $company->id)
                    ->where('status', 'active')
                    ->where('role', UserRole::CompanyOwner->value),
            ],
            'showcase_public' => [
                Rule::prohibitedIf(! $this->user()->can('manage', $company)),
                'sometimes',
                'boolean',
            ],
            'payment_terms' => ['nullable', 'string', 'max:5000'],
            'project_manager_id' => [
                'nullable',
                'integer',
                Rule::exists('company_user', 'user_id')
                    ->where('company_id', $company->id)
                    ->where('status', 'active')
                    ->whereIn('role', [
                        UserRole::CompanyOwner->value,
                        UserRole::ProjectManager->value,
                    ]),
            ],
            'members' => ['sometimes', 'array'],
            'members.*.user_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('company_user', 'user_id')
                    ->where('company_id', $company->id)
                    ->where('status', 'active'),
            ],
            'members.*.role' => [
                'required',
                Rule::in(UserRole::projectValues()),
            ],
        ];
    }

    public function projectManagerId(): ?int
    {
        $id = $this->validated('project_manager_id');

        return $id === null ? null : (int) $id;
    }

    public function ownerId(): ?int
    {
        $id = $this->validated('owner_id');

        return $id === null ? null : (int) $id;
    }
}
