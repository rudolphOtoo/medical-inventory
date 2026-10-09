<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\EquipmentStatus;
use App\Http\Requests\StoreEquipmentRequest;
use App\Http\Requests\TransferEquipmentRequest;
use App\Http\Requests\UpdateEquipmentCalibrationRequest;
use App\Http\Requests\UpdateEquipmentRequest;
use App\Http\Requests\UpdateEquipmentStatusRequest;
use App\Http\Requests\UploadEquipmentAttachmentRequest;
use App\Models\Department;
use App\Models\Equipment;
use App\Services\EquipmentService;
use App\Services\QrCodeService;
use App\Support\FuzzySearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class EquipmentController extends Controller
{
    public function __construct(private readonly EquipmentService $equipmentService) {}

    /**
     * Display a listing of medical equipment.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $statuses = EquipmentStatus::cases();
        $departments = Department::orderBy('name')->get();

        $query = Equipment::with(['department', 'issues' => fn ($q) => $q->open()])
            ->forUser($user)
            ->active()
            ->when($request->filled('search'), function ($q) use ($request) {
                FuzzySearch::apply($q, [
                    'name',
                    'asset_tag',
                    'serial_number',
                    'manufacturer',
                    'model_number',
                    'department.name',
                ], (string) $request->string('search'));
            })
            ->when($request->filled('status') && $request->status !== 'all', function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->when($request->filled('department_id') && $request->department_id !== 'all' && $user->isAdmin(), function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            })
            ->when($request->filled('calibration_status') && $request->calibration_status !== 'all', function ($q) use ($request) {
                $q->calibrationStatus($request->calibration_status);
            })
            ->latest();

        $equipmentList = $query->paginate(15)->withQueryString();

        return view('pages.equipment.index', compact('equipmentList', 'statuses', 'departments'));
    }

    /**
     * Store a newly created equipment in storage.
     */
    public function store(StoreEquipmentRequest $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validated();

        // If not admin, restrict to own department
        if (! $user->isAdmin()) {
            $validated['department_id'] = $user->department_id;
        }

        $equipment = $this->equipmentService->register(
            $validated,
            $user,
            $this->uploadedFile($request, 'photo'),
            $this->uploadedFile($request, 'manual'),
        );

        return redirect()->route('equipment.show', $equipment)->with('success', "Equipment '{$equipment->name}' registered successfully.");
    }

    /**
     * Display the specified equipment details.
     */
    public function show(Request $request, Equipment $equipment): View
    {
        if (! $this->canAccess($request, $equipment)) {
            abort(403, 'Unauthorized device access.');
        }

        $equipment->load(['department', 'creator', 'issues.reporter', 'issues.assignee', 'clinicalNotes.author']);
        $statuses = EquipmentStatus::cases();
        $departments = Department::orderBy('name')->get();

        return view('pages.equipment.show', compact('equipment', 'statuses', 'departments'));
    }

    /**
     * Upload photo or user manual attachments.
     */
    public function uploadAttachment(UploadEquipmentAttachmentRequest $request, Equipment $equipment): RedirectResponse
    {
        if (! $this->canAccess($request, $equipment)) {
            abort(403, 'Unauthorized.');
        }

        $this->equipmentService->uploadAttachments(
            $equipment,
            $request->user(),
            $this->uploadedFile($request, 'photo'),
            $this->uploadedFile($request, 'manual'),
        );

        return back()->with('success', 'Attachments updated successfully.');
    }

    /**
     * Delete an attachment (photo or manual).
     */
    public function deleteAttachment(Request $request, Equipment $equipment, string $type): RedirectResponse
    {
        if (! $this->canAccess($request, $equipment)) {
            abort(403, 'Unauthorized.');
        }

        $this->equipmentService->removeAttachment($equipment, $request->user(), $type);

        return back()->with('success', ucfirst($type).' removed successfully.');
    }

    /**
     * Update equipment calibration dates.
     */
    public function updateCalibration(UpdateEquipmentCalibrationRequest $request, Equipment $equipment): RedirectResponse
    {
        if (! $this->canAccess($request, $equipment)) {
            abort(403, 'Unauthorized.');
        }

        $this->equipmentService->recordCalibration($equipment, $request->validated(), $request->user());

        return back()->with('success', 'Calibration certification saved successfully.');
    }

    /**
     * Transfer equipment to a different department.
     */
    public function transferDepartment(TransferEquipmentRequest $request, Equipment $equipment): RedirectResponse
    {
        if (! $this->canAccess($request, $equipment)) {
            abort(403, 'Unauthorized to transfer this equipment.');
        }

        $validated = $request->validated();

        $newDepartment = $this->equipmentService->transfer(
            $equipment,
            $request->user(),
            (int) $validated['department_id'],
            $validated['location'] ?? null,
        );

        return back()->with('success', "Equipment successfully transferred to {$newDepartment->name}.");
    }

    /**
     * Render printable asset tag and QR label.
     */
    public function printTag(Request $request, Equipment $equipment): View
    {
        if (! $this->canAccess($request, $equipment)) {
            abort(403, 'Unauthorized.');
        }

        $tagUrl = route('equipment.show', $equipment);
        $qrSvg = QrCodeService::svg($tagUrl, 160);

        return view('pages.equipment.tag', compact('equipment', 'qrSvg', 'tagUrl'));
    }

    /**
     * Update equipment operational status.
     */
    public function updateStatus(UpdateEquipmentStatusRequest $request, Equipment $equipment): RedirectResponse
    {
        if (! $this->canAccess($request, $equipment)) {
            abort(403, 'Unauthorized.');
        }

        $status = $this->resolveStatus($request->validated()['status']);

        $this->equipmentService->changeStatus($equipment, $status, $request->user());

        return back()->with('success', "Equipment status updated to '{$status->label()}'.");
    }

    /**
     * Toggle archive status.
     */
    public function toggleArchive(Request $request, Equipment $equipment): RedirectResponse
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Only administrators can archive equipment.');
        }

        $archived = $this->equipmentService->toggleArchive($equipment, $request->user());
        $state = $archived ? 'Archived' : 'Restored';

        return redirect()->route('equipment.index')->with('success', "Equipment '{$equipment->name}' {$state}.");
    }

    /**
     * Update technical specifications and equipment details.
     */
    public function update(UpdateEquipmentRequest $request, Equipment $equipment): RedirectResponse
    {
        if (! $this->canAccess($request, $equipment)) {
            abort(403, 'Unauthorized.');
        }

        $this->equipmentService->updateDetails($equipment, $request->validated(), $request->user());

        return back()->with('success', "Equipment '{$equipment->name}' specifications updated successfully.");
    }

    /**
     * Remove the specified equipment from storage (Admin only).
     */
    public function destroy(Request $request, Equipment $equipment): RedirectResponse
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Only administrators can permanently delete equipment.');
        }

        $name = $equipment->name;
        $tag = $equipment->asset_tag;

        $this->equipmentService->delete($equipment, $request->user());

        return redirect()->route('equipment.index')->with('success', "Equipment '{$name}' [{$tag}] was permanently deleted.");
    }

    /**
     * Determine whether the current user may act on the given device.
     */
    private function canAccess(Request $request, Equipment $equipment): bool
    {
        $user = $request->user();

        return $user->isAdmin() || $equipment->department_id === $user->department_id;
    }

    /**
     * Resolve the submitted status value into a typed enum.
     */
    private function resolveStatus(mixed $status): EquipmentStatus
    {
        return $status instanceof EquipmentStatus
            ? $status
            : EquipmentStatus::from((string) $status);
    }

    /**
     * Extract a single uploaded file from the request, ignoring arrays.
     */
    private function uploadedFile(Request $request, string $key): ?UploadedFile
    {
        $file = $request->file($key);

        return $file instanceof UploadedFile ? $file : null;
    }
}
