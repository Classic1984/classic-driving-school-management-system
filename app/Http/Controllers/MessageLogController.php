<?php

namespace App\Http\Controllers;

use App\Models\MessageLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MessageLogController extends Controller
{
    protected const SORTABLE_COLUMNS = ['created_at', 'recipient_name', 'purpose', 'status'];

    protected const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    /**
     * Director-only log of every automatic SMS/WhatsApp reminder the
     * system has attempted to send, so staff can confirm what actually
     * went out (and to whom) instead of just trusting the schedule ran.
     */
    public function index(Request $request): View
    {
        $messageLogs = $this->query($request)
            ->paginate($this->perPage($request), ['*'], 'page')
            ->appends($request->query());

        return view('message-logs.index', [
            'messageLogs' => $messageLogs,
            'sort' => $this->sort($request),
            'direction' => $this->direction($request),
            'perPage' => $this->perPage($request),
        ]);
    }

    /**
     * Download a CSV export of the same filtered/sorted list (every
     * matching row, not just the current page).
     */
    public function export(Request $request): StreamedResponse
    {
        $logs = $this->query($request)->get();

        return response()->streamDownload(function () use ($logs) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Date & Time', 'Recipient', 'Recipient Type', 'Purpose', 'Status', 'Channel', 'Message']);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->created_at->format('Y-m-d H:i'),
                    $log->recipient_name,
                    $log->recipient_type,
                    MessageLog::PURPOSES[$log->purpose] ?? $log->purpose,
                    $log->status,
                    $log->channel ?? '',
                    $log->message ?? '',
                ]);
            }

            fclose($handle);
        }, 'message-delivery-log.csv', ['Content-Type' => 'text/csv']);
    }

    protected function query(Request $request): Builder
    {
        $query = MessageLog::query();

        if ($search = $request->query('search')) {
            $query->where('recipient_name', 'like', "%{$search}%");
        }

        if ($purpose = $request->query('purpose')) {
            $query->where('purpose', $purpose);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($dateFrom = $request->query('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->query('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return $query->orderBy($this->sort($request), $this->direction($request));
    }

    protected function sort(Request $request): string
    {
        $sort = $request->query('sort', 'created_at');

        return in_array($sort, self::SORTABLE_COLUMNS, true) ? $sort : 'created_at';
    }

    protected function direction(Request $request): string
    {
        return $request->query('direction') === 'asc' ? 'asc' : 'desc';
    }

    protected function perPage(Request $request): int
    {
        $perPage = (int) $request->query('per_page', 10);

        return in_array($perPage, self::PER_PAGE_OPTIONS, true) ? $perPage : 10;
    }
}
