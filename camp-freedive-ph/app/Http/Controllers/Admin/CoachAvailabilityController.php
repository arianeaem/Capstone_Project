<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use App\Models\CoachAvailability;
use App\Models\CoachBlackoutDate;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoachAvailabilityController extends Controller
{
    /**
     * Update weekly recurring availability for a coach.
     */
    public function updateRecurring(Request $request, Coach $coach): RedirectResponse
    {
        $currentUser = Auth::user();
        $days = $request->input('days', []); // Array of day_of_week checked

        for ($d = 0; $d <= 6; $d++) {
            $isAvailable = in_array((string) $d, (array) $days);
            CoachAvailability::updateOrCreate(
                ['coach_id' => $coach->id, 'day_of_week' => $d],
                ['is_available' => $isAvailable]
            );
        }

        AuditLogger::log(
            'COACH_AVAILABILITY_UPDATED',
            "Weekly availability updated for {$coach->full_name} by {$currentUser->name}",
            $currentUser,
            $currentUser->name,
            $request
        );

        return back()->with('success', "Weekly availability schedule saved for {$coach->full_name}.");
    }

    /**
     * Add a blackout date for a coach.
     */
    public function storeBlackout(Request $request, Coach $coach): RedirectResponse
    {
        $currentUser = Auth::user();

        $validated = $request->validate([
            'blackout_date' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        // Prevent duplicate blackout date
        $exists = CoachBlackoutDate::where('coach_id', $coach->id)
            ->whereDate('blackout_date', $validated['blackout_date'])
            ->exists();

        if ($exists) {
            return back()->with('error', "{$validated['blackout_date']} is already registered as a blackout date.");
        }

        CoachBlackoutDate::create([
            'coach_id' => $coach->id,
            'blackout_date' => $validated['blackout_date'],
            'reason' => $validated['reason'] ?? null,
        ]);

        AuditLogger::log(
            'COACH_BLACKOUT_ADDED',
            "Blackout date ({$validated['blackout_date']}) added for {$coach->full_name} by {$currentUser->name}",
            $currentUser,
            $currentUser->name,
            $request
        );

        return back()->with('success', "Blackout date added successfully.");
    }

    /**
     * Remove a blackout date.
     */
    public function destroyBlackout(Request $request, Coach $coach, CoachBlackoutDate $blackout): RedirectResponse
    {
        $currentUser = Auth::user();
        $dateStr = $blackout->blackout_date->format('M d, Y');

        $blackout->delete();

        AuditLogger::log(
            'COACH_BLACKOUT_REMOVED',
            "Blackout date ({$dateStr}) removed for {$coach->full_name} by {$currentUser->name}",
            $currentUser,
            $currentUser->name,
            $request
        );

        return back()->with('success', "Blackout date {$dateStr} removed.");
    }
}
