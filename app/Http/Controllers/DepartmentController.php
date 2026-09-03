<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
}
