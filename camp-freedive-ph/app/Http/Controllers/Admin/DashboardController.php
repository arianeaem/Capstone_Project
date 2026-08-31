<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssignmentReleaseRequest;
use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\Booking;
use App\Models\BookingParticipant;
use App\Models\Payment;
use App\Models\RefundRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the Admin & Owner operational dashboard.
     */
    public function index(): View
    {
        $user = Auth::user();

        $stats = [
            'total_bookings' => Booking::count(),
            'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
            'pending_reschedules' => Booking::where('status', 'reschedule_requested')->count(),
            'pending_cancellations' => Booking::where('status', 'cancellation_requested')->count(),
            'pending_refunds' => RefundRequest::where('status', 'pending')->count(),
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
            'active_coaches' => User::where('role', 'coach')->where('status', 'active')->count(),
            'total_users' => User::count(),
            'unmatched_students' => BookingParticipant::whereHas('booking', fn($q) => $q->where('status', 'confirmed'))->whereDoesntHave('assignment')->count(),
            'pending_releases' => AssignmentReleaseRequest::where('status', 'pending')->count(),
        ];

        // Next upcoming batch
        $nextBatch = Batch::where('start_date', '>=', Carbon::now('Asia/Manila')->startOfDay())
            ->whereIn('status', ['confirmed', 'open'])
            ->orderBy('start_date', 'asc')
            ->with(['bookings.participants', 'coachAssignments.coach', 'riskAssessment'])
            ->first();

        $recentBookings = Booking::with('participants', 'payments')
            ->latest()
            ->take(5)
            ->get();

        $recentAuditLogs = AuditLog::latest('created_at')
            ->take(6)
            ->get();

        return view('admin.dashboard', compact('user', 'stats', 'nextBatch', 'recentBookings', 'recentAuditLogs'));
    }
}

