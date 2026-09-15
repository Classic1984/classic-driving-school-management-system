<?php

namespace App\Http\Controllers;

use App\Console\Commands\RecordSchedulerHeartbeat;
use App\Models\ActivityLog;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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
        $date = $this->exactDate($request);
        $category = $this->category($request);

        $activityLogs = $this->applyCategory($this->query($period, $date), $category)
            ->with('user')->latest()->paginate(20)->withQueryString();

        $label = self::LABELS[$period];
        $schedulerStatus = $this->schedulerStatus();

        // Every tile's count is scoped to the currently selected Period/
        // Date (so picking "This Month" re-counts every category for that
        // month), but never to the currently selected category itself -
        // otherwise every other tile would show 0 the moment one was
        // active, instead of letting a director switch straight from one
        // category to another.
        $categoryTiles = collect(ActivityLog::categories())
            ->push(ActivityLog::otherCategory())
            ->map(fn (array $category) => [
                ...$category,
                'count' => $this->applyCategory($this->query($period, $date), $category['key'])->count(),
            ]);

        // A standalone shortcut to "everything from today" regardless of
        // whatever Period/Date/Category is currently selected - the
        // fastest way back to the most common thing a director checks.
        $todayCount = $this->query('today', null)->count();

        // The breakdown always covers exactly one calendar day - the
        // Specific Date filter when one is picked, otherwise today - since
        // "how much came in, by category" only reads cleanly for a single
        // day; a director wanting a different day already has the date
        // picker right above this for it.
        $breakdownDate = $date ?? today()->toDateString();
        $dailyBreakdown = $this->revenueByCategory($breakdownDate);
        $dailyBreakdownTotal = $dailyBreakdown->sum();

        return view('activity-logs.index', compact(
            'activityLogs', 'schedulerStatus', 'period', 'date', 'label', 'category', 'categoryTiles', 'todayCount',
            'breakdownDate', 'dailyBreakdown', 'dailyBreakdownTotal'
        ));
    }

    /**
     * Money actually collected on one calendar day, grouped by the same
     * category buckets as the Financial Reports "Revenue by Service"
     * table (Training, Driver's License Processing, Learner's Permit,
     * Online Certificate, Student Certificate, Reactivation Fee, etc.) -
     * fully automatic from the underlying Payment/PaymentAllocation
     * records, never a manually maintained figure, and reversed payments
     * are excluded the same way they already are there.
     *
     * @return Collection<string, float>
     */
    protected function revenueByCategory(string $date): Collection
    {
        return Payment::where('status', 'paid')
            ->whereDate('payment_date', $date)
            ->with(['allocations.studentService.service'])
            ->get()
            ->flatMap(fn (Payment $payment) => $payment->allocations)
            ->groupBy(fn (PaymentAllocation $allocation) => $allocation->categoryLabel())
            ->map(fn (Collection $group) => (float) $group->sum('amount'))
            ->sortDesc();
    }

    protected function period(Request $request): string
    {
        $period = $request->query('period', 'all_time');

        return in_array($period, self::PERIODS, true) ? $period : 'all_time';
    }

    /**
     * A specific calendar date to filter by, if the request named a valid
     * one - takes priority over the relative Period filter when present,
     * so a director can look up exactly what happened on, say, Jan 1st
     * instead of only ever browsing in today/week/month/year buckets.
     */
    protected function exactDate(Request $request): ?string
    {
        $date = $request->query('date');

        return ($date && \DateTime::createFromFormat('Y-m-d', $date) !== false) ? $date : null;
    }

    /**
     * The category tile to filter by, if the request named a known one -
     * combines with Period/Date rather than overriding them, so a director
     * can view e.g. "Driver's License, this month" together.
     */
    protected function category(Request $request): ?string
    {
        $category = $request->query('category');
        $knownKeys = collect(ActivityLog::categories())->push(ActivityLog::otherCategory())->pluck('key');

        return $knownKeys->contains($category) ? $category : null;
    }

    /**
     * Narrow a query to one category, rebuilding ActivityLog::category()'s
     * keyword-matching order as SQL: match this category's own keywords,
     * excluding every keyword belonging to a category checked earlier (the
     * "other" category, checked last of all, excludes every keyword there
     * is) - so a tile's count and its filtered list always agree with
     * what ActivityLog::category() would report for the same row, and an
     * entry is never double-counted across two tiles.
     */
    protected function applyCategory($query, ?string $categoryKey)
    {
        if ($categoryKey === null) {
            return $query;
        }

        $matchKeywords = null;
        $priorKeywords = [];

        foreach (ActivityLog::categories() as $category) {
            if ($category['key'] === $categoryKey) {
                $matchKeywords = $category['keywords'];
                break;
            }

            $priorKeywords = [...$priorKeywords, ...$category['keywords']];
        }

        if ($categoryKey === 'other') {
            $priorKeywords = collect(ActivityLog::categories())->flatMap(fn (array $category) => $category['keywords'])->all();
        }

        if ($matchKeywords !== null) {
            $query->where(function ($inner) use ($matchKeywords) {
                foreach ($matchKeywords as $keyword) {
                    $inner->orWhere('description', 'like', "%{$keyword}%");
                }
            });
        }

        foreach ($priorKeywords as $keyword) {
            $query->where('description', 'not like', "%{$keyword}%");
        }

        return $query;
    }

    protected function query(string $period, ?string $date = null)
    {
        $query = ActivityLog::query();

        if ($date !== null) {
            $query->whereDate('created_at', $date);

            return $query;
        }

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
