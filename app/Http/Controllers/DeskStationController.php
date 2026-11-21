<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeskStationRequest;
use App\Models\ActivityLog;
use App\Models\DeskStation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Director-only management of desk/camera stations - each one is a
 * single desk (front desk, secretary's office, ...) whose on-site agent
 * reports "occupied" or "empty" by pinging that station's token. CDSMS
 * only ever receives that ping; it never talks to the camera or DVR.
 */
class DeskStationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $stations = DeskStation::orderBy('name')->get();

        return view('desk-stations.index', compact('stations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('desk-stations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDeskStationRequest $request): RedirectResponse
    {
        $station = DeskStation::create($request->validated());

        ActivityLog::record("Added a desk station ({$station->name})");

        return Redirect::route('desk-stations.index')->with('status', 'station-created');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeskStation $deskStation): RedirectResponse
    {
        $name = $deskStation->name;
        $deskStation->delete();

        ActivityLog::record("Deleted a desk station ({$name})");

        return Redirect::route('desk-stations.index')->with('status', 'station-deleted');
    }

    /**
     * Rotate a station's token - e.g. if it's been exposed, or the
     * on-site agent needs to be reconfigured from scratch.
     */
    public function regenerateToken(DeskStation $deskStation): RedirectResponse
    {
        $deskStation->forceFill(['api_token' => Str::random(48)])->save();

        ActivityLog::record("Regenerated the token for a desk station ({$deskStation->name})");

        return Redirect::route('desk-stations.index')->with('status', 'token-regenerated');
    }

    /**
     * Live status for every station, polled by the index page so the
     * grid updates without a full page reload.
     */
    public function status(): JsonResponse
    {
        $stations = DeskStation::orderBy('name')->get()->map(fn (DeskStation $station) => [
            'id' => $station->id,
            'display_status' => $station->displayStatus(),
            'last_seen_at' => $station->last_seen_at?->diffForHumans(),
        ]);

        return response()->json(['stations' => $stations]);
    }
}
