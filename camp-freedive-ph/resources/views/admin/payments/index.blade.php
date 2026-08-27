@extends('layouts.admin')

@section('title', 'Payments & Refunds | Camp FreedivePH')

@section('content')
<div class="space-y-6 text-sm">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight">Payments & Refunds</h1>
            <p class="text-xs sm:text-sm text-[#6E6E73] mt-1">
                Keep track of guest downpayments, balances, online transactions, and refunds.
            </p>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            <a href="{{ route('admin.payments.refunds') }}" class="btn-secondary px-4 py-2.5 text-xs sm:text-sm font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 text-[#FF3B3C]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10h18M3 14h18M5 6h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z"></path></svg>
                <span>Pending Refunds</span>
                @if(isset($stats['pending_refunds']) && $stats['pending_refunds'] > 0)
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-[#FF3B3C] text-white">
                        {{ $stats['pending_refunds'] }}
                    </span>
                @endif
            </a>

            <a href="{{ route('admin.payments.create') }}" class="btn-primary px-5 py-2.5 text-xs sm:text-sm font-bold shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Record Payment</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats Grid (Single Box with Vertical Dividers with Top/Bottom Margin) -->
    <div class="bg-white rounded-2xl border border-[#E5E5EA] p-4 sm:p-5">
        <div class="grid grid-cols-2 lg:grid-cols-4 items-center gap-y-4">
            <!-- Total Collections -->
            <div class="px-4 sm:px-6 py-1">
                <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Total Collections</span>
                <div class="text-2xl font-extrabold text-emerald-700 mt-1">₱{{ number_format($stats['total_gross'] ?? 0, 2) }}</div>
                <span class="text-xs text-[#6E6E73] block mt-0.5">Verified completed payments</span>
            </div>

            <!-- Net Received -->
            <div class="relative px-4 sm:px-6 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Net Received</span>
                <div class="text-2xl font-extrabold text-[#1D1D1F] mt-1">₱{{ number_format($stats['total_net'] ?? 0, 2) }}</div>
                <span class="text-xs text-[#6E6E73] block mt-0.5">Net of gateway processing fees</span>
            </div>

            <!-- Total Refunded -->
            <div class="relative px-4 sm:px-6 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Total Refunded</span>
                <div class="text-2xl font-extrabold text-blue-700 mt-1">₱{{ number_format($stats['total_refunded'] ?? 0, 2) }}</div>
                <span class="text-xs text-[#6E6E73] block mt-0.5">Returned to guest accounts</span>
            </div>

            <!-- Forfeited -->
            <div class="relative px-4 sm:px-6 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Forfeited (Policy Locked)</span>
                <div class="text-2xl font-extrabold text-purple-700 mt-1">₱{{ number_format($stats['total_forfeited'] ?? 0, 2) }}</div>
                <span class="text-xs text-[#6E6E73] block mt-0.5">Non-refundable cancellations</span>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar (Auto-filter on select, No manual Filter button) -->
    <div class="bg-white p-4 rounded-xl border border-[#E5E5EA]">
        <form method="GET" action="{{ route('admin.payments.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 text-xs items-end">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block font-bold text-[#1D1D1F] mb-1">Search</label>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Booking #, Transaction ID, Guest (Enter)..." 
                       class="w-full px-3.5 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block font-bold text-[#1D1D1F] mb-1">Payment Status</label>
                <select name="status" onchange="this.form.submit()" class="w-full px-3.5 pr-10 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white font-medium">
                    <option value="">All Statuses</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid / Completed</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="refund_requested" {{ request('status') === 'refund_requested' ? 'selected' : '' }}>Refund Requested</option>
                    <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                    <option value="forfeited" {{ request('status') === 'forfeited' ? 'selected' : '' }}>Forfeited</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed / Cancelled</option>
                </select>
            </div>

            <!-- Payment Type / Stage -->
            <div>
                <label class="block font-bold text-[#1D1D1F] mb-1">Stage</label>
                <select name="stage" onchange="this.form.submit()" class="w-full px-3.5 pr-10 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white font-medium">
                    <option value="">All Stages</option>
                    <option value="downpayment" {{ request('stage') === 'downpayment' ? 'selected' : '' }}>Downpayment</option>
                    <option value="balance_settlement" {{ request('stage') === 'balance_settlement' ? 'selected' : '' }}>Balance Settlement</option>
                    <option value="full" {{ request('stage') === 'full' ? 'selected' : '' }}>Full Payment</option>
                </select>
            </div>

            <!-- Payment Method -->
            <div>
                <label class="block font-bold text-[#1D1D1F] mb-1">Method</label>
                <select name="method" onchange="this.form.submit()" class="w-full px-3.5 pr-10 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white font-medium">
                    <option value="">All Methods</option>
                    <option value="gcash" {{ request('method') === 'gcash' ? 'selected' : '' }}>GCash</option>
                    <option value="bpi_bank_transfer" {{ request('method') === 'bpi_bank_transfer' ? 'selected' : '' }}>BPI Bank Transfer</option>
                    <option value="card" {{ request('method') === 'card' ? 'selected' : '' }}>Credit / Debit Card</option>
                    <option value="cash" {{ request('method') === 'cash' ? 'selected' : '' }}>Cash at Camp</option>
                </select>
            </div>

            <!-- Reset Button (If Filtered) -->
            <div>
                @if(request()->anyFilled(['search', 'status', 'stage', 'method', 'date_from', 'date_to']))
                    <a href="{{ route('admin.payments.index') }}" class="btn-secondary px-4 py-2 text-xs font-semibold w-full text-center block">
                        Clear Filters
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Payments Ledger Table -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FAFAFC] border-b border-[#E5E5EA] text-[#6E6E73] font-bold">
                    <tr>
                        <th class="py-3 px-4">Transaction ID</th>
                        <th class="py-3 px-4">Booking #</th>
                        <th class="py-3 px-4">Lead Guest</th>
                        <th class="py-3 px-4">Stage</th>
                        <th class="py-3 px-4">Method</th>
                        <th class="py-3 px-4 text-right">Amount</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E5EA]">
                    @forelse($payments as $payment)
                    <tr class="hover:bg-[#FAFAFC] transition-colors">
                        <!-- Transaction ID -->
                        <td class="py-3 px-4 font-mono font-bold text-[#1D1D1F]">
                            {{ $payment->transaction_id ?? ('TXN-' . $payment->id) }}
                            @if($payment->paymongo_payment_id)
                                <span class="block text-[10px] font-normal text-[#6E6E73]">{{ $payment->paymongo_payment_id }}</span>
                            @endif
                        </td>

                        <!-- Booking Link -->
                        <td class="py-3 px-4">
                            @if($payment->booking)
                                <a href="{{ route('admin.bookings.show', $payment->booking) }}" class="font-mono font-bold text-[#780000] hover:underline">
                                    {{ $payment->booking->booking_number }}
                                </a>
                            @else
                                <span class="text-[#8E8E93]">No Booking</span>
                            @endif
                        </td>

                        <!-- Lead Guest Contact -->
                        <td class="py-3 px-4">
                            @if($payment->booking)
                                <strong class="text-[#1D1D1F] block">{{ $payment->booking->contact_name }}</strong>
                                <span class="text-[#6E6E73] text-[11px] block">{{ $payment->booking->contact_phone }}</span>
                            @else
                                <span class="text-[#8E8E93]">N/A</span>
                            @endif
                        </td>

                        <!-- Payment Stage -->
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded-md bg-[#F2F2F7] text-[#1D1D1F] font-semibold text-[11px]">
                                {{ $payment->payment_stage_label }}
                            </span>
                        </td>

                        <!-- Method -->
                        <td class="py-3 px-4 font-medium text-[#1D1D1F]">
                            {{ $payment->formatted_payment_method }}
                        </td>

                        <!-- Gross Amount -->
                        <td class="py-3 px-4 text-right font-bold text-sm text-[#1D1D1F]">
                            ₱{{ number_format($payment->amount, 2) }}
                        </td>

                        <!-- Status Badge -->
                        <td class="py-3 px-4 text-center">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border inline-block {{ $payment->status_badge['class'] }}">
                                {{ $payment->status_badge['label'] }}
                            </span>
                        </td>

                        <!-- Date -->
                        <td class="py-3 px-4 text-[#6E6E73] whitespace-nowrap">
                            {{ $payment->paid_at ? $payment->paid_at->format('M d, Y g:i A') : $payment->created_at->format('M d, Y g:i A') }}
                        </td>

                        <!-- Action -->
                        <td class="py-3 px-4 text-right whitespace-nowrap">
                            <a href="{{ route('admin.payments.show', $payment) }}" class="px-3 py-1.5 rounded-lg border border-[#D1D1D6] hover:bg-[#F2F2F7] font-semibold text-xs text-[#1D1D1F] transition-colors">
                                View Details →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-8 text-center text-xs text-[#8E8E93]">
                            No payment transactions matching your search criteria found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($payments->hasPages())
        <div class="p-4 border-t border-[#E5E5EA] bg-[#FAFAFC]">
            {{ $payments->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
