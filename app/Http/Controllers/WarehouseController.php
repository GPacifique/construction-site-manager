<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWarehouseRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;

class WarehouseController extends Controller
{
    public function store(
        StoreWarehouseRequest $request,
        Project $project,
    ): RedirectResponse {
        $project->warehouses()->create([
            ...$request->validated(),
            'company_id' => $project->company_id,
            'is_active' => true,
        ]);

        return back()->with('success', 'Project warehouse added successfully.');
    }
}
