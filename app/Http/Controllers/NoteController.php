<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreClinicalNoteRequest;
use App\Http\Requests\UpdateClinicalNoteRequest;
use App\Models\ClinicalNote;
use App\Models\Equipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    /**
     * Store a newly created clinical sticky note.
     */
    public function store(StoreClinicalNoteRequest $request): RedirectResponse
    {
        $this->authorize('create', ClinicalNote::class);

        $user = $request->user();

        $validated = $request->validated();

        $tagsArray = [];
        if (! empty($validated['tags'])) {
            $tagsArray = array_values(array_filter(array_map('trim', explode(',', $validated['tags']))));
        }

        $departmentId = $user->department_id;
        if ($user->isAdmin() && ! empty($validated['department_id'])) {
            $departmentId = (int) $validated['department_id'];
        }

        $equipmentId = null;
        if (! empty($validated['equipment_id'])) {
            $equipment = Equipment::query()
                ->whereKey((int) $validated['equipment_id'])
                ->when(! $user->isAdmin(), fn ($query) => $query->where('department_id', $departmentId))
                ->first();

            $equipmentId = $equipment?->id;
        }

        ClinicalNote::create([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'color' => $validated['color'],
            'tags' => $tagsArray,
            'is_pinned' => $request->boolean('is_pinned'),
            'author_id' => $user->id,
            'department_id' => $departmentId,
            'equipment_id' => $equipmentId,
        ]);

        return back()->with('success', 'Clinical memo pinned successfully.');
    }

    /**
     * Update an existing clinical sticky note.
     */
    public function update(UpdateClinicalNoteRequest $request, ClinicalNote $note): RedirectResponse
    {
        $this->authorize('update', $note);

        $validated = $request->validated();

        $tagsArray = [];
        if (! empty($validated['tags'])) {
            $tagsArray = array_values(array_filter(array_map('trim', explode(',', $validated['tags']))));
        }

        $note->update([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'color' => $validated['color'],
            'tags' => $tagsArray,
            'is_pinned' => $request->has('is_pinned') ? $request->boolean('is_pinned') : $note->is_pinned,
        ]);

        return back()->with('success', 'Clinical memo updated successfully.');
    }

    /**
     * Toggle the pinned status of a sticky note.
     */
    public function togglePin(Request $request, ClinicalNote $note): RedirectResponse
    {
        $this->authorize('update', $note);

        $note->update([
            'is_pinned' => ! $note->is_pinned,
        ]);

        $status = $note->is_pinned ? 'pinned' : 'unpinned';

        return back()->with('success', "Clinical memo {$status}.");
    }

    /**
     * Delete a sticky note.
     */
    public function destroy(Request $request, ClinicalNote $note): RedirectResponse
    {
        $this->authorize('delete', $note);

        $note->delete();

        return back()->with('success', 'Clinical note removed.');
    }
}
