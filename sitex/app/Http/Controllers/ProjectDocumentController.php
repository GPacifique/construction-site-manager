<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectDocumentRequest;
use App\Models\Project;
use App\Models\ProjectDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ProjectDocumentController extends Controller
{
    public function store(
        StoreProjectDocumentRequest $request,
        Project $project,
    ): RedirectResponse {
        $file = $request->file('file');
        $extension = $file->extension();
        $path = $file->storeAs(
            "projects/{$project->id}/documents",
            Str::uuid().($extension ? ".{$extension}" : ''),
            'local',
        );

        if (! $path) {
            throw new \RuntimeException('The project document could not be stored.');
        }

        $originalName = basename(str_replace('\\', '/', $file->getClientOriginalName()));

        try {
            DB::transaction(fn () => $project->documents()->create([
                'uploaded_by' => $request->user()->id,
                'name' => $request->validated('name'),
                'category' => $request->validated('category'),
                'visibility' => $request->validated('visibility'),
                'disk' => 'local',
                'path' => $path,
                'original_name' => mb_substr($originalName, 0, 255),
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'size' => $file->getSize(),
            ]));
        } catch (Throwable $exception) {
            if (! Storage::disk('local')->delete($path)) {
                Log::error('Failed to remove project document after database write failure.', [
                    'project_id' => $project->id,
                    'path' => $path,
                ]);
            }

            throw $exception;
        }

        return back()->with('success', 'Project document uploaded successfully.');
    }

    public function download(
        Project $project,
        ProjectDocument $document,
    ): StreamedResponse {
        abort_unless($document->project_id === $project->id, 404);
        $this->authorize('view', $project);

        if ($document->visibility === 'financial') {
            $this->authorize('viewFinancials', $project);
        }

        abort_unless(Storage::disk($document->disk)->exists($document->path), 404);

        return Storage::disk($document->disk)->download(
            $document->path,
            $document->original_name,
        );
    }
}
