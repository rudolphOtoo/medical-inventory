<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    /**
     * Display the hospital activity and audit trail.
     */
    public function index(Request $request): View
    {
        $activities = ActivityLog::with('causer')
            ->when($request->filled('event_type') && $request->event_type !== 'all', function ($q) use ($request) {
                $q->where('event_type', $request->event_type);
            })
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $eventTypes = ActivityLog::select('event_type')->distinct()->pluck('event_type');

        return view('pages.activity.index', compact('activities', 'eventTypes'));
    }
}
