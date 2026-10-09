<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SparePart;
use App\Models\User;

class SparePartPolicy
{
    /**
     * Any signed-in user may browse the spare parts catalogue.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Only administrators may add spare parts to the catalogue.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only administrators may modify spare parts or replenish stock.
     */
    public function update(User $user, SparePart $sparePart): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only administrators may delete spare parts.
     */
    public function delete(User $user, SparePart $sparePart): bool
    {
        return $user->isAdmin();
    }
}
