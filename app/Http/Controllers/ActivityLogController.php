<?php

namespace App\Http\Controllers;

use App\Console\Commands\RecordSchedulerHeartbeat;
use App\Models\ActivityLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * How stale the last heartbeat can be before the background scheduler
     * is considered not running, rather than just between ticks. The
     * scheduler runs on a dedicated Railway Cron Job service that wakes up
     * every 15 minutes (Railway has no finer-grained cron interval), so
     * this allows a full interval plus a buffer for a slow-starting tick.
     */
    protected const STALE_AFTER_MINUTES = 20;

    /**
     * Director-only "who did what and when" trail across the system's
     * everyday actions.
     */
    public function index(): View
    {
        $activityLogs = ActivityLog::with('user')->latest()->paginate(20);
        $schedulerStatus = $this->schedulerStatus();

        return view('activity-logs.index', compact('activityLogs', 'schedulerStatus'));
    }

    /**
     * Whether the background scheduler process (which drives every
     * automatic reminder and the database backup) appears to actually be
     * running in this environment, judged by how recently its heartbeat
     * job last ticked.
     *
     * @return array{state: string, last_seen_at: ?Carbon}
     */
    protected function schedulerStatus(): array
    {
        $lastSeenAt = Cache::get(RecordSchedulerHeartbeat::CACHE_KEY);

        $state = match (true) {
            $lastSeenAt === null => 'never',
            $lastSeenAt->diffInMinutes(now()) > self::STALE_AFTER_MINUTES => 'stale',
            default => 'running',
        };

        return ['state' => $state, 'last_seen_at' => $lastSeenAt];
    }
}
