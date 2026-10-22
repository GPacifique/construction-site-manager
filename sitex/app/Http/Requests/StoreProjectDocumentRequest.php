<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');

        if (! $project instanceof Project) {
            return false;
        }

        if ($this->input('visibility') === 'financial') {
            return $this->user()->can('manageFinance', $project)
                || $this->user()->can('approveBudget', $project);
        }

        return $this->user()->can('update', $project)
            || $this->user()->can('manageBoq', $project)
            || $this->user()->can('manageFinance', $project);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => [
                'required',
                Rule::in(['drawing', 'contract', 'permit', 'photo', 'report', 'other']),
            ],
            'visibility' => ['required', Rule::in(['project', 'financial'])],
            'file' => [
                'required',
                'file',
                'max:20480',
                'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx',
            ],
        ];
    }
}
