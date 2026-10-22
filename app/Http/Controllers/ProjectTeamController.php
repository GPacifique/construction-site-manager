<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\SyncProjectTeamRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectTeamController extends Controller
{
    public function update(
        SyncProjectTeamRequest $request,
        Project $project,
    ): RedirectResponse {
        $managerId = (int) $request->validated('project_manager_id');
        $members = [];

        foreach ($request->validated('members') as $member) {
            $members[(int) $member['user_id']] = ['role' => $member['role']];
        }

        if (isset($members[$managerId])
            && $members[$managerId]['role'] !== UserRole::ProjectManager->value) {
            throw ValidationException::withMessages([
                'project_manager_id' => 'The selected project manager cannot have a different project role.',
            ]);
        }

        $members[$managerId] = ['role' => UserRole::ProjectManager->value];

        DB::transaction(function () use ($project, $managerId, $members): void {
            $project->update(['project_manager_id' => $managerId]);
            $project->members()->sync($members);
        });

        return back()->with('success', 'Project team updated successfully.');
    }
}
