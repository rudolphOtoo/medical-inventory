<?php

namespace App\Http\Controllers;

use App\Enums\DepartmentStatus;
use App\Models\ActivityLog;
use App\Models\Department;
use App\Support\FuzzySearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    /**
     * Display a listing of hospital departments (Admin only).
     */
    public function index(Request $request): View
    {
        $this->authorize('manage-departments');

        $departments = Department::withCount(['equipment', 'activeEquipment', 'issues', 'staff'])
            ->when($request->filled('search'), function ($q) use ($request) {
                FuzzySearch::apply($q, [
                    'name',
                    'code',
                    'head_of_department',
                    'floor',
                    'contact_number',
                    'description',
                ], $request->string('search'));
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('pages.departments.index', compact('departments'));
    }

    /**
     * Store a newly created department (Admin only).
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('manage-departments');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:departments,name'],
            'code' => ['required', 'string', 'max:10', 'uppercase', 'unique:departments,code'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['sometimes', Rule::enum(DepartmentStatus::class)],
            'floor' => ['nullable', 'string', 'max:100'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'head_of_department' => ['nullable', 'string', 'max:100'],
        ]);

        $department = Department::create($validated);

        ActivityLog::record(
            $request->user(),
            'department.created',
            "Created hospital department: {$department->name} [{$department->code}]",
            $department
        );

        return redirect()->route('departments.index')->with('success', "Department '{$department->name}' created successfully.");
    }

    /**
     * Update an existing hospital department (Admin only).
     */
    public function update(Request $request, Department $department): RedirectResponse
    {
        $this->authorize('manage-departments');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('departments', 'name')->ignore($department->id)],
            'code' => ['required', 'string', 'max:10', 'uppercase', Rule::unique('departments', 'code')->ignore($department->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['sometimes', Rule::enum(DepartmentStatus::class)],
            'floor' => ['nullable', 'string', 'max:100'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'head_of_department' => ['nullable', 'string', 'max:100'],
        ]);

        $department->update($validated);

        ActivityLog::record(
            $request->user(),
            'department.updated',
            "Updated hospital department: {$department->name} [{$department->code}]",
            $department
        );

        return back()->with('success', "Department '{$department->name}' updated successfully.");
    }

    /**
     * Toggle a department between active and inactive (Admin only).
     */
    public function toggleStatus(Request $request, Department $department): RedirectResponse
    {
        $this->authorize('manage-departments');

        $department->status = $department->isActive()
            ? DepartmentStatus::Inactive
            : DepartmentStatus::Active;
        $department->save();

        $state = $department->isActive() ? 'activated' : 'deactivated';
        $stateLabel = $department->isActive() ? 'active' : 'inactive';

        ActivityLog::record(
            $request->user(),
            "department.{$state}",
            "{$state} hospital department: {$department->name} [{$department->code}]",
            $department
        );

        return back()->with('success', "Department '{$department->name}' has been {$stateLabel}.");
    }

    /**
     * Delete a hospital department with active equipment safeguards (Admin only).
     */
    public function destroy(Request $request, Department $department): RedirectResponse
    {
        $this->authorize('manage-departments');

        $equipmentCount = $department->equipment()->count();
        if ($equipmentCount > 0) {
            return back()->with('error', "Cannot delete '{$department->name}' while {$equipmentCount} medical device(s) are assigned to it. Please re-allocate or transfer the equipment first.");
        }

        $staffCount = $department->staff()->count();
        if ($staffCount > 0) {
            return back()->with('error', "Cannot delete '{$department->name}' while {$staffCount} hospital staff member(s) are assigned to it.");
        }

        $name = $department->name;
        $code = $department->code;

        ActivityLog::record(
            $request->user(),
            'department.deleted',
            "Deleted hospital department: {$name} [{$code}]"
        );

        $department->delete();

        return back()->with('success', "Department '{$name}' [{$code}] has been deleted successfully.");
    }
}
