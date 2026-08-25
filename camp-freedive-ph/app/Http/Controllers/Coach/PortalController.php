<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalController extends Controller
{
    /**
     * Show the Coach Portal dashboard.
     */
    public function index(): View
    {
        $coach = Auth::user();

        // Sample upcoming assigned batches and students
        $upcomingBookings = Booking::with('participants')
            ->where('start_date', '>=', now())
            ->where('status', 'confirmed')
            ->orderBy('start_date')
            ->take(6)
            ->get();

        return view('coach.dashboard', compact('coach', 'upcomingBookings'));
    }
}
