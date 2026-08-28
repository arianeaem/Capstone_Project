@extends('layouts.admin')

@section('title', 'Pending Guest Requests | Camp FreedivePH')

@section('content')
<div class="space-y-8 text-sm" x-data="{
    rejectModalOpen: false,
    rejectFormAction: '',
    rejectBookingNumber: '',
    rejectRequestType: '',
    rejectNotes: '',
    openRejectModal(action, bookingNum, type) {
        this.rejectFormAction = action;
        this.rejectBookingNumber = bookingNum;
        this.rejectRequestType = type;
        this.rejectNotes = '';
        this.rejectModalOpen = true;
    }
}">
    
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
            <a href="{{ route('admin.payments.refunds') }}" class="btn-secondary px-4 py-2.5 text-xs sm:text-sm font-semibold flex items-center gap-1.5">
                <svg class="w-4 h-4 text-[#6E6E73]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                <span>View Pending Refunds</span>
            </a>
            <a href="{{ route('admin.bookings.index') }}" class="btn-secondary px-4 py-2.5 text-xs sm:text-sm font-semibold">
                View All Bookings
            </a>
        </div>
    </div>

    <!-- Section 1: Pending Reschedule Requests -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="font-extrabold text-base text-[#1D1D1F] flex items-center gap-2">
                <span>Reschedule Requests</span>
                @if(count($pendingReschedules) > 0)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-amber-500 text-white">
                        {{ count($pendingReschedules) }} Pending
                    </span>
                @endif
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($pendingReschedules as $req)
            @php
                $booking = $req->booking;
                $resPolicy = $reschedulePolicies[$req->id] ?? null;
            @endphp
            <div class="bg-white rounded-2xl border border-[#E5E5EA] p-4 sm:p-5 flex flex-col justify-between shadow-2xs hover:border-[#D1D1D6] transition-all gap-4">
                <!-- Header & Body -->
                <div>
                    <div class="flex items-start justify-between gap-2 border-b border-[#E5E5EA] pb-3">
                        <div class="min-w-0">
                            <a href="{{ route('admin.bookings.show', $booking) }}" class="font-mono font-extrabold text-sm text-[#780000] hover:underline block truncate">
                                {{ $booking->booking_number }}
                            </a>
                            <div class="text-xs font-bold text-[#1D1D1F] mt-0.5 truncate">{{ $booking->contact_name }}</div>
                            <div class="text-[11px] text-[#6E6E73] truncate">{{ $booking->contact_phone }}</div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200 shrink-0">
                            Reschedule
                        </span>
                    </div>

                    <!-- Date Shift Comparison Box -->
                    <div class="mt-3 space-y-2 p-3 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] text-xs">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[#6E6E73] text-[11px]">Current:</span>
                            <strong class="text-[#1D1D1F] font-semibold text-right">{{ $booking->start_date->format('M d') }} – {{ $booking->end_date->format('M d, Y') }}</strong>
                        </div>
                        <div class="flex items-center justify-between gap-2 border-t border-[#E5E5EA] pt-2">
                            <span class="text-emerald-700 font-semibold text-[11px]">Requested:</span>
                            <strong class="text-emerald-700 font-bold text-right">{{ $req->requested_start_date->format('M d') }} – {{ $req->requested_end_date->format('M d, Y') }}</strong>
                        </div>
                    </div>

                    @if($req->reason)
                        <div class="mt-3 text-xs text-[#6E6E73] bg-[#F9F9FB] p-2.5 rounded-xl border border-[#E5E5EA]">
                            <strong class="text-[#1D1D1F] block text-[11px] mb-0.5">Guest Reason:</strong>
                            <span class="italic text-[11px]">"{{ $req->reason }}"</span>
                        </div>
                    @endif

                    <div class="mt-2.5 text-[10px] text-[#8E8E93]">
                        Submitted: {{ $req->requested_at ? $req->requested_at->format('M d, Y g:i A') : $req->created_at->format('M d, Y') }}
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-2 pt-3 border-t border-[#E5E5EA]">
                    <button type="button" 
                            @click="openRejectModal('{{ route('admin.bookings.requests.reschedule.reject', $req) }}', '{{ $booking->booking_number }}', 'Reschedule')"
                            class="flex-1 py-2 rounded-xl border border-[#D1D1D6] hover:bg-[#F2F2F7] text-xs font-bold text-[#6E6E73] transition-colors text-center">
                        Reject
                    </button>

                    <form action="{{ route('admin.bookings.requests.reschedule.approve', $req) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" 
                                onclick="return confirm('Approve reschedule for {{ $booking->booking_number }} to {{ $req->requested_start_date->format('M d, Y') }}?')"
                                class="btn-primary w-full py-2 text-xs font-bold shadow-sm text-center">
                            Approve
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="col-span-full bg-white rounded-2xl border border-[#E5E5EA] p-6 text-center text-xs text-[#8E8E93]">
                No pending guest reschedule requests.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Section 2: Pending Cancellation Requests -->
    <div class="space-y-4 pt-4">
        <div class="flex items-center justify-between">
            <h2 class="font-extrabold text-base text-[#1D1D1F] flex items-center gap-2">
                <span>Cancellation Requests</span>
                @if(count($pendingCancellations) > 0)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-[#FF3B3C] text-white">
                        {{ count($pendingCancellations) }} Pending
                    </span>
                @endif
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($pendingCancellations as $req)
            @php
                $booking = $req->booking;
                $policy = $cancellationPolicies[$req->id] ?? null;
                $isFullRefund = ($policy && ($policy['refund_percentage'] ?? 0) === 100);
                $recRefund = $policy['calculated_refund'] ?? $req->calculated_refund_amount;
            @endphp
            <div class="bg-white rounded-2xl border border-[#E5E5EA] p-4 sm:p-5 flex flex-col justify-between shadow-2xs hover:border-[#D1D1D6] transition-all gap-4">
                <!-- Header & Body -->
                <div>
                    <div class="flex items-start justify-between gap-2 border-b border-[#E5E5EA] pb-3">
                        <div class="min-w-0">
                            <a href="{{ route('admin.bookings.show', $booking) }}" class="font-mono font-extrabold text-sm text-[#780000] hover:underline block truncate">
                                {{ $booking->booking_number }}
                            </a>
                            <div class="text-xs font-bold text-[#1D1D1F] mt-0.5 truncate">{{ $booking->contact_name }}</div>
                            <div class="text-[11px] text-[#6E6E73] truncate">{{ $booking->contact_email }}</div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-200 shrink-0">
                            Cancel Claim
                        </span>
                    </div>

                    <!-- Booking Details Box -->
                    <div class="mt-3 space-y-2 p-3 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] text-xs">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[#6E6E73] text-[11px]">Trip Dates:</span>
                            <strong class="text-[#1D1D1F] font-semibold text-right">{{ $booking->start_date->format('M d') }} – {{ $booking->end_date->format('M d, Y') }}</strong>
                        </div>
                        <div class="flex items-center justify-between gap-2 border-t border-[#E5E5EA] pt-2">
                            <span class="text-[#6E6E73] text-[11px]">Total Paid:</span>
                            <strong class="text-[#1D1D1F] font-bold text-right">₱{{ number_format($booking->paid_amount, 2) }}</strong>
                        </div>
                    </div>

                    <!-- Cancellation Policy Recommendation Banner -->
                    @if($policy)
                        <div class="mt-3 p-3 rounded-xl border text-xs {{ $isFullRefund ? 'bg-emerald-50/70 border-emerald-200 text-emerald-900' : 'bg-amber-50/70 border-amber-200 text-amber-900' }}">
                            <div class="flex items-center justify-between font-bold text-[11px]">
                                <span>Policy Recommendation:</span>
                                <span class="{{ $isFullRefund ? 'text-emerald-700 font-extrabold' : 'text-amber-800 font-extrabold' }}">
                                    {{ $isFullRefund ? '100% Refund (₱' . number_format($recRefund, 2) . ')' : '0% Refund (Forfeited)' }}
                                </span>
                            </div>
                            <p class="text-[11px] mt-1 opacity-80 leading-relaxed">
                                {{ $policy['cancel_message'] ?? 'Cancellation evaluated under standard policy.' }}
                            </p>
                        </div>
                    @endif

                    @if($req->reason)
                        <div class="mt-3 text-xs text-[#6E6E73] bg-[#F9F9FB] p-2.5 rounded-xl border border-[#E5E5EA]">
                            <strong class="text-[#1D1D1F] block text-[11px] mb-0.5">Guest Reason:</strong>
                            <span class="italic text-[11px]">"{{ $req->reason }}"</span>
                        </div>
                    @endif

                    <div class="mt-2.5 text-[10px] text-[#8E8E93]">
                        Submitted: {{ $req->requested_at ? $req->requested_at->format('M d, Y g:i A') : $req->created_at->format('M d, Y') }}
                    </div>
                </div>

                <!-- Action Buttons with Policy Choices -->
                <div class="space-y-2 pt-3 border-t border-[#E5E5EA]">
                    @if($isFullRefund)
                        <!-- If Policy gives full refund -->
                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    @click="openRejectModal('{{ route('admin.bookings.requests.cancellation.reject', $req) }}', '{{ $booking->booking_number }}', 'Cancellation')"
                                    class="py-2 px-3 rounded-xl border border-[#D1D1D6] hover:bg-[#F2F2F7] text-xs font-bold text-[#6E6E73] transition-colors text-center shrink-0">
                                Reject
                            </button>

                            <form action="{{ route('admin.bookings.requests.cancellation.approve', $req) }}" method="POST" class="flex-1">
                                @csrf
                                <input type="hidden" name="action_type" value="policy_refund">
                                <button type="submit" 
                                        onclick="return confirm('Approve cancellation & queue full refund of ₱{{ number_format($recRefund, 2) }} for {{ $booking->booking_number }}?')"
                                        class="w-full py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition-colors text-center">
                                    Approve & Queue Refund (₱{{ number_format($recRefund, 2) }})
                                </button>
                            </form>
                        </div>
                    @else
                        <!-- If Policy forfeits downpayment (< 14 days), offer choices -->
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2">
                                <button type="button" 
                                        @click="openRejectModal('{{ route('admin.bookings.requests.cancellation.reject', $req) }}', '{{ $booking->booking_number }}', 'Cancellation')"
                                        class="py-2 px-3 rounded-xl border border-[#D1D1D6] hover:bg-[#F2F2F7] text-xs font-bold text-[#6E6E73] transition-colors text-center shrink-0">
                                    Reject
                                </button>

                                <form action="{{ route('admin.bookings.requests.cancellation.approve', $req) }}" method="POST" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="action_type" value="forfeit">
                                    <button type="submit" 
                                            onclick="return confirm('Approve cancellation with downpayment FORFEITED (₱0 refund) for {{ $booking->booking_number }} as per policy?')"
                                            class="w-full py-2 rounded-xl bg-[#1D1D1F] hover:bg-black text-white text-xs font-bold shadow-sm transition-colors text-center">
                                        Approve & Forfeit (₱0 Refund)
                                    </button>
                                </form>
                            </div>

                            <form action="{{ route('admin.bookings.requests.cancellation.approve', $req) }}" method="POST">
                                @csrf
                                <input type="hidden" name="action_type" value="full_refund">
                                <button type="submit" 
                                        onclick="return confirm('Override policy and approve 100% REFUND (₱{{ number_format($booking->paid_amount, 2) }}) for {{ $booking->booking_number }}?')"
                                        class="w-full py-1.5 rounded-xl border border-rose-200 bg-rose-50/60 hover:bg-rose-100 text-[#780000] text-[11px] font-bold transition-colors text-center">
                                    Override Policy: Approve 100% Refund (₱{{ number_format($booking->paid_amount, 2) }})
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-full bg-white rounded-2xl border border-[#E5E5EA] p-6 text-center text-xs text-[#8E8E93]">
                No pending guest cancellation requests.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Rejection Modal -->
    <div x-show="rejectModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-[#E5E5EA]" 
             @click.outside="rejectModalOpen = false">
            <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                <h3 class="font-extrabold text-base text-[#1D1D1F]">
                    Reject <span x-text="rejectRequestType"></span> Request
                </h3>
                <button type="button" @click="rejectModalOpen = false" class="text-lg font-bold text-[#8E8E93] hover:text-[#1D1D1F]">✕</button>
            </div>

            <form :action="rejectFormAction" method="POST" class="space-y-4">
                @csrf
                <p class="text-xs text-[#6E6E73]">
                    Please provide an optional reason for rejecting the request for Booking #<strong class="text-[#1D1D1F]" x-text="rejectBookingNumber"></strong>:
                </p>

                <div>
                    <textarea name="admin_notes" 
                              x-model="rejectNotes" 
                              rows="3" 
                              placeholder="e.g. Requested batch date is at full capacity, or policy window has passed..." 
                              class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] focus:outline-none focus:border-[#780000] focus:ring-1 focus:ring-[#780000]"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-[#E5E5EA]">
                    <button type="button" @click="rejectModalOpen = false" class="btn-secondary px-4 py-2 text-xs font-semibold">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#FF3B3C] hover:bg-[#D32F2F] text-white text-xs font-bold shadow-sm transition-colors">
                        Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
