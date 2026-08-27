@extends('layouts.admin')

@section('title', 'Booking Management | Camp FreedivePH')

@section('content')
<div class="space-y-6 text-sm">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight">Booking Management</h1>
            <p class="text-xs sm:text-sm text-[#6E6E73] mt-1">
                View, search, and manage all guest reservations and trip details.
            </p>
        </div>

        @php
            $pendingRequestsCount = \App\Models\RescheduleRequest::where('status', 'pending')->count() + \App\Models\CancellationRequest::where('status', 'pending')->count();
        @endphp

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.bookings.requests') }}" class="btn-secondary px-4 py-2.5 text-xs sm:text-sm font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 text-[#FF8D28]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                <span>Pending Requests</span>
                @if($pendingRequestsCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-[#FF3B3C] text-white">
                        {{ $pendingRequestsCount }}
                    </span>
                @endif
            </a>

            <a href="{{ route('admin.bookings.create') }}" class="btn-primary px-5 py-2.5 text-xs sm:text-sm font-bold flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Add Booking</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats (Single Box with Vertical Dividers) -->
    <div class="bg-white rounded-2xl border border-[#E5E5EA] p-4 sm:p-5">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 items-center gap-y-4">
            <!-- Total -->
            <div class="px-4 sm:px-5 py-1">
                <span class="text-xs text-[#1D1D1F] font-bold uppercase tracking-wider block">Total</span>
                <div class="text-2xl font-extrabold text-[#1D1D1F] mt-1">{{ $stats['total'] }}</div>
            </div>

            <!-- Confirmed -->
            <div class="relative px-4 sm:px-5 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs text-[#1D1D1F] font-bold uppercase tracking-wider block">Confirmed</span>
                <div class="text-2xl font-extrabold text-[#1D1D1F] mt-1">{{ $stats['confirmed'] }}</div>
            </div>

            <!-- Rescheduled -->
            <div class="relative px-4 sm:px-5 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs text-[#1D1D1F] font-bold uppercase tracking-wider block">Rescheduled</span>
                <div class="text-2xl font-extrabold text-[#1D1D1F] mt-1">{{ $stats['rescheduled'] }}</div>
            </div>

            <!-- Completed -->
            <div class="relative px-4 sm:px-5 py-1">
                <div class="hidden lg:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs text-[#1D1D1F] font-bold uppercase tracking-wider block">Completed</span>
                <div class="text-2xl font-extrabold text-[#1D1D1F] mt-1">{{ $stats['completed'] }}</div>
            </div>

            <!-- No-Show -->
            <div class="relative px-4 sm:px-5 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs text-[#1D1D1F] font-bold uppercase tracking-wider block">No-Show</span>
                <div class="text-2xl font-extrabold text-[#1D1D1F] mt-1">{{ $stats['no_show'] }}</div>
            </div>

            <!-- Cancelled -->
            <div class="relative px-4 sm:px-5 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs text-[#1D1D1F] font-bold uppercase tracking-wider block">Cancelled</span>
                <div class="text-2xl font-extrabold text-[#1D1D1F] mt-1">{{ $stats['cancelled'] }}</div>
            </div>
        </div>
    </div>

    <!-- Filters & Search Bar (Auto-filter on select, No manual Filter button) -->
    <div class="bg-white p-4 sm:p-5 rounded-xl border border-[#E5E5EA]">
        <form method="GET" action="{{ route('admin.bookings.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 text-xs sm:text-sm items-end">
            
            <!-- Search Query -->
            <div class="lg:col-span-2">
                <label class="block font-bold text-[#1D1D1F] text-xs mb-1">Search Reservations</label>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Booking #, Name, Phone, Email (Enter)..." 
                       class="w-full px-3.5 py-2 rounded-xl border border-[#D1D1D6] text-xs sm:text-sm text-[#1D1D1F] bg-white">
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block font-bold text-[#1D1D1F] text-xs mb-1">Lifecycle Status</label>
                <select name="status" onchange="this.form.submit()" class="w-full px-3.5 pr-10 py-2 rounded-xl border border-[#D1D1D6] text-xs sm:text-sm text-[#1D1D1F] bg-white font-medium">
                    <option value="">All Statuses</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="rescheduled" {{ request('status') === 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                    <option value="reschedule_requested" {{ request('status') === 'reschedule_requested' ? 'selected' : '' }}>Reschedule Requested</option>
                    <option value="cancellation_requested" {{ request('status') === 'cancellation_requested' ? 'selected' : '' }}>Cancellation Requested</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="no_show" {{ request('status') === 'no_show' ? 'selected' : '' }}>No-Show</option>
                    <option value="cancelled_by_camp" {{ request('status') === 'cancelled_by_camp' ? 'selected' : '' }}>Cancelled by Camp</option>
                    <option value="cancelled_by_guest" {{ request('status') === 'cancelled_by_guest' ? 'selected' : '' }}>Cancelled by Guest</option>
                </select>
            </div>

            <!-- Batch Status Filter -->
            <div>
                <label class="block font-bold text-[#1D1D1F] text-xs mb-1">Batch Assignment</label>
                <select name="batch_status" onchange="this.form.submit()" class="w-full px-3.5 pr-10 py-2 rounded-xl border border-[#D1D1D6] text-xs sm:text-sm text-[#1D1D1F] bg-white font-medium">
                    <option value="">All Batches</option>
                    <option value="unassigned" {{ request('batch_status') === 'unassigned' || request('unassigned') === '1' ? 'selected' : '' }}>No Batch (Unassigned)</option>
                    <option value="assigned" {{ request('batch_status') === 'assigned' ? 'selected' : '' }}>Batch Assigned</option>
                </select>
            </div>

            <!-- Class Type Filter -->
            <div>
                <label class="block font-bold text-[#1D1D1F] text-xs mb-1">Class Package</label>
                <select name="class_type" onchange="this.form.submit()" class="w-full px-3.5 pr-10 py-2 rounded-xl border border-[#D1D1D6] text-xs sm:text-sm text-[#1D1D1F] bg-white font-medium">
                    <option value="">All Classes</option>
                    <option value="discovery" {{ request('class_type') === 'discovery' ? 'selected' : '' }}>Discovery</option>
                    <option value="fundive" {{ request('class_type') === 'fundive' ? 'selected' : '' }}>Fundive</option>
                    <option value="refinement" {{ request('class_type') === 'refinement' ? 'selected' : '' }}>Refinement</option>
                </select>
            </div>

            <!-- Sort By / Reset -->
            <div class="flex items-center gap-2">
                <div class="flex-1">
                    <label class="block font-bold text-[#1D1D1F] text-xs mb-1">Sort Order</label>
                    <select name="sort" onchange="this.form.submit()" class="w-full px-3.5 pr-10 py-2 rounded-xl border border-[#D1D1D6] text-xs sm:text-sm text-[#1D1D1F] bg-white font-medium">
                        <option value="created_desc" {{ request('sort', 'created_desc') === 'created_desc' ? 'selected' : '' }}>Newest</option>
                        <option value="created_asc" {{ request('sort') === 'created_asc' ? 'selected' : '' }}>Oldest</option>
                        <option value="dive_date_desc" {{ request('sort') === 'dive_date_desc' ? 'selected' : '' }}>Dive Date (Latest)</option>
                        <option value="dive_date_asc" {{ request('sort') === 'dive_date_asc' ? 'selected' : '' }}>Dive Date (Soonest)</option>
                    </select>
                </div>
                @if(request()->hasAny(['search', 'status', 'class_type', 'sort', 'batch_status', 'unassigned', 'date_from', 'date_to', 'payment_status']))
                    <a href="{{ route('admin.bookings.index') }}" class="btn-secondary px-3.5 py-2 text-xs shrink-0 self-end mb-0.5" title="Clear all filters">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Bookings Table (Flat border, no shadow) -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] overflow-x-auto">
        <table class="w-full text-left min-w-[900px]">
            <thead class="bg-[#FAFAFC] border-b border-[#E5E5EA] text-xs uppercase font-bold text-[#6E6E73]">
                <tr>
                    <th class="p-4 pl-6">Booking Number</th>
                    <th class="p-4">Customer Name</th>
                    <th class="p-4">Class Package</th>
                    <th class="p-4">Dive Dates</th>
                    <th class="p-4">Batch Assignment</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Payment Status</th>
                    <th class="p-4 text-right pr-6">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E5E5EA]">
                @forelse($bookings as $b)
                <tr class="hover:bg-[#FAFAFC]/60 transition-colors text-xs sm:text-sm">
                    <!-- Booking Number & PIN -->
                    <td class="p-4 pl-6 font-mono">
                        <a href="{{ route('admin.bookings.show', $b) }}" class="font-bold text-[#780000] hover:underline block text-sm">
                            {{ $b->booking_number }}
                        </a>
                        <span class="text-xs text-[#8E8E93]">PIN: {{ $b->pin }}</span>
                        @if($b->createdBy)
                            <span class="text-xs text-[#6E6E73] block">Manual: {{ $b->createdBy->name }}</span>
                        @endif
                    </td>

                    <!-- Contact -->
                    <td class="p-4">
                        <div class="font-bold text-[#1D1D1F]">{{ $b->contact_name }}</div>
                        <div class="text-xs text-[#6E6E73]">{{ $b->contact_phone }}</div>
                    </td>

                    <!-- Class -->
                    <td class="p-4">
                        <span class="font-semibold text-[#1D1D1F] block">{{ $b->formatted_class_type }}</span>
                        <span class="text-xs text-[#6E6E73]">{{ $b->pickup_option === 'carpool' ? 'Carpool' : 'Own Transpo' }} • {{ $b->participants->count() }} pax</span>
                    </td>

                    <!-- Dates -->
                    <td class="p-4 whitespace-nowrap">
                        <strong class="text-[#1D1D1F] block">{{ $b->start_date->format('M d, Y') }}</strong>
                        <span class="text-xs text-[#8E8E93]">to {{ $b->end_date->format('M d, Y') }}</span>
                    </td>

                    <!-- Batch Assignment -->
                    <td class="p-4">
                        @if($b->batch)
                            <a href="{{ route('admin.batches.show', $b->batch) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[#166534] text-xs font-semibold hover:underline">
                                <span>{{ $b->batch->batch_code }}</span>
                            </a>
                        @else
                            @if(in_array($b->status, ['confirmed', 'rescheduled']))
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold text-[#92400E]">
                                    No Batch
                                </span>
                            @else
                                <span class="text-xs text-[#8E8E93] italic">Unassigned</span>
                            @endif
                        @endif
                    </td>

                    <!-- Status Badge -->
                    <td class="p-4">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $b->status_badge['bg'] }}">
                            {{ $b->status_badge['label'] }}
                        </span>
                    </td>

                    <!-- Payment Status -->
                    <td class="p-4">
                        <span class="px-2 py-0.5 rounded text-xs font-bold {{ $b->payment_status_badge['class'] }}">
                            {{ $b->payment_status_badge['label'] }}
                        </span>
                        <span class="text-xs font-bold text-[#1D1D1F] block mt-0.5">₱{{ number_format($b->downpayment_amount, 2) }}</span>
                    </td>

                    <!-- Actions -->
                    <td class="p-4 text-right pr-6 whitespace-nowrap">
                        <a href="{{ route('admin.bookings.show', $b) }}" class="btn-primary px-3.5 py-1.5 text-xs font-bold flex items-center justify-center gap-1.5 whitespace-nowrap">
                            <span>Manage</span>
                            <span>→</span>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="p-8 text-center text-[#6E6E73]">
                        No bookings found matching your search or filters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        {{ $bookings->links() }}
    </div>

</div>
@endsection
