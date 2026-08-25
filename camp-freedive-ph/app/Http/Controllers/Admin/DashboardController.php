<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
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
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
            'active_coaches' => User::where('role', 'coach')->where('status', 'active')->count(),
            'total_users' => User::count(),
        ];

        $recentBookings = Booking::with('participants', 'payments')
            ->latest()
            ->take(5)
            ->get();

        $recentAuditLogs = AuditLog::latest('created_at')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('user', 'stats', 'recentBookings', 'recentAuditLogs'));
    }
}
