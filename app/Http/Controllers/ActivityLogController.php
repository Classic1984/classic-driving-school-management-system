<?php

namespace App\Http\Controllers;

use App\Console\Commands\RecordSchedulerHeartbeat;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
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
     * Periods the log can be filtered by, applied to when the action was
     * recorded. Defaults to all_time - an audit trail is more often
     * reviewed for a specific past incident than "just today", so the
     * unfiltered view stays the default even though this filter exists.
     */
    protected const PERIODS = ['today', 'week', 'month', 'year', 'all_time'];

    protected const LABELS = [
        'today' => 'Today',
        'week' => 'This Week',
        'month' => 'This Month',
        'year' => 'This Year',
        'all_time' => 'All Time',
    ];

    /**
     * Director-only "who did what and when" trail across the system's
     * everyday actions.
     */
    public function index(Request $request): View
    {
        $period = $this->period($request);
        $activityLogs = $this->query($period)->with('user')->latest()->paginate(20)->withQueryString();
        $label = self::LABELS[$period];
        $schedulerStatus = $this->schedulerStatus();

        return view('activity-logs.index', compact('activityLogs', 'schedulerStatus', 'period', 'label'));
    }

    protected function period(Request $request): string
    {
        $period = $request->query('period', 'all_time');

        return in_array($period, self::PERIODS, true) ? $period : 'all_time';
    }

    protected function query(string $period)
    {
        $query = ActivityLog::query();

        [$from, $to] = match ($period) {
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            'all_time' => [null, null],
            default => [today(), today()],
        };

        if ($from !== null) {
            $query->whereDate('created_at', '>=', $from->toDateString())
                ->whereDate('created_at', '<=', $to->toDateString());
        }

        return $query;
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
