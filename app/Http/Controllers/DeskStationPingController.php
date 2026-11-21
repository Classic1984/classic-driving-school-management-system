<?php

namespace App\Http\Controllers;

use App\Models\DeskStation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Where the on-site camera agent reports in - no login, no session; the
 * station's own token in the URL is the only credential. Deliberately
 * outside the dashboard's auth group (see routes/web.php), the same way
 * the Paystack webhook is.
 */
class DeskStationPingController extends Controller
{
    public function update(Request $request, string $token): JsonResponse
    {
        $station = DeskStation::where('api_token', $token)->first();

        if ($station === null) {
            return response()->json(['message' => 'Unknown station.'], 404);
        }

        // Validated manually (rather than a FormRequest) so a non-browser
        // caller without an "Accept: application/json" header still gets
        // a JSON error back instead of a redirect it has no use for.
        try {
            $validated = $request->validate([
                'status' => ['required', 'string', 'in:occupied,empty'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Invalid status.', 'errors' => $e->errors()], 422);
        }

        $station->recordPing($validated['status']);

        return response()->json(['ok' => true]);
    }
}
