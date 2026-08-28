@extends('layouts.admin')

@section('title', 'Pending Refund Requests | Camp FreedivePH')

@section('content')
<div class="space-y-6 text-sm">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-5">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.payments.index') }}" class="text-xs text-[#6E6E73] hover:text-[#1D1D1F] flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                    <span>Back to Payments</span>
                </a>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight mt-1">Pending Refund Requests</h1>
            <p class="text-xs sm:text-sm text-[#6E6E73] mt-1">
                Review and process guest refund requests based on camp cancellation policy.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.payments.index') }}" class="btn-secondary px-4 py-2.5 text-xs sm:text-sm font-semibold">
                Payments Ledger
            </a>
        </div>
    </div>

    <!-- Section 1: Pending Queue -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="font-extrabold text-base text-[#1D1D1F]">
                Pending Refund Approvals
                @if(count($pendingRefunds) > 0)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-[#FF3B3C] text-white ml-1.5">
                        {{ count($pendingRefunds) }} Pending
                    </span>
                @endif
            </h2>
        </div>

        @forelse($pendingRefunds as $req)
        @php
            $policy = $policies[$req->id] ?? null;
            $payment = $req->payment;
            $booking = $req->booking;
        @endphp
        <div class="bg-white rounded-2xl border border-[#E5E5EA] p-5 sm:p-6 space-y-5 shadow-2xs">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 border-b border-[#E5E5EA] pb-4">
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <a href="{{ route('admin.bookings.show', $booking) }}" class="font-mono font-extrabold text-base text-[#780000] hover:underline">
                            {{ $booking->booking_number }}
                        </a>
                        <span class="text-xs text-[#6E6E73]">• Lead Guest: <strong class="text-[#1D1D1F]">{{ $booking->contact_name }}</strong></span>
                    </div>
                    <p class="text-xs text-[#6E6E73] mt-1">
                        Dive Date: <strong class="text-[#1D1D1F]">{{ $booking->start_date->format('M d, Y') }}</strong>
                    </p>
                    <p class="text-xs text-[#6E6E73] mt-1">
                        Requested on {{ $req->requested_at ? $req->requested_at->format('M d, Y g:i A') : $req->created_at->format('M d, Y') }}
                    </p>
                </div>

                <div class="text-right">
                    <div class="text-xs text-[#6E6E73]">Refund Claim Amount</div>
                    <div class="text-lg font-extrabold text-[#1D1D1F]">₱{{ number_format($payment->amount ?? 0, 2) }}</div>
                </div>
            </div>

            <!-- Policy Evaluation -->
            <div class="p-4 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] space-y-2">
                <div class="flex items-center justify-between gap-2 flex-wrap">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-[#1D1D1F]">Camp Cancellation Policy Evaluation:</span>
                        @if($policy && ($policy['refund_percentage'] ?? 0) > 0)
                            <span class="py-0.5 text-xs font-bold text-emerald-700">
                                Eligible for Full Refund ({{ $policy['refund_percentage'] }}% • Notice > 7 Days)
                            </span>
                        @else
                            <span class="py-0.5 text-xs font-bold text-purple-700">
                                Non-Refundable Cancellation Window (Notice < 7 Days)
                            </span>
                        @endif
                    </div>
                </div>
                @if($req->notes)
                    <p class="text-xs text-[#6E6E73] italic">
                        Guest Note: "{{ $req->notes }}"
                    </p>
                @endif
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-2.5 pt-2 flex-wrap">
                <!-- Reject -->
                <form action="{{ route('admin.payments.refunds.reject', $req) }}" method="POST">
                    @csrf
                    <button type="submit" 
                            onclick="return confirm('Reject this refund request?')"
                            class="px-4 py-2 rounded-xl border border-[#D1D1D6] hover:bg-[#F2F2F7] text-xs font-bold text-[#6E6E73] transition-colors">
                        Reject Request
                    </button>
                </form>

                <!-- Forfeit -->
                <form action="{{ route('admin.payments.refunds.forfeit', $req) }}" method="POST">
                    @csrf
                    <button type="submit" 
                            onclick="return confirm('Mark downpayment as forfeited under camp cancellation policy?')"
                            class="px-4 py-2 rounded-xl border border-purple-300 bg-purple-50 hover:bg-purple-100 text-xs font-bold text-purple-800 transition-colors">
                        Forfeit Downpayment
                    </button>
                </form>

                <!-- Approve via PayMongo -->
                <form action="{{ route('admin.payments.refunds.approve', $req) }}" method="POST">
                    @csrf
                    <button type="submit" 
                            onclick="return confirm('Approve and process ₱{{ number_format($payment->amount, 2) }} refund via PayMongo?')"
                            class="btn-primary px-6 py-2 text-xs font-bold shadow-sm">
                        Approve & Execute Refund
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-[#E5E5EA] p-8 text-center space-y-2">
            <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto text-lg font-bold">
                ✓
            </div>
            <h3 class="font-bold text-[#1D1D1F]">No Pending Refund Requests</h3>
            <p class="text-xs text-[#6E6E73]">All customer cancellation refund claims have been reviewed and processed.</p>
        </div>
        @endforelse
    </div>

    <!-- Section 2: Processed History -->
    <div class="space-y-3 pt-4">
        <h2 class="font-extrabold text-base text-[#1D1D1F]">
            Processed Refund History
        </h2>

        <div class="bg-white rounded-xl border border-[#E5E5EA] overflow-hidden shadow-2xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#FAFAFC] border-b border-[#E5E5EA] text-[#6E6E73] font-bold">
                        <tr>
                            <th class="py-3 px-4">Booking #</th>
                            <th class="py-3 px-4">Lead Guest</th>
                            <th class="py-3 px-4 text-right">Amount</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4">Reviewed By</th>
                            <th class="py-3 px-4">Reviewed At</th>
                            <th class="py-3 px-4">Gateway Reference</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E5EA]">
                        @forelse($processedRefunds as $pr)
                        <tr class="hover:bg-[#FAFAFC] transition-colors">
                            <td class="py-3 px-4">
                                <a href="{{ route('admin.bookings.show', $pr->booking) }}" class="font-mono font-bold text-[#780000] hover:underline">
                                    {{ $pr->booking->booking_number }}
                                </a>
                            </td>
                            <td class="py-3 px-4 font-medium text-[#1D1D1F]">
                                {{ $pr->booking->contact_name }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-[#1D1D1F]">
                                ₱{{ number_format($pr->payment->amount ?? 0, 2) }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($pr->status === 'approved')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        Refunded
                                    </span>
                                @elseif($pr->status === 'forfeited')
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                        Forfeited
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Rejected
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-[#1D1D1F]">
                                {{ $pr->reviewer->name ?? 'System' }}
                            </td>
                            <td class="py-3 px-4 text-[#6E6E73] whitespace-nowrap">
                                {{ $pr->reviewed_at ? $pr->reviewed_at->format('M d, Y g:i A') : '—' }}
                            </td>
                            <td class="py-3 px-4 font-mono text-[11px] text-[#6E6E73]">
                                {{ $pr->paymongo_refund_id ?? 'N/A' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-xs text-[#8E8E93]">
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

</div>
@endsection
