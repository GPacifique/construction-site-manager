<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectMilestoneRequest;
use App\Http\Requests\UpdateProjectMilestoneRequest;
use App\Models\Project;
use App\Models\ProjectMilestone;
use Illuminate\Http\RedirectResponse;

class ProjectMilestoneController extends Controller
{
    public function store(
        StoreProjectMilestoneRequest $request,
        Project $project,
    ): RedirectResponse {
        $project->milestones()->create($request->validated() + ['status' => 'pending']);

        return back()->with('success', 'Project milestone added successfully.');
    }

    public function update(
        UpdateProjectMilestoneRequest $request,
        Project $project,
        ProjectMilestone $milestone,
    ): RedirectResponse {
        $milestone->update($request->validated());

        return back()->with('success', 'Project milestone updated successfully.');
    }
}
