<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\ResetUserPasswordRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\User;
use App\Support\FuzzySearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a filtered, paginated directory of staff accounts (Admin only).
     */
    public function index(Request $request): View
    {
        $this->authorize('manage-users');

        $departments = Department::orderBy('name')->get();
        $roles = UserRole::cases();

        $users = User::with('department')
            ->when($request->filled('search'), function ($q) use ($request) {
                FuzzySearch::apply($q, [
                    'name',
                    'email',
                    'department.name',
                    'department.code',
                ], (string) $request->string('search'));
            })
            ->when($request->filled('department_id') && $request->department_id !== 'all', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            })
            ->when($request->filled('role') && $request->role !== 'all', function ($q) use ($request) {
                $q->where('role', $request->role);
            })
            ->when($request->filled('status') && in_array($request->status, ['active', 'inactive'], true), function ($q) use ($request) {
                $q->where('is_active', $request->status === 'active');
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.users.index', compact('users', 'departments', 'roles'));
    }

    /**
     * Create a new staff account (Admin only).
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->requireDepartmentForStaff($validated['role'], $validated['department_id'] ?? null);

        $user = DB::transaction(function () use ($request, $validated): User {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'department_id' => $validated['department_id'] ?? null,
                'password' => $validated['password'],
                'email_verified_at' => now(),
            ]);

            ActivityLog::record(
                $request->user(),
                'user.created',
                "Created {$user->role->label()} account for {$user->name} ({$user->email})",
                $user
            );

            return $user;
        });

        return redirect()->route('users.index')->with('success', "User '{$user->name}' created successfully.");
    }

    /**
     * Update an existing staff account (Admin only).
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $this->requireDepartmentForStaff($validated['role'], $validated['department_id'] ?? $user->department_id);

        if (array_key_exists('department_id', $validated)) {
            $validated['department_id'] ??= null;
        }

        DB::transaction(function () use ($request, $user, $validated): void {
            $user->update($validated);

            ActivityLog::record(
                $request->user(),
                'user.updated',
                "Updated account for {$user->name} ({$user->email})",
                $user
            );
        });

        return back()->with('success', "User '{$user->name}' updated successfully.");
    }

    /**
     * Toggle a user between active and inactive (Admin only).
     */
    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage-users');

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->is_active = ! $user->is_active;

        $state = $user->is_active ? 'activated' : 'deactivated';
        $stateLabel = $user->is_active ? 'active' : 'inactive';

        DB::transaction(function () use ($request, $user, $state): void {
            $user->save();

            ActivityLog::record(
                $request->user(),
                "user.{$state}",
                "{$state} account for {$user->name} ({$user->email})",
                $user
            );
        });

        return back()->with('success', "User '{$user->name}' has been {$stateLabel}.");
    }

    /**
     * Force reset a user's password to an admin-provided value (Admin only).
     */
    public function resetPassword(ResetUserPasswordRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($request, $user, $validated): void {
            $user->update(['password' => $validated['password']]);

            ActivityLog::record(
                $request->user(),
                'user.password_reset',
                "Reset password for {$user->name} ({$user->email})",
                $user
            );
        });

        return back()->with('success', "Password for '{$user->name}' has been reset successfully.");
    }

    /**
     * Generate a secure temporary password for a user (Admin only).
     */
    public function generateTemporaryPassword(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage-users');

        $temporary = Str::password(16);

        DB::transaction(function () use ($request, $user, $temporary): void {
            $user->update(['password' => $temporary]);

            ActivityLog::record(
                $request->user(),
                'user.temporary_password_generated',
                "Generated a temporary password for {$user->name} ({$user->email})",
                $user
            );
        });

        return back()->with('success', "Temporary password for '{$user->name}' was generated. Share it securely: {$temporary}");
    }

    /**
     * Enforce a department assignment for departmental staff accounts.
     */
    protected function requireDepartmentForStaff(string $role, ?int $departmentId): void
    {
        if ($role === UserRole::DepartmentUser->value && blank($departmentId)) {
            throw ValidationException::withMessages([
                'department_id' => 'A department is required for department staff accounts.',
            ]);
        }
    }
}
