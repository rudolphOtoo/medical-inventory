<?php

namespace App\Http\Controllers;

use App\Models\ClinicalNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    /**
     * Store a newly created clinical sticky note.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'body' => ['required', 'string', 'max:1000'],
            'color' => ['required', 'string', 'in:canary,mint,azure,coral,lavender'],
            'tags' => ['nullable', 'string'],
            'is_pinned' => ['nullable', 'boolean'],
            'department_id' => ['nullable', 'integer'],
            'equipment_id' => ['nullable', 'integer'],
        ]);

        $tagsArray = [];
        if (! empty($validated['tags'])) {
            $tagsArray = array_filter(array_map('trim', explode(',', $validated['tags'])));
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
