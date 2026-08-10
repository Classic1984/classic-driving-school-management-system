<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * Director-only "who did what and when" trail across the system's
     * everyday actions.
     */
    public function index(): View
    {
        $activityLogs = ActivityLog::with('user')->latest()->paginate(20);

        return view('activity-logs.index', compact('activityLogs'));
    }
}
