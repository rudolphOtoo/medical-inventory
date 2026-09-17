<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\User;
use App\Support\FuzzySearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
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
                ], $request->string('search'));
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
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage-users');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'password' => ['required', 'string', Password::defaults()],
        ]);

        $this->requireDepartmentForStaff($validated['role'], $validated['department_id'] ?? null);

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

        return redirect()->route('users.index')->with('success', "User '{$user->name}' created successfully.");
    }

    /**
     * Update an existing staff account (Admin only).
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage-users');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::enum(UserRole::class)],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $this->requireDepartmentForStaff($validated['role'], $validated['department_id'] ?? $user->department_id);

        if (array_key_exists('department_id', $validated)) {
            $validated['department_id'] ??= null;
        }

        $user->update($validated);

        ActivityLog::record(
            $request->user(),
            'user.updated',
            "Updated account for {$user->name} ({$user->email})",
            $user
        );

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
        $user->save();

        $state = $user->is_active ? 'activated' : 'deactivated';
        $stateLabel = $user->is_active ? 'active' : 'inactive';

        ActivityLog::record(
            $request->user(),
            "user.{$state}",
            "{$state} account for {$user->name} ({$user->email})",
            $user
        );

        return back()->with('success', "User '{$user->name}' has been {$stateLabel}.");
    }

    /**
     * Force reset a user's password to an admin-provided value (Admin only).
     */
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage-users');

        $validated = $request->validate([
            'password' => ['required', 'string', Password::defaults()],
        ]);

        $user->update(['password' => $validated['password']]);

        ActivityLog::record(
            $request->user(),
            'user.password_reset',
            "Reset password for {$user->name} ({$user->email})",
            $user
        );

        return back()->with('success', "Password for '{$user->name}' has been reset successfully.");
    }

    /**
     * Generate a secure temporary password for a user (Admin only).
     */
    public function generateTemporaryPassword(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage-users');

        $temporary = Str::password(16);

        $user->update(['password' => $temporary]);

        ActivityLog::record(
            $request->user(),
            'user.temporary_password_generated',
            "Generated a temporary password for {$user->name} ({$user->email})",
            $user
        );

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
