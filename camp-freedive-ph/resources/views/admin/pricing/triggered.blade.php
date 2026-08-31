@extends('layouts.admin')

@section('title', 'Bookings Triggered by ' . $rule->name . ' | Camp FreedivePH')

@section('content')
<div class="space-y-6 text-sm">

    <!-- Top Header & Breadcrumb -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-5">
        <div>
            <div class="flex items-center gap-2 text-xs text-[#6E6E73] mb-1">
                <a href="{{ route('admin.pricing.index') }}" class="hover:text-[#780000] font-semibold transition-colors">Dynamic Pricing</a>
                <span>/</span>
                <span>Triggered Bookings Audit</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight">
                {{ $rule->name }}
            </h1>
            <div class="flex items-center gap-2.5 mt-1.5 flex-wrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-800">
                    {{ $rule->condition_summary }}
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $rule->adjustment_type === 'increase' ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' }}">
                    {{ $rule->formatted_adjustment }}
                </span>
                <span class="text-xs text-[#6E6E73]">
                    Applies to {{ $rule->formatted_applies_to }}
                </span>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.pricing.edit', $rule) }}" class="btn-secondary px-4 py-2 text-xs font-bold">
                Edit Rule
            </a>
            <a href="{{ route('admin.pricing.index') }}" class="btn-secondary px-4 py-2 text-xs font-bold">
                ← Back to Rules
            </a>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-[#E5E5EA] shadow-2xs space-y-1">
            <div class="text-xs font-semibold text-[#6E6E73] uppercase tracking-wider">Bookings Triggered</div>
            <div class="text-2xl sm:text-3xl font-extrabold text-[#780000]">{{ number_format($totalCount) }}</div>
            <div class="text-[11px] text-[#6E6E73]">Total reservations evaluated with this rule</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-[#E5E5EA] shadow-2xs space-y-1">
            <div class="text-xs font-semibold text-[#6E6E73] uppercase tracking-wider">Total Rule Price Impact</div>
            <div class="text-2xl sm:text-3xl font-extrabold {{ $totalImpact >= 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                {{ $totalImpact >= 0 ? '+' : '−' }}₱{{ number_format(abs($totalImpact), 2) }}
            </div>
            <div class="text-[11px] text-[#6E6E73]">Cumulative discount or surcharge volume</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-[#E5E5EA] shadow-2xs space-y-1">
            <div class="text-xs font-semibold text-[#6E6E73] uppercase tracking-wider">Rule Status</div>
            <div class="text-xl sm:text-2xl font-extrabold flex items-center gap-2 mt-1">
                <span class="w-2.5 h-2.5 rounded-full {{ $rule->status === 'active' ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                <span class="{{ $rule->status === 'active' ? 'text-emerald-700' : 'text-gray-600' }}">{{ ucfirst($rule->status) }}</span>
            </div>
            <div class="text-[11px] text-[#6E6E73]">{{ $rule->status === 'active' ? 'Currently evaluating live customer bookings' : 'Rule paused (not active)' }}</div>
        </div>
    </div>

    <!-- Date Filter Bar -->
    <div class="bg-white rounded-2xl border border-[#E5E5EA] p-4 shadow-2xs">
        <form method="GET" action="{{ route('admin.pricing.triggered', $rule) }}" class="flex flex-col sm:flex-row sm:items-end gap-3">
            <div>
                <label class="block text-xs font-bold text-[#1D1D1F] mb-1">Dive Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="text-xs rounded-xl border border-[#D1D1D6] px-3 py-2 bg-[#FAFAFC] focus:bg-white focus:border-[#780000]">
            </div>

            <div>
                <label class="block text-xs font-bold text-[#1D1D1F] mb-1">Dive Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="text-xs rounded-xl border border-[#D1D1D6] px-3 py-2 bg-[#FAFAFC] focus:bg-white focus:border-[#780000]">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="btn-primary px-4 py-2 text-xs font-bold">
                    Filter
                </button>
                @if(request('date_from') || request('date_to'))
                <a href="{{ route('admin.pricing.triggered', $rule) }}" class="btn-secondary px-3 py-2 text-xs font-bold">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Triggered Bookings Table -->
    <div class="bg-white rounded-2xl border border-[#E5E5EA] overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-[#E5E5EA] bg-[#FAFAFC] text-[11px] font-bold text-[#6E6E73] uppercase tracking-wider">
                        <th class="py-3 px-4 text-left">Booking Number</th>
                        <th class="py-3 px-4 text-left">Customer Name</th>
                        <th class="py-3 px-4 text-left">Dive Date</th>
                        <th class="py-3 px-4 text-left">Class</th>
                        <th class="py-3 px-4 text-left">Base → Adjusted Rate</th>
                        <th class="py-3 px-4 text-left">Rule Delta (Per Pax)</th>
                        <th class="py-3 px-4 text-left pr-6">Date Booked</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E5EA]">
                    @forelse($adjustments as $adj)
                    @php
                        $booking = $adj->booking;
                    @endphp
                    <tr @if($booking) onclick="window.location='{{ route('admin.bookings.show', $booking) }}'" class="hover:bg-[#F2F2F7]/60 cursor-pointer transition-colors group" @else class="hover:bg-[#F2F2F7]/40 transition-colors" @endif>
                        <!-- Booking Number -->
                        <td class="py-3.5 px-4 text-left font-mono font-bold text-[#780000] group-hover:underline">
                            {{ $booking ? $booking->booking_number : '—' }}
                        </td>

                        <!-- Customer Name -->
                        <td class="py-3.5 px-4 text-left">
                            <div class="font-bold text-[#1D1D1F]">{{ $booking ? $booking->contact_name : 'Unknown Guest' }}</div>
                            <div class="text-xs text-[#6E6E73]">{{ $booking ? $booking->contact_phone : '' }}</div>
                        </td>

                        <!-- Dive Date -->
                        <td class="py-3.5 px-4 text-left font-medium text-[#1D1D1F]">
                            {{ $booking ? $booking->start_date->format('M d, Y') : '—' }}
                        </td>

                        <!-- Class -->
                        <td class="py-3.5 px-4 text-left capitalize font-semibold text-[#1D1D1F]">
                            {{ $booking ? $booking->class_type : '—' }}
                        </td>

                        <!-- Base -> Adjusted Rate -->
                        <td class="py-3.5 px-4 text-left">
                            <div class="flex items-center gap-1.5 text-xs">
                                <span class="line-through text-[#8E8E93]">₱{{ number_format($adj->base_price, 2) }}</span>
                                <span class="text-[#8E8E93]">→</span>
                                <span class="font-extrabold text-[#1D1D1F]">₱{{ number_format($adj->adjusted_price, 2) }}</span>
                            </div>
                        </td>

                        <!-- Rule Delta -->
                        <td class="py-3.5 px-4 text-left">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $adj->adjustment_amount >= 0 ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' }}">
                                {{ $adj->adjustment_amount >= 0 ? '+' : '−' }}₱{{ number_format(abs($adj->adjustment_amount), 2) }}
                            </span>
                        </td>

                        <!-- Date Booked -->
                        <td class="py-3.5 px-4 text-left pr-6 text-xs text-[#6E6E73]">
                            {{ $adj->created_at->format('M d, Y h:i A') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center text-[#6E6E73]">
                            <div class="space-y-1">
                                <div class="font-bold text-[#1D1D1F]">No Bookings Have Triggered This Rule Yet</div>
                                <p class="text-xs">Once customer bookings meet this rule's conditions during checkout, they will be logged here automatically.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($adjustments->hasPages())
        <div class="p-4 border-t border-[#E5E5EA]">
            {{ $adjustments->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
