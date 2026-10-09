<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreClinicalNoteRequest;
use App\Http\Requests\UpdateClinicalNoteRequest;
use App\Models\ClinicalNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    /**
     * Store a newly created clinical sticky note.
     */
    public function store(StoreClinicalNoteRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $tagsArray = [];
        if (! empty($validated['tags'])) {
            $tagsArray = array_values(array_filter(array_map('trim', explode(',', $validated['tags']))));
        }

        ClinicalNote::create([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'color' => $validated['color'],
            'tags' => $tagsArray,
            'is_pinned' => $request->boolean('is_pinned'),
            'author_id' => $request->user()->id,
            'department_id' => $validated['department_id'] ?? $request->user()->department_id,
            'equipment_id' => $validated['equipment_id'] ?? null,
        ]);

        return back()->with('success', 'Clinical memo pinned successfully.');
    }

    /**
     * Update an existing clinical sticky note.
     */
    public function update(UpdateClinicalNoteRequest $request, ClinicalNote $note): RedirectResponse
    {
        $user = $request->user();

        // Admin or Author can edit note
        if (! $user->isAdmin() && $note->author_id !== $user->id) {
            abort(403, 'Unauthorized to edit this note.');
        }

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
        $user = $request->user();

        // Admin or Author can toggle pin
        if (! $user->isAdmin() && $note->author_id !== $user->id) {
            abort(403, 'Unauthorized to pin this note.');
        }

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
        // Admin or Author can delete note
        if (! $request->user()->isAdmin() && $note->author_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $note->delete();

        return back()->with('success', 'Clinical note removed.');
    }
}
