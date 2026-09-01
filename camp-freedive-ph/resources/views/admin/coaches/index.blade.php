@extends('layouts.admin')

@section('title', 'Coach Roster & Schedules | Camp FreedivePH')

@section('content')
<div class="space-y-6 text-sm">
    
    <!-- Top Header & Action Controls -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight">Coach Roster & Schedules</h1>
            <p class="text-xs sm:text-sm text-[#6E6E73] mt-1">
                View certified freediving coaches, student assignments, and availability calendars.
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <!-- Shortcut 1: Matching Queue -->
            <a href="{{ route('admin.coaches.matching') }}" 
               class="btn-primary px-4 py-2.5 text-xs sm:text-sm font-bold flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                <span>Students Needing Coach</span>
                @if($unassignedStudentsCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-xs font-black bg-[#FF3B3C] text-white">
                        {{ $unassignedStudentsCount }}
                    </span>
                @endif
            </a>

            <!-- Shortcut 2: Coach Requests -->
            <a href="{{ route('admin.coaches.requests') }}" 
               class="btn-secondary px-3.5 py-2.5 text-xs sm:text-sm font-semibold flex items-center gap-1.5">
                <svg class="w-4 h-4 text-[#FF8D28]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                <span>Coach Requests</span>
            </a>
        </div>
    </div>

    <!-- Search & Filter Controls (Flat border, no shadow) -->
    <!-- Modern Integrated Toolbar (Status Pill Tabs + Search & Filter Popover) -->
    <div class="bg-white rounded-2xl border border-[#E5E5EA] p-3 sm:p-4 shadow-2xs">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            
            <!-- Left: Account Status Pill Tabs (Primary: #780000) -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 lg:pb-0 scrollbar-none">
                <a href="{{ request()->fullUrlWithQuery(['status' => '']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ !request('status') ? 'bg-[#780000] text-white shadow-xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                    All Coaches ({{ $activeCount + $inactiveCount }})
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'active']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ request('status') === 'active' ? 'bg-[#780000] text-white shadow-xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                    Active ({{ $activeCount }})
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'inactive']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ request('status') === 'inactive' ? 'bg-[#780000] text-white shadow-xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                    Inactive ({{ $inactiveCount }})
                </a>
            </div>

            <!-- Right: Search Input + Filter Popover -->
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
                        @if(request()->filled('available_on'))
                            <span class="w-2 h-2 rounded-full bg-[#780000]"></span>
                        @endif
                    </button>

                    <!-- Filter Popover Menu -->
                    <div x-show="openFilters" 
                         @click.outside="openFilters = false" 
                         x-cloak 
                         class="absolute right-0 mt-2 w-72 sm:w-80 bg-white rounded-2xl border border-[#E5E5EA] shadow-xl p-4 z-50 space-y-3">
                        <form action="{{ route('admin.coaches.index') }}" method="GET" class="space-y-3 text-xs">
                            @if(request('status'))
                                <input type="hidden" name="status" value="{{ request('status') }}">
                            @endif
                            @if(request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif

                            <div>
                                <label class="block font-bold text-[#1D1D1F] mb-2">Available on Specific Date</label>
                                <input type="date" 
                                       name="available_on" 
                                       onchange="this.form.submit()"
                                       value="{{ request('available_on') }}" 
                                       class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white font-medium">
                            </div>

                            <div class="flex items-center justify-between pt-2">
                                <a href="{{ route('admin.coaches.index') }}" class="text-xs text-[#8E8E93] hover:text-[#1D1D1F]">Reset All</a>
                                <button type="button" @click="openFilters = false" class="btn-secondary px-3 py-1.5 text-xs font-semibold">Done</button>
                            </div>
                        </form>
                    </div>
                </div>

                @if(request()->anyFilled(['search', 'available_on']))
                    <a href="{{ route('admin.coaches.index', ['status' => request('status')]) }}" 
                       class="text-xs text-[#6E6E73] hover:text-[#780000] underline font-medium px-1.5 py-1">
                        Reset
                    </a>
                @endif
            </div>

        </div>
    </div>

    <!-- Coach Roster Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
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
        
        <div onclick="window.location='{{ route('admin.coaches.show', $coach) }}'" class="bg-white rounded-2xl border border-[#E5E5EA] p-5 hover:border-[#780000]/40 hover:shadow-md cursor-pointer transition-all flex flex-col justify-between space-y-4 group">
            
            <!-- Card Header: Avatar, Name & Status -->
            <div>
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-[#F8EAEA] text-[#780000] border border-[#780000] flex items-center justify-center font-extrabold text-lg shrink-0 group-hover:scale-105 transition-transform shadow-2xs">
                            {{ substr($coach->name, 0, 1) }}
                        </div>
                        <div>
                            <span class="font-extrabold text-[#1D1D1F] group-hover:text-[#780000] text-base block leading-tight transition-colors">
                                {{ $coach->name }}
                            </span>
                            @if($coach->nickname)
                                <span class="text-xs font-semibold text-[#008E98]">"{{ $coach->nickname }}"</span>
                            @endif
                        </div>
                    </div>

                    <!-- Status Pill -->
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border shrink-0 {{ $coach->status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                        {{ ucfirst($coach->status) }}
                    </span>
                </div>

                <!-- Metrics & Details -->
                <div class="mt-4 pt-3 border-t border-[#E5E5EA] space-y-2 text-xs">
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
                            <span class="font-semibold {{ $loadOnNext > ($coach->max_ratio ?? 4) ? 'text-[#FF3B3C]' : 'text-[#065F46]' }}">
                                {{ $loadOnNext }} / {{ $coach->max_ratio ?? 4 }} Students
                            </span>
                            @if($loadOnNext > ($coach->max_ratio ?? 4))
                                <span class="text-[11px] font-bold text-[#FF3B3C] block">Override Exception</span>
                            @endif
                        </div>
                    @endif

                    <!-- Availability Summary -->
                    <div class="flex items-center justify-between pt-1 border-t border-[#E5E5EA]">
                        <span class="text-[#6E6E73]">Calendar Availability:</span>
                        @if($markedAvailableCount > 0)
                            <span class="px-2 py-0.5 rounded-full font-bold text-[#065F46] text-[11px]">
                                {{ $markedAvailableCount }} Open Dates
                            </span>
                        @else
                            <span class="text-[#8E8E93] text-[11px]">0 future dates open</span>
                        @endif
                    </div>
                </div>
            </div>

        </div>
        @empty
        <div class="col-span-full py-12 text-center text-[#6E6E73] bg-white rounded-2xl border border-[#E5E5EA]">
            <p class="text-base font-bold text-[#1D1D1F]">No coaches found</p>
            <p class="text-xs text-[#6E6E73] mt-1">Try adjusting your search criteria or clear active filters.</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pt-2">
        {{ $coaches->links() }}
    </div>

</div>
@endsection
