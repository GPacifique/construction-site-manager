<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyMemberController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectTeamController;
use App\Http\Controllers\ConstructionActivityController;
use App\Http\Controllers\ProjectMilestoneController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\ProjectDocumentController;
use App\Http\Controllers\ProfileController;
use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'featuredProjects' => Project::query()
            ->where('showcase_public', true)
            ->whereIn('status', [
                ProjectStatus::Active->value,
                ProjectStatus::Completed->value,
            ])
            ->latest('updated_at')
            ->limit(8)
            ->get(['id', 'name', 'location', 'construction_type', 'status']),
    ]);
});

Route::get('/dashboard', fn () => to_route('companies.index'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/{company}', [CompanyController::class, 'show'])->name('companies.show');
    Route::post('/companies/{company}/members', [CompanyMemberController::class, 'store'])
        ->name('companies.members.store');
    Route::get('/companies/{company}/projects/create', [ProjectController::class, 'create'])
        ->name('companies.projects.create');
    Route::post('/companies/{company}/projects', [ProjectController::class, 'store'])
        ->name('companies.projects.store');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])
        ->name('projects.edit');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])
        ->name('projects.update');
    Route::put('/projects/{project}/team', [ProjectTeamController::class, 'update'])
        ->name('projects.team.update');
    Route::post('/projects/{project}/activities', [ConstructionActivityController::class, 'store'])
        ->name('projects.activities.store');
    Route::patch('/projects/{project}/activities/{activity}', [
        ConstructionActivityController::class,
        'updateProgress',
    ])->name('projects.activities.progress');
    Route::post('/projects/{project}/milestones', [ProjectMilestoneController::class, 'store'])
        ->name('projects.milestones.store');
    Route::patch('/projects/{project}/milestones/{milestone}', [
        ProjectMilestoneController::class,
        'update',
    ])->name('projects.milestones.update');
    Route::post('/projects/{project}/warehouses', [WarehouseController::class, 'store'])
        ->name('projects.warehouses.store');
    Route::post('/projects/{project}/documents', [ProjectDocumentController::class, 'store'])
        ->name('projects.documents.store');
    Route::get('/projects/{project}/documents/{document}', [
        ProjectDocumentController::class,
        'download',
    ])->name('projects.documents.download');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
