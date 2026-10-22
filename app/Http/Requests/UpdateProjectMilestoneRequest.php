<?php

namespace App\Http\Requests;

use App\Models\Project;
use App\Models\ProjectMilestone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectMilestoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');
        $milestone = $this->route('milestone');

        return $project instanceof Project
            && $milestone instanceof ProjectMilestone
            && $milestone->project_id === $project->id
            && ($this->user()->can('update', $project)
                || $this->user()->can('recordSiteActivity', $project));
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed', 'delayed'])],
            'completed_at' => ['nullable', 'date'],
        ];
    }
}
