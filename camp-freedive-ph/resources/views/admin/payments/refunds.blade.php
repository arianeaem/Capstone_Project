@extends('layouts.admin')

@section('title', 'Pending Refund Requests | Camp FreedivePH')

@section('content')
<div class="space-y-6 text-sm" x-data="{
    rejectModalOpen: false,
    rejectFormAction: '',
    rejectBookingNumber: '',
    rejectAmount: '',
    rejectNotes: '',
    openRejectModal(url, bookingNumber, amount) {
        this.rejectFormAction = url;
        this.rejectBookingNumber = bookingNumber;
        this.rejectAmount = amount;
        this.rejectNotes = '';
        this.rejectModalOpen = true;
    }
}">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-5">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.payments.index') }}" class="text-xs text-[#6E6E73] hover:text-[#1D1D1F] flex items-center gap-1 font-medium">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                    <span>Back to Payments</span>
                </a>
                <span class="text-[#D1D1D6]">/</span>
                <span class="text-xs font-bold text-[#780000]">Refund Queue</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight mt-1">Pending Refund Requests</h1>
            <p class="text-xs sm:text-sm text-[#6E6E73] mt-1">
                Review, forfeit, or approve customer refund claims based on the 14-day camp cancellation policy.
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.bookings.requests') }}" class="btn-secondary px-3.5 py-2 text-xs sm:text-sm font-semibold flex items-center gap-1.5">
                <svg class="w-4 h-4 text-[#6E6E73]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <span>Reschedule/Cancel Requests</span>
            </a>
            <a href="{{ route('admin.payments.index') }}" class="btn-secondary px-3.5 py-2 text-xs sm:text-sm font-semibold">
                Payments Ledger
            </a>
        </div>
    </div>

    <!-- Section 1: Pending Queue (Card Format) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="font-extrabold text-base text-[#1D1D1F] flex items-center gap-2">
                <span>Pending Refund Approvals</span>
                @if(count($pendingRefunds) > 0)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-[#FF3B3C] text-white shadow-2xs">
                        {{ count($pendingRefunds) }} Action Required
                    </span>
                @endif
            </h2>
        </div>

        <!-- CARD GRID -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @forelse($pendingRefunds as $req)
            @php
                $policy = $policies[$req->id] ?? null;
                $payment = $req->payment;
                $booking = $req->booking;
                $isEligible = ($policy && ($policy['refund_percentage'] ?? 0) > 0);
                $claimAmount = $payment->amount ?? 0;
            @endphp
            <div class="bg-white rounded-2xl border border-[#E5E5EA] p-5 shadow-2xs hover:border-[#D1D1D6] transition-all flex flex-col justify-between space-y-4">
                
                <!-- Card Header -->
                <div>
                    <div class="flex items-start justify-between gap-3 border-b border-[#E5E5EA] pb-3.5">
                        <div class="space-y-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('admin.bookings.show', $booking) }}" class="font-mono font-extrabold text-sm text-[#780000] hover:underline">
                                    {{ $booking->booking_number }}
                                </a>
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-md bg-[#FAFAFC] border border-[#E5E5EA] text-[#3A3A3C] truncate">
                                    {{ $booking->formatted_class_type }}
                                </span>
                            </div>
                            <div class="text-[11px] text-[#8E8E93] flex items-center gap-1">
                                <span>Requested {{ $req->requested_at ? $req->requested_at->diffForHumans() : $req->created_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        <span class="px-2.5 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-amber-50 text-amber-800 border border-amber-200 shrink-0">
                            Pending
                        </span>
                    </div>

                    <!-- Lead Guest & Trip Details -->
                    <div class="mt-3.5 space-y-2.5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-[#F8EAEA] text-[#780000] border border-[#780000] flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                {{ substr($booking->contact_name, 0, 1) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="font-bold text-xs text-[#1D1D1F] truncate">{{ $booking->contact_name }}</div>
                                <div class="text-[11px] text-[#6E6E73] truncate">{{ $booking->contact_email }} • {{ $booking->contact_phone }}</div>
                            </div>
                        </div>

                        <div class="p-2.5 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] flex items-center justify-between text-xs">
                            <div class="flex items-center gap-1.5 text-[#1D1D1F] font-medium">
                                <span>Dive Date:</span>
                                <strong class="text-[#1D1D1F]">{{ $booking->start_date->format('M d, Y') }}</strong>
                            </div>
                            <span class="text-[11px] text-[#6E6E73] font-semibold">
                                {{ $booking->participants->count() }} Student{{ $booking->participants->count() > 1 ? 's' : '' }}
                            </span>
                        </div>
                    </div>

                    <!-- Refund Claim Financial Box -->
                    <div class="mt-3 p-3.5 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] space-y-2">
                        <div class="flex items-baseline justify-between">
                            <span class="text-xs text-[#6E6E73] font-medium">Refund Claim Amount:</span>
                            <div class="text-xl font-black text-[#1D1D1F]">
                                ₱{{ number_format($claimAmount, 2) }}
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-[11px] text-[#6E6E73] pt-1.5 border-t border-[#E5E5EA]/70">
                            <span>Method: <strong class="text-[#1D1D1F] uppercase">{{ $payment->payment_method ?? 'GCash' }}</strong></span>
                            <span>Type: <strong class="text-[#1D1D1F] capitalize">{{ $payment->payment_type ?? 'downpayment' }}</strong></span>
                        </div>
                    </div>

                    <!-- Policy Evaluation Chip -->
                    <div class="mt-3 p-3 rounded-xl {{ $isEligible ? 'bg-emerald-50/70 border border-emerald-200' : 'bg-purple-50/70 border border-purple-200' }} space-y-1.5">
                        <div class="flex items-center gap-1.5 font-bold text-xs {{ $isEligible ? 'text-emerald-800' : 'text-purple-900' }}">
                            @if($isEligible)
                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span>Eligible for 100% Full Refund</span>
                            @else
                                <svg class="w-3.5 h-3.5 text-purple-700 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                <span>Non-Refundable Policy Window</span>
                            @endif
                        </div>
                        <p class="text-[11px] {{ $isEligible ? 'text-emerald-700' : 'text-purple-800' }} leading-relaxed">
                            @if($isEligible)
                                Cancellation filed 14 days or more before the dive date. Qualified for full online gateway reversal.
                            @else
                                Cancellation filed less than 14 days before the dive date. Standard policy prescribes deposit forfeiture.
                            @endif
                        </p>
                        @if($req->notes)
                            <div class="text-[11px] text-[#6E6E73] italic pt-1 border-t {{ $isEligible ? 'border-emerald-200/60' : 'border-purple-200/60' }}">
                                Note: "{{ $req->notes }}"
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Card Action Footer -->
                <div class="pt-3 border-t border-[#E5E5EA] space-y-2">
                    <div class="flex items-center gap-2">
                        <!-- Reject Button -->
                        <button type="button" 
                                @click="openRejectModal('{{ route('admin.payments.refunds.reject', $req) }}', '{{ $booking->booking_number }}', '₱{{ number_format($claimAmount, 2) }}')"
                                class="py-2 px-3.5 rounded-xl border border-[#D1D1D6] hover:bg-[#F2F2F7] text-xs font-bold text-[#6E6E73] transition-colors text-center shrink-0">
                            Reject
                        </button>

                        @if(!$isEligible)
                            <!-- Forfeit Action Form -->
                            <form action="{{ route('admin.payments.refunds.forfeit', $req) }}" method="POST" class="flex-1">
                                @csrf
                                <input type="hidden" name="forfeit_reason" value="cancellation_outside_policy_window">
                                <button type="submit" 
                                        onclick="return confirm('Forfeit ₱{{ number_format($claimAmount, 2) }} deposit under camp cancellation policy for {{ $booking->booking_number }}?')"
                                        class="w-full py-2 rounded-xl border border-purple-300 bg-purple-100 hover:bg-purple-200 text-purple-900 text-xs font-bold transition-colors text-center shadow-2xs">
                                    Forfeit Deposit (₱0)
                                </button>
                            </form>
                        @else
                            <!-- Approve Action Form -->
                            <form action="{{ route('admin.payments.refunds.approve', $req) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" 
                                        onclick="return confirm('Approve & execute ₱{{ number_format($claimAmount, 2) }} refund via PayMongo for {{ $booking->booking_number }}?')"
                                        class="w-full py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition-colors text-center">
                                    Approve & Execute (₱{{ number_format($claimAmount, 2) }})
                                </button>
                            </form>
                        @endif
                    </div>

                    @if(!$isEligible)
                        <!-- Policy Override Action Button -->
                        <form action="{{ route('admin.payments.refunds.approve', $req) }}" method="POST">
                            @csrf
                            <input type="hidden" name="notes" value="Administrative policy override: 100% refund approved">
                            <button type="submit" 
                                    onclick="return confirm('Override policy and execute full refund of ₱{{ number_format($claimAmount, 2) }} via PayMongo for {{ $booking->booking_number }}?')"
                                    class="w-full py-1.5 rounded-xl border border-rose-200 bg-rose-50/70 hover:bg-rose-100 text-[#780000] text-[11px] font-bold transition-colors text-center">
                                Override Policy: Execute Refund (₱{{ number_format($claimAmount, 2) }})
                            </button>
                        </form>
                    @endif
                </div>

            </div>
            @empty
            <div class="col-span-full bg-white rounded-2xl border border-[#E5E5EA] p-10 text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center mx-auto text-xl font-bold">
                    ✓
                </div>
                <h3 class="font-extrabold text-base text-[#1D1D1F]">No Pending Refund Requests</h3>
                <p class="text-xs text-[#6E6E73] max-w-md mx-auto">
                    All customer cancellation refund claims have been reviewed and processed.
                </p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Section 2: Processed Refund History Table -->
    <div class="space-y-3 pt-6 border-t border-[#E5E5EA]">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-extrabold text-base text-[#1D1D1F]">Processed Refund History</h2>
                <p class="text-xs text-[#6E6E73] mt-0.5">Audit ledger of approved, forfeited, and rejected refund transactions.</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-[#E5E5EA] overflow-hidden shadow-2xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#FAFAFC] border-b border-[#E5E5EA] text-[#6E6E73] font-bold">
                        <tr>
                            <th class="py-3.5 px-4 text-left">Booking #</th>
                            <th class="py-3.5 px-4 text-left">Lead Guest</th>
                            <th class="py-3.5 px-4 text-left">Amount</th>
                            <th class="py-3.5 px-4 text-left">Resolution</th>
                            <th class="py-3.5 px-4 text-left">Processed By</th>
                            <th class="py-3.5 px-4 text-left">Date Processed</th>
                            <th class="py-3.5 px-4 text-left">Gateway Reference</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E5EA]">
                        @forelse($processedRefunds as $pr)
                        <tr class="hover:bg-[#FAFAFC] transition-colors">
                            <td class="py-3.5 px-4 text-left">
                                <a href="{{ route('admin.bookings.show', $pr->booking) }}" class="font-mono font-bold text-[#780000] hover:underline">
                                    {{ $pr->booking->booking_number }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4 text-left font-medium text-[#1D1D1F]">
                                {{ $pr->booking->contact_name }}
                            </td>
                            <td class="py-3.5 px-4 text-left font-bold text-[#1D1D1F]">
                                ₱{{ number_format($pr->payment->amount ?? 0, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-left">
                                @if($pr->status === 'approved')
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Refunded
                                    </span>
                                @elseif($pr->status === 'forfeited')
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                        Forfeited
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Rejected
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-[#1D1D1F]">
                                {{ $pr->reviewer->name ?? 'System Administrator' }}
                            </td>
                            <td class="py-3.5 px-4 text-[#6E6E73] whitespace-nowrap">
                                {{ $pr->reviewed_at ? $pr->reviewed_at->format('M d, Y g:i A') : '—' }}
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-[#6E6E73]">
                                {{ $pr->paymongo_refund_id ?? ($pr->status === 'forfeited' ? 'Forfeited to Camp' : 'N/A') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-xs text-[#8E8E93]">
                                No processed refund records found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($processedRefunds->hasPages())
            <div class="p-4 border-t border-[#E5E5EA] bg-[#FAFAFC]">
                {{ $processedRefunds->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Rejection Reason Modal -->
    <div x-show="rejectModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-[#E5E5EA]" 
             @click.outside="rejectModalOpen = false">
            <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                <h3 class="font-extrabold text-base text-[#1D1D1F]">
                    Reject Refund Request
                </h3>
                <button type="button" @click="rejectModalOpen = false" class="text-lg font-bold text-[#8E8E93] hover:text-[#1D1D1F]">✕</button>
            </div>

            <form :action="rejectFormAction" method="POST" class="space-y-4">
                @csrf
                <p class="text-xs text-[#6E6E73]">
                    Please provide a reason for rejecting the refund request (<span class="font-bold text-[#1D1D1F]" x-text="rejectAmount"></span>) for Booking #<strong class="text-[#1D1D1F]" x-text="rejectBookingNumber"></strong>:
                </p>

                <div>
                    <textarea name="notes" 
                              x-model="rejectNotes" 
                              rows="3" 
                              required
                              placeholder="e.g. Request does not meet cancellation criteria or chargeback already settled..." 
                              class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] focus:outline-none focus:border-[#780000] focus:ring-1 focus:ring-[#780000]"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-[#E5E5EA]">
                    <button type="button" @click="rejectModalOpen = false" class="btn-secondary px-4 py-2 text-xs font-semibold">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#FF3B3C] hover:bg-[#D32F2F] text-white text-xs font-bold shadow-sm transition-colors">
                        Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
