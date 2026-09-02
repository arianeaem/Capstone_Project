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

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.bookings.requests') }}" class="btn-secondary px-3.5 py-2 text-xs sm:text-sm font-semibold flex items-center gap-2">
                <span>Pending Requests</span>
                @if($pendingRequestsCount > 0)
                    <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-[#780000] text-white">
                        {{ $pendingRequestsCount }}
                    </span>
                @endif
            </a>

            <a href="{{ route('admin.bookings.create') }}" class="btn-primary px-4 py-2 text-xs sm:text-sm font-bold flex items-center gap-1.5 shadow-2xs">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Add Booking</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats (Single Box with Vertical Dividers) -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 shadow-2xs">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 items-center gap-y-4">
            <!-- Total -->
            <div class="px-4 py-1">
                <span class="text-xs text-[#6E6E73] font-bold uppercase tracking-wider block">Total</span>
                <div class="text-2xl font-extrabold text-[#1D1D1F] mt-0.5">{{ $stats['total'] }}</div>
            </div>

            <!-- Confirmed -->
            <div class="relative px-4 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs text-[#6E6E73] font-bold uppercase tracking-wider block">Confirmed</span>
                <div class="text-2xl font-extrabold text-emerald-700 mt-0.5">{{ $stats['confirmed'] }}</div>
            </div>

            <!-- Rescheduled -->
            <div class="relative px-4 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs text-[#6E6E73] font-bold uppercase tracking-wider block">Rescheduled</span>
                <div class="text-2xl font-extrabold text-[#1D1D1F] mt-0.5">{{ $stats['rescheduled'] }}</div>
            </div>

            <!-- Completed -->
            <div class="relative px-4 py-1">
                <div class="hidden lg:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs text-[#6E6E73] font-bold uppercase tracking-wider block">Completed</span>
                <div class="text-2xl font-extrabold text-[#1D1D1F] mt-0.5">{{ $stats['completed'] }}</div>
            </div>

            <!-- No-Show -->
            <div class="relative px-4 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs text-[#6E6E73] font-bold uppercase tracking-wider block">No-Show</span>
                <div class="text-2xl font-extrabold text-[#6E6E73] mt-0.5">{{ $stats['no_show'] }}</div>
            </div>

            <!-- Cancelled -->
            <div class="relative px-4 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs text-[#6E6E73] font-bold uppercase tracking-wider block">Cancelled</span>
                <div class="text-2xl font-extrabold text-rose-700 mt-0.5">{{ $stats['cancelled'] }}</div>
            </div>
        </div>
    </div>

    <!-- Bookings Table Container with Integrated Toolbar Header -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] overflow-hidden shadow-2xs">
        
        <!-- Integrated Toolbar Header (Pills + Search + Filter Popover) -->
        <div class="p-3 sm:p-4 border-b border-[#E5E5EA]">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                
                <!-- Left: Class Package Tabs -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 lg:pb-0 scrollbar-none">
                    <a href="{{ request()->fullUrlWithQuery(['class_type' => '']) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 {{ !request('class_type') ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                        All Classes
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['class_type' => 'discovery']) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 {{ request('class_type') === 'discovery' ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                        Discovery
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['class_type' => 'fundive']) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 {{ request('class_type') === 'fundive' ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                        Fundive
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['class_type' => 'refinement']) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 {{ request('class_type') === 'refinement' ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                        Refinement
                    </a>
                </div>

                <!-- Right: Search Input + Filter Popover -->
                <div class="flex items-center gap-2" x-data="{ openFilters: false }">
                    <form method="GET" action="{{ route('admin.bookings.index') }}" class="flex items-center gap-2">
                        @if(request('class_type'))
                            <input type="hidden" name="class_type" value="{{ request('class_type') }}">
                        @endif
                        @if(request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                        @if(request('batch_status'))
                            <input type="hidden" name="batch_status" value="{{ request('batch_status') }}">
                        @endif
                        @if(request('sort'))
                            <input type="hidden" name="sort" value="{{ request('sort') }}">
                        @endif

                        <div class="relative w-48 sm:w-64">
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Search booking #, name..." 
                                   class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-[#D1D1D6] bg-[#FAFAFC] focus:bg-white focus:border-[#780000]">
                            <svg class="w-3.5 h-3.5 text-[#8E8E93] absolute left-2.5 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </div>
                    </form>

                    <!-- Filter Button with Popover -->
                    <div class="relative">
                        <button type="button" 
                                @click="openFilters = !openFilters" 
                                class="btn-secondary flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-[#6E6E73]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>
                            <span>Filter & Sort</span>
                            @if(request()->anyFilled(['status', 'batch_status', 'sort']))
                                <span class="w-2 h-2 rounded-full bg-[#780000]"></span>
                            @endif
                        </button>

                        <!-- Filter Popover Menu -->
                        <div x-show="openFilters" 
                             @click.outside="openFilters = false" 
                             x-cloak 
                             class="absolute right-0 mt-2 w-72 bg-white rounded-xl border border-[#E5E5EA] shadow-xl p-4 z-50 space-y-3">
                            <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-2">
                                <h4 class="font-bold text-xs text-[#1D1D1F]">Filter & Sort Bookings</h4>
                                <a href="{{ route('admin.bookings.index') }}" class="text-[11px] text-[#780000] hover:underline font-semibold">Reset</a>
                            </div>

                            <form method="GET" action="{{ route('admin.bookings.index') }}" class="space-y-3 text-xs">
                                @if(request('class_type'))
                                    <input type="hidden" name="class_type" value="{{ request('class_type') }}">
                                @endif
                                @if(request('search'))
                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                @endif

                                <div>
                                    <label class="block font-semibold text-[#6E6E73] mb-1">Sort By</label>
                                    <select name="sort" class="w-full px-2.5 py-1.5 rounded-lg border border-[#D1D1D6] text-xs">
                                        <option value="created_desc" {{ request('sort', 'created_desc') === 'created_desc' ? 'selected' : '' }}>Newest Booking First (Created)</option>
                                        <option value="created_asc" {{ request('sort') === 'created_asc' ? 'selected' : '' }}>Oldest Booking First (Created)</option>
                                        <option value="dive_date_asc" {{ request('sort') === 'dive_date_asc' ? 'selected' : '' }}>Upcoming Dive Date (Soonest First)</option>
                                        <option value="dive_date_desc" {{ request('sort') === 'dive_date_desc' ? 'selected' : '' }}>Past Dive Date (Furthest First)</option>
                                        <option value="amount_desc" {{ request('sort') === 'amount_desc' ? 'selected' : '' }}>Highest Booking Amount (₱)</option>
                                        <option value="amount_asc" {{ request('sort') === 'amount_asc' ? 'selected' : '' }}>Lowest Booking Amount (₱)</option>
                                        <option value="guest_asc" {{ request('sort') === 'guest_asc' ? 'selected' : '' }}>Guest Name (A Z)</option>
                                        <option value="status" {{ request('sort') === 'status' ? 'selected' : '' }}>Booking Status</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block font-semibold text-[#6E6E73] mb-1">Status</label>
                                    <select name="status" class="w-full px-2.5 py-1.5 rounded-lg border border-[#D1D1D6] text-xs">
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

                                <div>
                                    <label class="block font-semibold text-[#6E6E73] mb-1">Batch Assignment</label>
                                    <select name="batch_status" class="w-full px-2.5 py-1.5 rounded-lg border border-[#D1D1D6] text-xs">
                                        <option value="">All Batches</option>
                                        <option value="unassigned" {{ request('batch_status') === 'unassigned' || request('unassigned') === '1' ? 'selected' : '' }}>No Batch (Unassigned)</option>
                                        <option value="assigned" {{ request('batch_status') === 'assigned' ? 'selected' : '' }}>Batch Assigned</option>
                                    </select>
                                </div>

                                <div class="pt-2 border-t border-[#E5E5EA] flex justify-end">
                                    <button type="submit" class="btn-primary w-full py-1.5 text-xs font-bold">
                                        Apply Filter & Sort
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="overflow-x-auto">
        <table class="w-full text-left min-w-[900px]">
            <thead class="bg-[#FAFAFC] border-b border-[#E5E5EA] text-xs uppercase font-bold text-[#6E6E73]">
                <tr>
                    <th class="p-4 pl-6">Booking Number</th>
                    <th class="p-4">Customer Name</th>
                    <th class="p-4">Class Package</th>
                    <th class="p-4">Dive Dates</th>
                    <th class="p-4">Batch Assignment</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 pr-6">Payment Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E5E5EA]">
                @forelse($bookings as $b)
                <tr onclick="window.location='{{ route('admin.bookings.show', $b) }}'" class="hover:bg-[#FAFAFC] cursor-pointer transition-colors text-xs sm:text-sm group">
                    <!-- Booking Number & PIN -->
                    <td class="p-4 pl-6 font-mono">
                        <span class="font-bold text-[#780000] group-hover:underline block text-sm">
                            {{ $b->booking_number }}
                        </span>
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
                    <td class="p-4" onclick="event.stopPropagation()">
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
                        <span class="px-2 py-0.5 rounded text-xs font-bold {{ $b->status_badge['bg'] }}">
                            {{ $b->status_badge['label'] }}
                        </span>
                    </td>

                    <!-- Payment Status -->
                    <td class="p-4 pr-6">
                        <span class="px-2 py-0.5 rounded text-xs font-bold {{ $b->payment_status_badge['class'] }}">
                            {{ $b->payment_status_badge['label'] }}
                        </span>
                        <span class="text-xs font-bold text-[#1D1D1F] block mt-0.5">₱{{ number_format($b->downpayment_amount, 2) }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="p-8 text-center text-[#6E6E73]">
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
