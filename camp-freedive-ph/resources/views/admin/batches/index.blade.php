@extends('layouts.admin')

@section('title', 'Batches & Schedules | Camp FreedivePH')

@section('content')
<div class="space-y-6 text-sm">
    
    <!-- Top Header & Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight">Batches & Schedules</h1>
            <p class="text-xs sm:text-sm text-[#6E6E73] mt-1">
                Organize weekend dive trips, group guest bookings, and check coach assignments and occupancy.
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            @if(isset($unbatchedCount) && $unbatchedCount > 0)
                <a href="{{ route('admin.bookings.index', ['batch_status' => 'unassigned']) }}" 
                   class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-amber-50 border border-amber-200 text-xs font-bold text-amber-900 hover:bg-amber-100 transition-all shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                    <span>{{ $unbatchedCount }} Confirmed Booking(s) Have No Batch Assigned</span>
                </a>
            @endif

            <a href="{{ route('admin.batches.create') }}" 
               class="btn-primary px-4 py-2 text-xs sm:text-sm font-bold flex items-center gap-1.5 shrink-0 shadow-2xs">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Add Batch</span>
            </a>
        </div>
    </div>

    <!-- Search and Filter Toolbar -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] p-2.5 sm:p-3 shadow-2xs">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-2.5 sm:gap-3">
            
            <!-- Batch Status Filters -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 lg:pb-0 scrollbar-none -mx-0.5 px-0.5">
                <a href="{{ request()->fullUrlWithQuery(['status' => '']) }}" 
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 {{ !request('status') ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                    All Batches
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'confirmed']) }}" 
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 {{ request('status') === 'confirmed' ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                    Confirmed
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'completed']) }}" 
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 {{ request('status') === 'completed' ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                    Completed
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'rescheduled']) }}" 
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 {{ request('status') === 'rescheduled' ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                    Rescheduled
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'cancelled_by_camp']) }}" 
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 {{ request('status') === 'cancelled_by_camp' ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                    Cancelled
                </a>
            </div>

            <!-- Search and Advanced Filters -->
            <div class="flex items-center gap-2 w-full lg:w-auto" x-data="{ openFilters: false }">
                <form action="{{ route('admin.batches.index') }}" method="GET" class="flex-1 min-w-0 lg:flex-initial">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    @if(request('staffing'))
                        <input type="hidden" name="staffing" value="{{ request('staffing') }}">
                    @endif
                    @if(request('sort'))
                        <input type="hidden" name="sort" value="{{ request('sort') }}">
                    @endif

                    <div class="relative w-full sm:w-64">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Search batch (e.g. Batch 4)..." 
                               class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-[#D1D1D6] bg-[#FAFAFC] focus:bg-white focus:border-[#780000] shadow-xs">
                        <svg class="w-3.5 h-3.5 text-[#8E8E93] absolute left-2.5 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </div>
                </form>

                <!-- Advanced Filter Toggle -->
                <div class="relative shrink-0">
                    <button type="button" 
                            @click="openFilters = !openFilters" 
                            class="btn-secondary flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold whitespace-nowrap shrink-0 cursor-pointer shadow-xs">
                        <svg class="w-3.5 h-3.5 text-[#6E6E73] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                        </svg>
                        <span class="whitespace-nowrap">Filter & Sort</span>
                        @if(request()->anyFilled(['staffing', 'sort', 'date_from', 'date_to']))
                            <span class="w-2 h-2 rounded-full bg-[#780000] shrink-0"></span>
                        @endif
                    </button>

                    <!-- Advanced Filter Options -->
                    <div x-show="openFilters" 
                         x-cloak 
                         @click.outside="openFilters = false" 
                         class="absolute right-0 mt-2 w-[calc(100vw-2rem)] sm:w-80 max-w-sm bg-white rounded-xl shadow-xl border border-[#E5E5EA] p-4 z-30 space-y-3">
                        <div class="flex items-center justify-between pb-2">
                            <h4 class="font-bold text-xs text-[#1D1D1F]">Filter & Sort Batches</h4>
                            <a href="{{ route('admin.batches.index') }}" class="text-[11px] text-[#780000] hover:underline font-semibold">Reset</a>
                        </div>

                        <form action="{{ route('admin.batches.index') }}" method="GET" class="space-y-3 text-xs">
                            @if(request('status'))
                                <input type="hidden" name="status" value="{{ request('status') }}">
                            @endif
                            @if(request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif

                            <div>
                                <label class="block font-semibold text-[#6E6E73] mb-1">Sort By</label>
                                <select name="sort" class="w-full px-2.5 py-1.5 rounded-lg border border-[#D1D1D6] text-xs">
                                    <option value="date_asc" {{ request('sort', 'date_asc') === 'date_asc' ? 'selected' : '' }}>Soonest Dive Date (Upcoming First)</option>
                                    <option value="date_desc" {{ request('sort') === 'date_desc' ? 'selected' : '' }}>Latest Dive Date (Newest to Oldest)</option>
                                    <option value="batch_asc" {{ request('sort') === 'batch_asc' ? 'selected' : '' }}>Batch Number (Ascending)</option>
                                    <option value="batch_desc" {{ request('sort') === 'batch_desc' ? 'selected' : '' }}>Batch Number (Descending)</option>
                                    <option value="capacity_desc" {{ request('sort') === 'capacity_desc' ? 'selected' : '' }}>Highest Occupancy / Pax</option>
                                    <option value="capacity_asc" {{ request('sort') === 'capacity_asc' ? 'selected' : '' }}>Lowest Occupancy / Pax</option>
                                    <option value="created_desc" {{ request('sort') === 'created_desc' ? 'selected' : '' }}>Recently Created</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-semibold text-[#6E6E73] mb-1">Staffing Status</label>
                                <select name="staffing" class="w-full px-2.5 py-1.5 rounded-lg border border-[#D1D1D6] text-xs">
                                    <option value="">All Staffing States</option>
                                    <option value="pending" {{ request('staffing') === 'pending' ? 'selected' : '' }}>Instructor Pending</option>
                                    <option value="staffed" {{ request('staffing') === 'staffed' ? 'selected' : '' }}>Fully Staffed</option>
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

    <!-- Batches List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        @forelse($batches as $batch)
        <div onclick="window.location='{{ route('admin.batches.show', $batch) }}'" class="rounded-xl border border-[#E5E5EA] p-4 sm:p-5 hover:border-[#780000] cursor-pointer transition-all flex flex-col justify-between space-y-3 shadow-2xs group bg-white">
            
            <!-- Batch Information -->
            <div>
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <span class="font-extrabold text-[#1D1D1F] group-hover:text-[#780000] text-base block leading-tight transition-colors">
                            {{ $batch->batch_number }}
                        </span>
                    </div>
                    <span class="px-2 py-0.5 rounded-md text-xs font-bold border shrink-0 {{ $batch->status_badge['class'] }}">
                        {{ $batch->status_badge['label'] }}
                    </span>
                </div>
                <div class="mt-1">
                    <strong class="text-[#1D1D1F] font-bold text-xs">{{ $batch->start_date->format('M d') }} – {{ $batch->end_date->format('M d, Y') }}</strong>
                </div>
                @if($batch->capacity_note)
                    <div class="mt-1.5 text-[11px] text-[#6E6E73] italic">
                        {{ $batch->capacity_note }}
                    </div>
                @endif

                <!-- Metrics and Operational Status -->
                <div class="mt-3 space-y-2 text-xs">

                    <!-- Staffing and Capacity Overview -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-[#1D1D1F]">
                                {{ $batch->total_participants_count }} Pax ({{ $batch->bookings->whereNotIn('status', ['cancelled_by_camp', 'cancelled_by_guest'])->count() }} bookings)
                            </span>
                            @if($batch->total_participants_count > 0 && $batch->is_coach_pending)
                                <span class="text-xs font-bold text-amber-700">
                                    Instructor Pending
                                </span>
                            @elseif($batch->assigned_coaches_count > 0)
                                <span class="text-xs text-emerald-700 font-bold">
                                    {{ $batch->assigned_coaches_count }} Coach(es)
                                </span>
                            @else
                                <span class="text-xs text-[#8E8E93] font-medium">
                                    No Bookings Yet
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between text-xs text-[#6E6E73]">
                            <span>{{ $batch->occupancy_percentage }}% Full</span>
                            <span class="text-[#8E8E93]">{{ $batch->remaining_capacity }} free slot(s)</span>
                        </div>
                        <div class="w-full bg-[#E5E5EA] rounded-full h-1.5 overflow-hidden">
                            <div class="h-1.5 rounded-full {{ $batch->occupancy_percentage >= 100 ? 'bg-[#780000]' : ($batch->occupancy_percentage > 70 ? 'bg-amber-600' : 'bg-emerald-600') }}" 
                                 style="width: {{ min(100, $batch->occupancy_percentage ?? 0) }}%"></div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
        @empty
        <div class="col-span-full py-10 text-center text-[#6E6E73] bg-white rounded-xl border border-[#E5E5EA]">
            <p class="text-base font-bold text-[#1D1D1F]">No batches found</p>
            <p class="text-xs text-[#6E6E73] mt-1">Try adjusting your search criteria or create a new batch schedule.</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="bg-[#FAFAFC] rounded-xl border border-[#E5E5EA] overflow-hidden shadow-xs [&>*]:border-t-0">
        {{ $batches->links() }}
    </div>

</div>
@endsection
