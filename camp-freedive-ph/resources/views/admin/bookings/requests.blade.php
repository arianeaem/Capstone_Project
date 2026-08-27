@extends('layouts.admin')

@section('title', 'Pending Guest Requests | Camp FreedivePH')

@section('content')
<div class="space-y-8 text-sm">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-5">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.bookings.index') }}" class="text-xs text-[#6E6E73] hover:text-[#1D1D1F] flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                    <span>Back to Bookings</span>
                </a>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight mt-1">Pending Guest Requests</h1>
            <p class="text-xs sm:text-sm text-[#6E6E73] mt-1">
                Review and approve reschedule or cancellation requests submitted by guests.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.bookings.index') }}" class="btn-secondary px-4 py-2.5 text-xs sm:text-sm font-semibold">
                View All Bookings
            </a>
        </div>
    </div>

    <!-- Section 1: Pending Reschedule Requests -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="font-extrabold text-base text-[#1D1D1F] flex items-center gap-2">
                <span>🔄 Reschedule Requests</span>
                @if(count($pendingReschedules) > 0)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-amber-500 text-white">
                        {{ count($pendingReschedules) }} Pending
                    </span>
                @endif
            </h2>
        </div>

        @forelse($pendingReschedules as $req)
        @php
            $booking = $req->booking;
        @endphp
        <div class="bg-white rounded-2xl border border-[#E5E5EA] p-5 sm:p-6 space-y-4 shadow-2xs">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 border-b border-[#E5E5EA] pb-4">
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <a href="{{ route('admin.bookings.show', $booking) }}" class="font-mono font-extrabold text-base text-[#780000] hover:underline">
                            {{ $booking->booking_number }}
                        </a>
                        <span class="text-xs text-[#6E6E73]">• Lead Guest: <strong class="text-[#1D1D1F]">{{ $booking->contact_name }}</strong> ({{ $booking->contact_phone }})</span>
                    </div>
                    <p class="text-xs text-[#6E6E73] mt-1">
                        Submitted: {{ $req->requested_at ? $req->requested_at->format('M d, Y g:i A') : $req->created_at->format('M d, Y') }}
                    </p>
                </div>

                <div class="text-right">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                        Pending Staff Review
                    </span>
                </div>
            </div>

            <!-- Date Comparison -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] text-xs">
                <div>
                    <span class="text-[#6E6E73] block mb-1">Current Booked Dates:</span>
                    <strong class="text-[#1D1D1F] text-sm">{{ $booking->start_date->format('M d, Y') }} – {{ $booking->end_date->format('M d, Y') }}</strong>
                </div>
                <div>
                    <span class="text-[#6E6E73] block mb-1">Requested New Dates:</span>
                    <strong class="text-emerald-700 text-sm">{{ $req->requested_start_date->format('M d, Y') }} – {{ $req->requested_end_date->format('M d, Y') }}</strong>
                </div>
            </div>

            @if($req->reason)
                <p class="text-xs text-[#6E6E73] italic">
                    Reason: "{{ $req->reason }}"
                </p>
            @endif

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-2.5 pt-2">
                <form action="{{ route('admin.bookings.requests.reschedule.reject', $req) }}" method="POST">
                    @csrf
                    <button type="submit" 
                            onclick="return confirm('Reject this reschedule request?')"
                            class="px-4 py-2 rounded-xl border border-[#D1D1D6] hover:bg-[#F2F2F7] text-xs font-bold text-[#6E6E73] transition-colors">
                        Reject Request
                    </button>
                </form>

                <form action="{{ route('admin.bookings.requests.reschedule.approve', $req) }}" method="POST">
                    @csrf
                    <button type="submit" 
                            onclick="return confirm('Approve reschedule to {{ $req->requested_start_date->format('M d, Y') }}?')"
                            class="btn-primary px-6 py-2 text-xs font-bold shadow-sm">
                        Approve Reschedule
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-[#E5E5EA] p-6 text-center text-xs text-[#8E8E93]">
            No pending guest reschedule requests.
        </div>
        @endforelse
    </div>

    <!-- Section 2: Pending Cancellation Requests -->
    <div class="space-y-4 pt-4">
        <div class="flex items-center justify-between">
            <h2 class="font-extrabold text-base text-[#1D1D1F] flex items-center gap-2">
                <span>✕ Cancellation Requests</span>
                @if(count($pendingCancellations) > 0)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-[#FF3B3C] text-white">
                        {{ count($pendingCancellations) }} Pending
                    </span>
                @endif
            </h2>
        </div>

        @forelse($pendingCancellations as $req)
        @php
            $booking = $req->booking;
        @endphp
        <div class="bg-white rounded-2xl border border-[#E5E5EA] p-5 sm:p-6 space-y-4 shadow-2xs">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 border-b border-[#E5E5EA] pb-4">
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <a href="{{ route('admin.bookings.show', $booking) }}" class="font-mono font-extrabold text-base text-[#780000] hover:underline">
                            {{ $booking->booking_number }}
                        </a>
                        <span class="text-xs text-[#6E6E73]">• Lead Guest: <strong class="text-[#1D1D1F]">{{ $booking->contact_name }}</strong></span>
                    </div>
                    <p class="text-xs text-[#6E6E73] mt-1">
                        Trip Date: <strong class="text-[#1D1D1F]">{{ $booking->start_date->format('M d, Y') }}</strong> • Paid: ₱{{ number_format($booking->paid_amount, 2) }}
                    </p>
                </div>

                <div class="text-right">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-800 border border-rose-200">
                        Cancellation Claim
                    </span>
                </div>
            </div>

            @if($req->reason)
                <div class="p-3.5 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] text-xs text-[#6E6E73]">
                    <strong>Reason for Cancellation:</strong> "{{ $req->reason }}"
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-2.5 pt-2">
                <form action="{{ route('admin.bookings.requests.cancellation.reject', $req) }}" method="POST">
                    @csrf
                    <button type="submit" 
                            onclick="return confirm('Reject this cancellation request?')"
                            class="px-4 py-2 rounded-xl border border-[#D1D1D6] hover:bg-[#F2F2F7] text-xs font-bold text-[#6E6E73] transition-colors">
                        Reject Request
                    </button>
                </form>

                <form action="{{ route('admin.bookings.requests.cancellation.approve', $req) }}" method="POST">
                    @csrf
                    <button type="submit" 
                            onclick="return confirm('Approve cancellation for booking {{ $booking->booking_number }}?')"
                            class="px-6 py-2 rounded-xl bg-[#FF3B3C] hover:bg-[#D32F2F] text-white text-xs font-bold shadow-sm transition-colors">
                        Approve Cancellation
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-[#E5E5EA] p-6 text-center text-xs text-[#8E8E93]">
            No pending guest cancellation requests.
        </div>
        @endforelse
    </div>

</div>
@endsection
