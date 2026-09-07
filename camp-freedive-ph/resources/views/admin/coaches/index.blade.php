@extends('layouts.admin')

@section('title', 'Coach Roster & Schedules | Camp FreedivePH')

@section('content')
<div class="space-y-6 text-sm">
    
    <!-- Header and Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight">Coach Roster & Schedules</h1>
            <p class="text-xs sm:text-sm text-[#6E6E73] mt-1">
                View certified freediving coaches, student assignments, and availability calendars.
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <!-- Matching Queue Shortcut -->
            <a href="{{ route('admin.coaches.matching') }}" 
               class="btn-primary px-4 py-2 text-xs sm:text-sm font-bold flex items-center gap-2 shadow-2xs">
                <span>Students Needing Coach</span>
                @if($unassignedStudentsCount > 0)
                    <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-white text-[#780000]">
                        {{ $unassignedStudentsCount }}
                    </span>
                @endif
            </a>

            <!-- Coach Requests Shortcut -->
            <a href="{{ route('admin.coaches.requests') }}" 
               class="btn-secondary px-3.5 py-2 text-xs sm:text-sm font-semibold flex items-center gap-1.5">
                <span>Coach Requests</span>
            </a>
        </div>
    </div>

    <!-- Toolbar and Filter Controls -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] p-3 shadow-2xs">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            
            <!-- Status Filter Tabs -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 lg:pb-0 scrollbar-none">
                <a href="{{ request()->fullUrlWithQuery(['status' => '']) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 {{ !request('status') ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                    All Coaches ({{ $activeCount + $inactiveCount }})
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'active']) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 {{ request('status') === 'active' ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                    Active ({{ $activeCount }})
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'inactive']) }}" 
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 {{ request('status') === 'inactive' ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                    Inactive ({{ $inactiveCount }})
                </a>
            </div>

            <!-- Search and Filter Controls -->
            <div class="flex items-center gap-2" x-data="{ openFilters: false }">
                <form action="{{ route('admin.coaches.index') }}" method="GET" class="flex items-center gap-2">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    @if(request('available_on'))
                        <input type="hidden" name="available_on" value="{{ request('available_on') }}">
                    @endif

                    <div class="relative w-48 sm:w-64">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Search coach name, email..." 
                               class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-[#D1D1D6] bg-[#FAFAFC] focus:bg-white focus:border-[#780000]">
                        <svg class="w-3.5 h-3.5 text-[#8E8E93] absolute left-2.5 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </div>
                </form>

                <!-- Filter Controls -->
                <div class="relative">
                    <button type="button" 
                            @click="openFilters = !openFilters" 
                            class="btn-secondary flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-[#6E6E73]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                        </svg>
                        <span>Filter</span>
                        @if(request()->filled('available_on'))
                            <span class="w-2 h-2 rounded-full bg-[#780000]"></span>
                        @endif
                    </button>

                    <!-- Filter Form Dropdown -->
                    <div x-show="openFilters" 
                         @click.outside="openFilters = false" 
                         x-cloak 
                         class="absolute right-0 mt-2 w-72 bg-white rounded-xl border border-[#E5E5EA] shadow-xl p-4 z-50 space-y-3">
                        <div class="flex items-center justify-between pb-2">
                            <h4 class="font-bold text-xs text-[#1D1D1F]">Filter Coaches</h4>
                            <a href="{{ route('admin.coaches.index') }}" class="text-[11px] text-[#780000] hover:underline font-semibold">Reset</a>
                        </div>

                        <form action="{{ route('admin.coaches.index') }}" method="GET" class="space-y-3 text-xs">
                            @if(request('status'))
                                <input type="hidden" name="status" value="{{ request('status') }}">
                            @endif
                            @if(request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif

                            <div>
                                <label class="block font-semibold text-[#6E6E73] mb-1">Available on Specific Date</label>
                                <input type="date" 
                                       name="available_on" 
                                       value="{{ request('available_on') }}" 
                                       class="w-full px-2.5 py-1.5 rounded-lg border border-[#D1D1D6] text-xs">
                            </div>

                            <div class="pt-2 border-t border-[#E5E5EA] flex justify-end">
                                <button type="submit" class="btn-primary w-full py-1.5 text-xs font-bold">
                                    Apply Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Coach Roster -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
        @forelse($coaches as $coach)
        @php
            $upcomingCount = $coach->assignedParticipants()
                ->where('status', 'assigned')
                ->where('dive_date', '>=', now()->toDateString())
                ->distinct('dive_date')
                ->count('dive_date');

            $nextAssignment = $coach->assignedParticipants()
                ->where('status', 'assigned')
                ->where('dive_date', '>=', now()->toDateString())
                ->with('batch')
                ->orderBy('dive_date', 'asc')
                ->first();

            $loadOnNext = $nextAssignment ? $coach->assignedCountForDate($nextAssignment->dive_date) : 0;
            $markedAvailableCount = $coach->coachAvailabilities->where('status', 'available')->where('date', '>=', now()->toDateString())->count();
        @endphp
        
        <div onclick="window.location='{{ route('admin.coaches.show', $coach) }}'" class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-5 hover:border-[#780000] cursor-pointer transition-all flex flex-col justify-between space-y-3 shadow-2xs group">
            
            <!-- Coach Header -->
            <div>
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-full bg-[#F8EAEA] text-[#780000] border border-[#780000] flex items-center justify-center font-extrabold text-base shrink-0 shadow-2xs">
                            {{ substr($coach->name, 0, 1) }}
                        </div>
                        <div>
                            <span class="font-extrabold text-[#1D1D1F] group-hover:text-[#780000] text-base block leading-tight transition-colors">
                                {{ $coach->name }}
                            </span>
                            @if($coach->nickname)
                                <span class="text-xs font-semibold text-[#780000]">"{{ $coach->nickname }}"</span>
                            @endif
                        </div>
                    </div>

                    <!-- Status Badge -->
                    <span class="px-2 py-0.5 rounded-md text-xs font-bold border shrink-0 {{ $coach->status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                        {{ ucfirst($coach->status) }}
                    </span>
                </div>

                <!-- Coach Metrics -->
                <div class="mt-3.5 pt-3 border-t border-[#E5E5EA] space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-[#6E6E73]">Upcoming Active Dates:</span>
                        <strong class="text-[#1D1D1F]">{{ $upcomingCount }} scheduled</strong>
                    </div>

                    @if($nextAssignment)
                        <div class="flex items-center justify-between">
                            <span class="text-[#6E6E73]">Next Dive Batch:</span>
                            <span class="font-bold text-[#1D1D1F]">
                                {{ \Carbon\Carbon::parse($nextAssignment->dive_date)->format('M d, Y') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[#6E6E73]">Next Batch Load:</span>
                            <span class="font-semibold {{ $loadOnNext > ($coach->max_ratio ?? 4) ? 'text-rose-700' : 'text-emerald-700' }}">
                                {{ $loadOnNext }} / {{ $coach->max_ratio ?? 4 }} Students
                            </span>
                            @if($loadOnNext > ($coach->max_ratio ?? 4))
                                <span class="text-[11px] font-bold text-rose-700 block">Override Exception</span>
                            @endif
                        </div>
                    @endif

                    <!-- Availability Summary -->
                    <div class="flex items-center justify-between pt-1 border-t border-[#E5E5EA]">
                        <span class="text-[#6E6E73]">Calendar Availability:</span>
                        @if($markedAvailableCount > 0)
                            <span class="font-bold text-emerald-700 text-xs">
                                {{ $markedAvailableCount }} Open Dates
                            </span>
                        @else
                            <span class="text-[#8E8E93] text-xs">0 future dates open</span>
                        @endif
                    </div>
                </div>
            </div>

        </div>
        @empty
        <div class="col-span-full py-10 text-center text-[#6E6E73] bg-white rounded-xl border border-[#E5E5EA]">
            <p class="text-base font-bold text-[#1D1D1F]">No coaches found</p>
            <p class="text-xs text-[#6E6E73] mt-1">Try adjusting your search criteria or clear active filters.</p>
        </div>
        @endforelse
    </div>

    <!-- Table Pagination -->
    <div class="bg-[#FAFAFC] rounded-xl border border-[#E5E5EA] overflow-hidden shadow-xs [&>*]:border-t-0">
        {{ $coaches->links() }}
    </div>

</div>
@endsection
