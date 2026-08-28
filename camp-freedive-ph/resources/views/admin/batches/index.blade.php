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

        <div class="flex items-center gap-3 flex-wrap">
            @if(isset($unbatchedCount) && $unbatchedCount > 0)
                <a href="{{ route('admin.bookings.index', ['batch_status' => 'unassigned']) }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-[#FFFBEB] border border-[#FDE68A] text-xs font-bold text-[#92400E] hover:bg-[#FEF3C7] transition-all shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-[#D97706] animate-pulse"></span>
                    <span>{{ $unbatchedCount }} Confirmed Booking(s) Have No Batch Assigned</span>
                </a>
            @endif

            <a href="{{ route('admin.batches.create') }}" 
               class="btn-primary px-5 py-2.5 text-xs sm:text-sm font-bold flex items-center gap-2 shrink-0">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Add Batch</span>
            </a>
        </div>
    </div>

    <!-- Modern Integrated Toolbar (Status Pill Tabs + Search & Filter Popover) -->
    <div class="bg-white rounded-2xl border border-[#E5E5EA] p-3 sm:p-4 shadow-2xs">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            
            <!-- Left: Batch Status Pill Tabs (Primary: #780000) -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 lg:pb-0 scrollbar-none">
                <a href="{{ request()->fullUrlWithQuery(['status' => '']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ !request('status') ? 'bg-[#780000] text-white shadow-xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                    All Batches
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'confirmed']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ request('status') === 'confirmed' ? 'bg-[#780000] text-white shadow-xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                    Confirmed
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'completed']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ request('status') === 'completed' ? 'bg-[#780000] text-white shadow-xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                    Completed
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'rescheduled']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ request('status') === 'rescheduled' ? 'bg-[#780000] text-white shadow-xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                    Rescheduled
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'cancelled_by_camp']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ request('status') === 'cancelled_by_camp' ? 'bg-[#780000] text-white shadow-xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                    Cancelled
                </a>
            </div>

            <!-- Right: Search Input + Filter Popover -->
            <div class="flex items-center gap-2" x-data="{ openFilters: false }">
                <form action="{{ route('admin.batches.index') }}" method="GET" class="flex items-center gap-2">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    @if(request('staffing'))
                        <input type="hidden" name="staffing" value="{{ request('staffing') }}">
                    @endif

                    <div class="relative w-48 sm:w-64">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Search batch (e.g. Batch 4)..." 
                               class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl border border-[#D1D1D6] bg-[#FAFAFC] focus:bg-white focus:border-[#780000] focus:ring-1 focus:ring-[#780000]">
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
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-[#D1D1D6] bg-white hover:bg-[#FAFAFC] text-xs font-bold text-[#1D1D1F] transition-all shadow-2xs cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-[#6E6E73]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                        </svg>
                        <span>Filter</span>
                        @if(request()->anyFilled(['staffing', 'date_from', 'date_to']))
                            <span class="w-2 h-2 rounded-full bg-[#780000]"></span>
                        @endif
                    </button>

                    <!-- Filter Popover Menu -->
                    <div x-show="openFilters" 
                         @click.outside="openFilters = false" 
                         x-cloak 
                         class="absolute right-0 mt-2 w-72 sm:w-80 bg-white rounded-2xl border border-[#E5E5EA] shadow-xl p-4 z-50 space-y-3">
                        <form action="{{ route('admin.batches.index') }}" method="GET" class="space-y-3 text-xs">
                            @if(request('status'))
                                <input type="hidden" name="status" value="{{ request('status') }}">
                            @endif
                            @if(request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif

                            <div>
                                <label class="block font-bold text-[#1D1D1F] mb-2">Staffing Status</label>
                                <select name="staffing" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white font-medium">
                                    <option value="">All Staffing</option>
                                    <option value="staffed" {{ request('staffing') === 'staffed' ? 'selected' : '' }}>Coaches Assigned</option>
                                    <option value="pending" {{ request('staffing') === 'pending' ? 'selected' : '' }}>Coach Pending (0)</option>
                                </select>
                            </div>

                            <div class="flex items-center justify-between pt-2">
                                <a href="{{ route('admin.batches.index') }}" class="text-xs text-[#8E8E93] hover:text-[#1D1D1F]">Reset All</a>
                                <button type="button" @click="openFilters = false" class="btn-secondary px-3 py-1.5 text-xs font-semibold">Done</button>
                            </div>
                        </form>
                    </div>
                </div>

                @if(request()->anyFilled(['search', 'staffing', 'date_from', 'date_to']))
                    <a href="{{ route('admin.batches.index', ['status' => request('status')]) }}" 
                       class="text-xs text-[#6E6E73] hover:text-[#780000] underline font-medium px-1.5 py-1">
                        Reset
                    </a>
                @endif
            </div>

        </div>
    </div>

    <!-- Batches Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
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
