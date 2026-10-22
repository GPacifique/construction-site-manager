<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConstructionActivityRequest;
use App\Http\Requests\UpdateActivityProgressRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;

class ConstructionActivityController extends Controller
{
    public function store(
        StoreConstructionActivityRequest $request,
        Project $project,
    ): RedirectResponse {
        $project->activities()->create($request->validated() + [
            'completed_quantity' => '0.0000',
            'status' => 'not_started',
        ]);

        return back()->with('success', 'Project activity added successfully.');
    }

    public function updateProgress(
        UpdateActivityProgressRequest $request,
        Project $project,
        int $activity,
    ): RedirectResponse {
        $project->activities()
            ->whereKey($activity)
            ->firstOrFail()
            ->update($request->validated());

        return back()->with('success', 'Activity progress updated successfully.');
    }
}
