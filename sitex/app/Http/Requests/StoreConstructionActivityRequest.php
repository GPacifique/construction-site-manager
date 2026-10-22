<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConstructionActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $project instanceof Project
            && ($this->user()->can('manageBoq', $project)
                || $this->user()->can('update', $project));
    }

    public function rules(): array
    {
        /** @var Project $project */
        $project = $this->route('project');

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('construction_activities', 'code')
                    ->where('project_id', $project->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'unit' => ['nullable', 'string', 'max:50'],
            'planned_quantity' => ['nullable', 'numeric', 'min:0', 'decimal:0,4'],
            'planned_start_date' => ['nullable', 'date'],
            'planned_end_date' => ['nullable', 'date', 'after_or_equal:planned_start_date'],
        ];
    }
}
