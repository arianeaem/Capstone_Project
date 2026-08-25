@extends('layouts.admin')

@section('title', 'Batches & 2D1N Schedules | Camp FreedivePH')

@section('content')
<div class="space-y-6 text-sm">
    
    <!-- Top Header & Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight">Batches & 2D1N Schedules</h1>
            <p class="text-xs sm:text-sm text-[#6E6E73] mt-1">
                Organize weekend dive trips, group guest bookings, and check coach assignments and occupancy.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.batches.create') }}" 
               class="btn-primary px-5 py-2.5 text-xs sm:text-sm font-bold flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Add Batch</span>
            </a>
        </div>
    </div>

    <!-- Unassigned Bookings Notification Banner -->
    @if(isset($unbatchedCount) && $unbatchedCount > 0)
    <div class="rounded-2xl border border-[#FDE68A] bg-[#FFFBEB] p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start gap-3.5">
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="font-extrabold text-[#92400E] text-sm sm:text-base">
                        {{ $unbatchedCount }} Confirmed Booking(s) Have No Batch Assigned
                    </h3>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-[#FDE68A] text-[#78350F]">
                        {{ $unbatchedPaxCount }} Total Participant(s)
                    </span>
                </div>
                <div class="mt-2.5 flex items-center gap-2 flex-wrap">
                    @foreach($unbatchedBookings->take(4) as $ub)
                        <a href="{{ route('admin.bookings.show', $ub) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-[#FDE68A] text-xs font-medium text-[#78350F] hover:bg-[#FEF3C7] transition-colors">
                            <span class="font-mono font-bold text-[#780000]">{{ $ub->booking_number }}</span>
                            <span>({{ $ub->start_date->format('M d') }} to {{ $ub->end_date->format('M d') }} • {{ $ub->participants->count() }}pax)</span>
                        </a>
                    @endforeach
                    @if($unbatchedCount > 4)
                        <span class="text-xs text-[#A16207] font-semibold">+{{ $unbatchedCount - 4 }} more</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('admin.bookings.index', ['batch_status' => 'unassigned']) }}" class="btn-secondary px-3.5 py-2 text-xs font-bold whitespace-nowrap">
                View Unassigned →
            </a>
        </div>
    </div>
    @endif

    <!-- Search & Filter Controls -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-5 space-y-4">
        <form action="{{ route('admin.batches.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            
            <!-- Search -->
            <div>
                <label class="block font-bold text-[#1D1D1F] mb-1">Search Batch</label>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Batch number (e.g. Batch 4...)"
                       class="w-full px-3.5 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block font-bold text-[#1D1D1F] mb-1">Batch Status</label>
                <select name="status" class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                    <option value="">All Statuses</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed (Active)</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="rescheduled" {{ request('status') === 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                    <option value="cancelled_by_camp" {{ request('status') === 'cancelled_by_camp' ? 'selected' : '' }}>Cancelled by Camp</option>
                </select>
            </div>

            <!-- Staffing Status -->
            <div>
                <label class="block font-bold text-[#1D1D1F] mb-1">Staffing Status</label>
                <select name="staffing" class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                    <option value="">All Staffing</option>
                    <option value="staffed" {{ request('staffing') === 'staffed' ? 'selected' : '' }}>Coaches Assigned</option>
                    <option value="pending" {{ request('staffing') === 'pending' ? 'selected' : '' }}>Coach Pending (0)</option>
                </select>
            </div>

            <!-- Filter Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary px-4 py-2 text-xs font-bold w-full">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'status', 'staffing', 'date_from', 'date_to']))
                    <a href="{{ route('admin.batches.index') }}" class="btn-secondary px-3 py-2 text-xs text-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Batches Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($batches as $batch)
        <div class="bg-white rounded-2xl border border-[#E5E5EA] p-5 hover:border-[#008E98]/40 transition-all flex flex-col justify-between space-y-4 {{ $batch->needs_attention ? 'border-[#FDE68A] bg-[#FFFDF7]' : '' }}">
            
            <!-- Card Header: Title & Status -->
            <div>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <a href="{{ route('admin.batches.show', $batch) }}" class="font-extrabold text-[#1D1D1F] hover:text-[#780000] text-base block leading-tight">
                            {{ $batch->batch_number }}
                        </a>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border shrink-0 {{ $batch->status_badge['class'] }}">
                        {{ $batch->status_badge['label'] }}
                    </span>
                </div>
                <div>
                    <strong class="text-[#1D1D1F] font-bold text-xs">{{ $batch->start_date->format('M d') }} – {{ $batch->end_date->format('M d, Y') }}</strong>
                </div>
                @if($batch->capacity_note)
                    <div class="mt-2 text-[11px] text-[#6E6E73] italic">
                        {{ $batch->capacity_note }}
                    </div>
                @endif

                <!-- Metrics & Operational Status -->
                <div class="mt-3.5 space-y-2.5 text-xs">

                    <!-- Staffing & Capacity Meter (Max 45 Pax) -->
                    <div class="pt-2 border-t border-[#E5E5EA] space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-[#1D1D1F]">
                                {{ $batch->total_participants_count }} Pax ({{ $batch->bookings->whereNotIn('status', ['cancelled_by_camp', 'cancelled_by_guest'])->count() }} bookings)
                            </span>
                            @if($batch->is_coach_pending)
                                <span class="text-xs font-bold text-[#D97706] px-2 py-0.5 rounded-full">
                                    Instructor Pending
                                </span>
                            @else
                                <span class="text-xs text-[#065F46] font-bold">
                                    {{ $batch->assigned_coaches_count }} Coach(es)
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-[#6E6E73] pt-0.5">
                            <span>{{ $batch->occupancy_percentage }}% Full</span>
                            <span class="text-[#8E8E93]">{{ $batch->remaining_capacity }} free slot(s)</span>
                        </div>
                        <div class="w-full bg-[#E5E5EA] rounded-full h-1.5 overflow-hidden">
                            <div class="h-1.5 rounded-full {{ $batch->occupancy_percentage >= 100 ? 'bg-[#FF3B3C]' : ($batch->occupancy_percentage > 70 ? 'bg-[#FF8D28]' : 'bg-[#34C759]') }}" 
                                 style="width: {{ min(100, $batch->occupancy_percentage ?? 0) }}%"></div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Card Footer: View Batch Button (Inline flex with guaranteed no-wrap) -->
            <div class="pt-2]">
                <a href="{{ route('admin.batches.show', $batch) }}" 
                   class="w-full py-2.5 px-4 rounded-xl font-bold text-xs text-center flex items-center justify-center gap-1.5 whitespace-nowrap btn-secondary hover:bg-[#F2F2F7] transition-all">
                    <span>View Batch</span>
                    <span>→</span>
                </a>
            </div>

        </div>
        @empty
        <div class="col-span-full py-12 text-center text-[#6E6E73] bg-white rounded-2xl border border-[#E5E5EA]">
            <p class="text-base font-bold text-[#1D1D1F]">No batches found</p>
            <p class="text-xs text-[#6E6E73] mt-1">Try adjusting your search criteria or create a new batch schedule.</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pt-2">
        {{ $batches->links() }}
    </div>

</div>
@endsection
