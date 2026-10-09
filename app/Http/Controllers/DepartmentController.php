<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\DepartmentStatus;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\ActivityLog;
use App\Models\Department;
use App\Support\FuzzySearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                ], (string) $request->string('search'));
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('pages.departments.index', compact('departments'));
    }

    /**
     * Store a newly created department (Admin only).
     */
    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $department = DB::transaction(function () use ($request, $validated): Department {
            $department = Department::create($validated);

            ActivityLog::record(
                $request->user(),
                'department.created',
                "Created hospital department: {$department->name} [{$department->code}]",
                $department
            );

            return $department;
        });

        return redirect()->route('departments.index')->with('success', "Department '{$department->name}' created successfully.");
    }

    /**
     * Update an existing hospital department (Admin only).
     */
    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($request, $department, $validated): void {
            $department->update($validated);

            ActivityLog::record(
                $request->user(),
                'department.updated',
                "Updated hospital department: {$department->name} [{$department->code}]",
                $department
            );
        });

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

        $state = $department->isActive() ? 'activated' : 'deactivated';
        $stateLabel = $department->isActive() ? 'active' : 'inactive';

        DB::transaction(function () use ($request, $department, $state): void {
            $department->save();

            ActivityLog::record(
                $request->user(),
                "department.{$state}",
                "{$state} hospital department: {$department->name} [{$department->code}]",
                $department
            );
        });

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

        DB::transaction(function () use ($request, $department, $name, $code): void {
            ActivityLog::record(
                $request->user(),
                'department.deleted',
                "Deleted hospital department: {$name} [{$code}]"
            );

            $department->delete();
        });

        return back()->with('success', "Department '{$name}' [{$code}] has been deleted successfully.");
    }
}
