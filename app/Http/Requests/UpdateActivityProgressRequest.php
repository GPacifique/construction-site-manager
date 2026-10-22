<?php

namespace App\Http\Requests;

use App\Models\ConstructionActivity;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateActivityProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');
        $activity = $this->route('activity');

        return $project instanceof Project
            && $activity instanceof ConstructionActivity
            && $activity->project_id === $project->id
            && $this->user()->can('recordSiteActivity', $project);
    }

    public function rules(): array
    {
        return [
            'completed_quantity' => ['required', 'numeric', 'min:0', 'decimal:0,4'],
            'status' => [
                'required',
                Rule::in(['not_started', 'in_progress', 'on_hold', 'completed']),
            ],
            'actual_start_date' => ['nullable', 'date'],
            'actual_end_date' => [
                'nullable',
                'date',
                'after_or_equal:actual_start_date',
            ],
        ];
    }
}
