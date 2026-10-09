<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ClinicalNote;
use App\Models\User;

class ClinicalNotePolicy
{
    /**
     * Any signed-in user may create a clinical sticky note.
     */
    public function create(User $user): bool
    {
        return $user->isActive();
    }

    /**
     * The author of a note, or any administrator, may edit it.
     */
    public function update(User $user, ClinicalNote $note): bool
    {
        return $user->isAdmin() || $note->author_id === $user->id;
    }

    /**
     * The author of a note, or any administrator, may delete it.
     */
    public function delete(User $user, ClinicalNote $note): bool
    {
        return $user->isAdmin() || $note->author_id === $user->id;
    }
}
