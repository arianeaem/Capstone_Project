@extends('layouts.admin')

@section('title', 'Pending Guest Requests | Camp FreedivePH')

@section('content')
<div class="space-y-6 text-sm" x-data="{
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

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.payments.refunds') }}" class="btn-secondary px-3.5 py-2 text-xs sm:text-sm font-semibold">
                <span>View Pending Refunds</span>
            </a>
            <a href="{{ route('admin.bookings.index') }}" class="btn-secondary px-3.5 py-2 text-xs sm:text-sm font-semibold">
                View All Bookings
            </a>
        </div>
    </div>

    <!-- Pending Reschedule Requests -->
    <div class="space-y-3.5">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-base text-[#1D1D1F] flex items-center gap-2">
                <span>Reschedule Requests</span>
                @if(count($pendingReschedules) > 0)
                    <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-amber-50 text-amber-800 border border-amber-300">
                        {{ count($pendingReschedules) }} Pending
                    </span>
                @endif
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
            @forelse($pendingReschedules as $req)
            @php
                $booking = $req->booking;
                $resPolicy = $reschedulePolicies[$req->id] ?? null;
            @endphp
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-5 flex flex-col justify-between shadow-2xs hover:border-[#D1D1D6] transition-all gap-4">
                <!-- Request Header -->
                <div>
                    <div class="flex items-start justify-between gap-2 border-b border-[#E5E5EA] pb-3">
                        <div class="min-w-0">
                            <a href="{{ route('admin.bookings.show', $booking) }}" class="font-mono font-extrabold text-sm text-[#780000] hover:underline block truncate">
                                {{ $booking->booking_number }}
                            </a>
                            <div class="text-xs font-bold text-[#1D1D1F] mt-0.5 truncate">{{ $booking->contact_name }}</div>
                            <div class="text-xs text-[#6E6E73] truncate">{{ $booking->contact_phone }}</div>
                        </div>
                        <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-[#FAFAFC] text-[#1D1D1F] border border-[#E5E5EA] shrink-0">
                            Reschedule
                        </span>
                    </div>

                    <!-- Date Shift Details -->
                    <div class="mt-3 space-y-2 p-3 rounded-lg bg-[#FAFAFC] border border-[#E5E5EA] text-xs">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[#6E6E73] text-xs">Current:</span>
                            <strong class="text-[#1D1D1F] font-semibold text-right">{{ $booking->start_date->format('M d') }} – {{ $booking->end_date->format('M d, Y') }}</strong>
                        </div>
                        <div class="flex items-center justify-between gap-2 border-t border-[#E5E5EA] pt-2">
                            <span class="text-[#1D1D1F] font-semibold text-xs">Requested:</span>
                            <strong class="text-emerald-700 font-bold text-right">{{ $req->requested_start_date->format('M d') }} – {{ $req->requested_end_date->format('M d, Y') }}</strong>
                        </div>

                        @if($req->reason)
                            <div class="border-t border-[#E5E5EA] pt-2 text-xs text-[#6E6E73]">
                                <strong class="text-[#1D1D1F] text-xs">Guest Reason:</strong>
                                <span class="italic text-xs block mt-0.5">"{{ $req->reason }}"</span>
                            </div>
                        @endif
                    </div>

                    <div class="mt-2.5 text-xs text-[#8E8E93]">
                        Submitted: {{ $req->requested_at ? $req->requested_at->format('M d, Y g:i A') : $req->created_at->format('M d, Y') }}
                    </div>
                </div>

                <!-- Reschedule Actions -->
                <div class="flex items-center gap-2 pt-3 border-t border-[#E5E5EA]">
                    <button type="button" 
                            @click="openRejectModal('{{ route('admin.bookings.requests.reschedule.reject', $req) }}', '{{ $booking->booking_number }}', 'Reschedule')"
                            class="btn-secondary flex-1 py-2 text-xs font-semibold text-center">
                        Reject
                    </button>

                    <form action="{{ route('admin.bookings.requests.reschedule.approve', $req) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" 
                                onclick="return confirm('Approve reschedule for {{ $booking->booking_number }} to {{ $req->requested_start_date->format('M d, Y') }}?')"
                                class="btn-primary w-full py-2 text-xs font-bold shadow-2xs text-center">
                            Approve
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="col-span-full bg-white rounded-xl border border-[#E5E5EA] p-6 text-center text-xs text-[#8E8E93]">
                No pending guest reschedule requests.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Pending Cancellation Requests -->
    <div class="space-y-3.5 pt-4">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-base text-[#1D1D1F] flex items-center gap-2">
                <span>Cancellation Requests</span>
                @if(count($pendingCancellations) > 0)
                    <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-[#780000] text-white">
                        {{ count($pendingCancellations) }} Pending
                    </span>
                @endif
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
            @forelse($pendingCancellations as $req)
            @php
                $booking = $req->booking;
                $policy = $cancellationPolicies[$req->id] ?? null;
                $isFullRefund = ($policy && ($policy['refund_percentage'] ?? 0) === 100);
                $recRefund = $policy['calculated_refund'] ?? $req->calculated_refund_amount;
            @endphp
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-5 flex flex-col justify-between shadow-2xs hover:border-[#D1D1D6] transition-all gap-4">
                <!-- Request Header -->
                <div>
                    <div class="flex items-start justify-between gap-2 border-b border-[#E5E5EA] pb-3">
                        <div class="min-w-0">
                            <a href="{{ route('admin.bookings.show', $booking) }}" class="font-mono font-extrabold text-sm text-[#780000] hover:underline block truncate">
                                {{ $booking->booking_number }}
                            </a>
                            <div class="text-xs font-bold text-[#1D1D1F] mt-0.5 truncate">{{ $booking->contact_name }}</div>
                            <div class="text-xs text-[#6E6E73] truncate">{{ $booking->contact_email }}</div>
                        </div>
                        <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-[#FAFAFC] text-[#1D1D1F] border border-[#E5E5EA] shrink-0">
                            Cancel Claim
                        </span>
                    </div>

                    <!-- Cancellation Policy & Breakdown -->
                    <div class="mt-3 p-3 rounded-lg bg-[#FAFAFC] border border-[#E5E5EA] space-y-2 text-xs">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[#6E6E73]">Trip Dates:</span>
                            <strong class="text-[#1D1D1F] font-semibold text-right">{{ $booking->start_date->format('M d') }} – {{ $booking->end_date->format('M d, Y') }}</strong>
                        </div>
                        <div class="flex items-center justify-between gap-2 border-t border-[#E5E5EA] pt-2">
                            <span class="text-[#6E6E73]">Total Paid:</span>
                            <strong class="text-[#1D1D1F] font-bold text-right">₱{{ number_format($booking->paid_amount, 2) }}</strong>
                        </div>

                        @if($policy)
                            <div class="border-t border-[#E5E5EA] pt-2 space-y-1">
                                <div class="flex items-center justify-between font-bold text-xs">
                                    <div class="flex items-center gap-1.5 {{ $isFullRefund ? 'text-emerald-800' : 'text-amber-900' }}">
                                        <span class="inline-block w-2 h-2 rounded-full {{ $isFullRefund ? 'bg-emerald-600' : 'bg-amber-600' }} shrink-0"></span>
                                        <span>Policy Recommendation:</span>
                                    </div>
                                    <span class="{{ $isFullRefund ? 'text-emerald-700 font-extrabold' : 'text-amber-800 font-extrabold' }}">
                                        {{ $isFullRefund ? '100% Refund (₱' . number_format($recRefund, 2) . ')' : '0% Refund (Forfeited)' }}
                                    </span>
                                </div>
                                <p class="text-xs text-[#6E6E73] leading-relaxed">
                                    {{ $policy['cancel_message'] ?? 'Cancellation evaluated under standard policy.' }}
                                </p>
                            </div>
                        @endif

                        @if($req->reason)
                            <div class="border-t border-[#E5E5EA] pt-2 text-xs text-[#6E6E73]">
                                <strong class="text-[#1D1D1F] text-xs">Guest Reason:</strong>
                                <span class="italic text-xs block mt-0.5">"{{ $req->reason }}"</span>
                            </div>
                        @endif
                    </div>

                    <div class="mt-2.5 text-xs text-[#8E8E93]">
                        Submitted: {{ $req->requested_at ? $req->requested_at->format('M d, Y g:i A') : $req->created_at->format('M d, Y') }}
                    </div>
                </div>

                <!-- Cancellation Resolution Actions -->
                <div class="space-y-2 pt-3 border-t border-[#E5E5EA]">
                    @if($isFullRefund)
                        <!-- Full Refund Actions -->
                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    @click="openRejectModal('{{ route('admin.bookings.requests.cancellation.reject', $req) }}', '{{ $booking->booking_number }}', 'Cancellation')"
                                    class="btn-secondary py-2 px-3 text-xs font-semibold text-center shrink-0">
                                Reject
                            </button>

                            <form action="{{ route('admin.bookings.requests.cancellation.approve', $req) }}" method="POST" class="flex-1">
                                @csrf
                                <input type="hidden" name="action_type" value="policy_refund">
                                <button type="submit" 
                                        onclick="return confirm('Approve cancellation & queue full refund of ₱{{ number_format($recRefund, 2) }} for {{ $booking->booking_number }}?')"
                                        class="btn-primary w-full py-2 text-xs font-bold shadow-2xs text-center">
                                    Approve & Queue Refund (₱{{ number_format($recRefund, 2) }})
                                </button>
                            </form>
                        </div>
                    @else
                        <!-- Forfeiture or Override Actions -->
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <button type="button" 
                                        @click="openRejectModal('{{ route('admin.bookings.requests.cancellation.reject', $req) }}', '{{ $booking->booking_number }}', 'Cancellation')"
                                        class="btn-secondary py-2 px-3 text-xs font-semibold text-center shrink-0">
                                    Reject
                                </button>

                                <form action="{{ route('admin.bookings.requests.cancellation.approve', $req) }}" method="POST" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="action_type" value="forfeit">
                                    <button type="submit" 
                                            onclick="return confirm('Approve cancellation with downpayment FORFEITED (₱0 refund) for {{ $booking->booking_number }} as per policy?')"
                                            class="btn-secondary w-full py-2 text-xs font-bold text-center">
                                        Approve & Forfeit (₱0 Refund)
                                    </button>
                                </form>
                            </div>

                            <form action="{{ route('admin.bookings.requests.cancellation.approve', $req) }}" method="POST">
                                @csrf
                                <input type="hidden" name="action_type" value="full_refund">
                                <button type="submit" 
                                        onclick="return confirm('Override policy and approve 100% REFUND (₱{{ number_format($booking->paid_amount, 2) }}) for {{ $booking->booking_number }}?')"
                                        class="btn-danger w-full py-1.5 text-xs font-bold text-center">
                                    Override Policy: Approve 100% Refund (₱{{ number_format($booking->paid_amount, 2) }})
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-full bg-white rounded-xl border border-[#E5E5EA] p-6 text-center text-xs text-[#8E8E93]">
                No pending guest cancellation requests.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Rejection Modal -->
    <div x-show="rejectModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div class="bg-white rounded-xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-[#E5E5EA]" 
             @click.outside="rejectModalOpen = false">
            <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                <h3 class="font-bold text-base text-[#1D1D1F]">
                    Reject <span x-text="rejectRequestType"></span> Request
                </h3>
                <button type="button" @click="rejectModalOpen = false" class="text-base font-bold text-[#8E8E93] hover:text-[#1D1D1F]">✕</button>
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
                              class="w-full px-3 py-2 rounded-lg border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-[#E5E5EA]">
                    <button type="button" @click="rejectModalOpen = false" class="btn-secondary px-3.5 py-1.5 text-xs font-semibold">
                        Cancel
                    </button>
                    <button type="submit" class="btn-danger px-4 py-1.5 text-xs font-bold shadow-2xs">
                        Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
