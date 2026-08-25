<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use App\Models\CoachAvailability;
use App\Models\DeactivationRequest;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CoachController extends Controller
{
    /**
     * Display a listing of coaches roster.
     */
    public function index(Request $request): View
    {
        $query = Coach::with(['activeAssignments.batch'])->latest('created_at');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('certification_level', 'like', "%{$search}%")
                  ->orWhere('certification_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('cert_state')) {
            $state = $request->input('cert_state');
            if ($state === 'expired') {
                $query->whereDate('certification_expiry', '<', now()->startOfDay());
            } elseif ($state === 'expiring_soon') {
                $query->whereDate('certification_expiry', '>=', now()->startOfDay())
                      ->whereDate('certification_expiry', '<=', now()->addDays(30)->startOfDay());
            } elseif ($state === 'valid') {
                $query->whereDate('certification_expiry', '>', now()->addDays(30)->startOfDay());
            }
        }

        $perPage = max(5, min(100, (int) $request->input('per_page', 10)));
        $coaches = $query->paginate($perPage)->withQueryString();

        $stats = [
            'total' => Coach::count(),
            'active' => Coach::where('status', 'active')->count(),
            'expiring_soon' => Coach::whereDate('certification_expiry', '>=', now()->startOfDay())
                                    ->whereDate('certification_expiry', '<=', now()->addDays(30)->startOfDay())
                                    ->count(),
            'expired' => Coach::whereDate('certification_expiry', '<', now()->startOfDay())->count(),
            'pending_deactivations' => DeactivationRequest::where('status', 'pending')->count(),
        ];

        return view('admin.coaches.index', compact('coaches', 'stats'));
    }

    /**
     * Show coach creation form.
     */
    public function create(): View
    {
        return view('admin.coaches.create');
    }

    /**
     * Store a newly created coach record.
     */
    public function store(Request $request): RedirectResponse
    {
        $currentUser = Auth::user();

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:coaches,email'],
            'phone' => ['required', 'string', 'max:50'],
            'certification_level' => ['required', 'string', 'max:255'],
            'certification_number' => ['required', 'string', 'max:100'],
            'certification_expiry' => ['required', 'date'],
            'specialties_notes' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,on_leave,inactive'],
            'joined_at' => ['nullable', 'date'],
        ]);

        $coach = DB::transaction(function () use ($validated) {
            $coach = Coach::create([
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'certification_level' => $validated['certification_level'],
                'certification_number' => $validated['certification_number'],
                'certification_expiry' => $validated['certification_expiry'],
                'specialties_notes' => $validated['specialties_notes'] ?? null,
                'status' => $validated['status'],
                'joined_at' => $validated['joined_at'] ?? now(),
            ]);

            // Seed default weekly availability (Available all 7 days by default)
            for ($d = 0; $d <= 6; $d++) {
                CoachAvailability::create([
                    'coach_id' => $coach->id,
                    'day_of_week' => $d,
                    'is_available' => true,
                ]);
            }

            return $coach;
        });

        AuditLogger::log(
            'COACH_CREATED',
            "Coach profile created for {$coach->full_name} ({$coach->certification_level}) by {$currentUser->name}",
            $currentUser,
            $currentUser->name,
            $request
        );

        return redirect()->route('admin.coaches.show', $coach)
            ->with('success', "Coach {$coach->full_name} successfully added to the roster!");
    }

    /**
     * Display coach profile, availability, and assignments.
     */
    public function show(Coach $coach): View
    {
        $coach->load([
            'availabilities',
            'blackoutDates',
            'activeAssignments.batch',
            'assignments.batch',
            'deactivationRequests.requester',
            'deactivationRequests.resolver',
        ]);

        // Weekly availability mapped by day 0-6
        $availabilitiesMap = $coach->availabilities->keyBy('day_of_week');

        return view('admin.coaches.show', compact('coach', 'availabilitiesMap'));
    }

    /**
     * Show edit form for coach.
     */
    public function edit(Coach $coach): View
    {
        return view('admin.coaches.edit', compact('coach'));
    }

    /**
     * Update coach profile.
     */
    public function update(Request $request, Coach $coach): RedirectResponse
    {
        $currentUser = Auth::user();

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:coaches,email,' . $coach->id],
            'phone' => ['required', 'string', 'max:50'],
            'certification_level' => ['required', 'string', 'max:255'],
            'certification_number' => ['required', 'string', 'max:100'],
            'certification_expiry' => ['required', 'date'],
            'specialties_notes' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,on_leave,inactive,pending_deactivation'],
            'joined_at' => ['nullable', 'date'],
        ]);

        $coach->update($validated);

        AuditLogger::log(
            'COACH_UPDATED',
            "Coach profile updated for {$coach->full_name} by {$currentUser->name}",
            $currentUser,
            $currentUser->name,
            $request
        );

        return redirect()->route('admin.coaches.show', $coach)
            ->with('success', "Coach profile for {$coach->full_name} updated successfully.");
    }

    /**
     * Soft delete coach record (Owner only).
     */
    public function destroy(Request $request, Coach $coach): RedirectResponse
    {
        $currentUser = Auth::user();

        if (!$currentUser->isOwner()) {
            abort(403, 'Unauthorized. Only the Camp Owner can delete a coach record.');
        }

        $name = $coach->full_name;
        $coach->delete();

        AuditLogger::log(
            'COACH_DELETED',
            "Coach {$name} soft-deleted by Camp Owner {$currentUser->name}",
            $currentUser,
            $currentUser->name,
            $request
        );

        return redirect()->route('admin.coaches.index')
            ->with('success', "Coach {$name} has been removed from the roster.");
    }
}
