<?php

namespace App\Http\Controllers;

use App\Models\MessageLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageLogController extends Controller
{
    /**
     * Director-only log of every automatic SMS/WhatsApp reminder the
     * system has attempted to send, so staff can confirm what actually
     * went out (and to whom) instead of just trusting the schedule ran.
     */
    public function index(Request $request): View
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

        $messageLogs = $query->latest()->paginate(20)->appends($request->query());

        return view('message-logs.index', compact('messageLogs'));
    }
}
