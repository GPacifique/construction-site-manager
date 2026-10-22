<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $company = $this->route('company');

        return $company instanceof Company
            && $this->user()->can('manage', $company);
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::exists('users', 'email')->whereNotNull('email_verified_at'),
            ],
            'role' => ['required', Rule::in(UserRole::projectValues())],
        ];
    }
}
