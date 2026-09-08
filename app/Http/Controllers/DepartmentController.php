<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Department;
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
        if (! $request->user()->isAdmin()) {
            abort(403, 'Restricted to hospital administrators.');
        }

        $departments = Department::withCount(['equipment', 'activeEquipment', 'issues', 'staff'])
            ->orderBy('name')
            ->get();

        return view('pages.departments.index', compact('departments'));
    }

    /**
     * Store a newly created department (Admin only).
     */
    public function store(Request $request): RedirectResponse
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Only hospital administrators can create departments.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:departments,name'],
            'code' => ['required', 'string', 'max:10', 'uppercase', 'unique:departments,code'],
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

        return back()->with('success', "Department '{$department->name}' created successfully.");
    }

    /**
     * Update an existing hospital department (Admin only).
     */
    public function update(Request $request, Department $department): RedirectResponse
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Only hospital administrators can edit departments.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('departments', 'name')->ignore($department->id)],
            'code' => ['required', 'string', 'max:10', 'uppercase', Rule::unique('departments', 'code')->ignore($department->id)],
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
     * Delete a hospital department with active equipment safeguards (Admin only).
     */
    public function destroy(Request $request, Department $department): RedirectResponse
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Only hospital administrators can delete departments.');
        }

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
