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
    <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-5 space-y-4">
        <form action="{{ route('admin.coaches.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            
            <!-- Search -->
            <div>
                <label class="block font-bold text-[#1D1D1F] mb-1">Search Coach</label>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Name, email, or phone..." 
                       class="w-full px-3.5 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block font-bold text-[#1D1D1F] mb-1">Account Status</label>
                <select name="status" onchange="this.form.submit()" class="w-full px-3.5 pr-10 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white font-medium">
                    <option value="">All Statuses ({{ $activeCount + $inactiveCount }})</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only ({{ $activeCount }})</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only ({{ $inactiveCount }})</option>
                </select>
            </div>

            <!-- Date Filter -->
            <div>
                <label class="block font-bold text-[#1D1D1F] mb-1">Available on Specific Date</label>
                <input type="date" 
                       name="available_on" 
                       onchange="this.form.submit()"
                       value="{{ request('available_on') }}" 
                       class="w-full px-3.5 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white font-medium">
            </div>

            <!-- Reset Button (If Filtered) -->
            <div class="flex items-end">
                @if(request()->anyFilled(['search', 'status', 'available_on', 'has_capacity']))
                    <a href="{{ route('admin.coaches.index') }}" class="btn-secondary px-4 py-2 text-xs text-center w-full block font-semibold">
                        Clear Filters
                    </a>
                @endif
            </div>
        </form>
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
        
        <div class="bg-white rounded-2xl border border-[#E5E5EA] p-5 hover:border-[#008E98]/40 transition-all flex flex-col justify-between space-y-4">
            
            <!-- Card Header: Avatar, Name & Status -->
            <div>
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#780000] to-[#A00000] text-white flex items-center justify-center font-bold text-lg shrink-0">
                            {{ substr($coach->name, 0, 1) }}
                        </div>
                        <div>
                            <a href="{{ route('admin.coaches.show', $coach) }}" class="font-extrabold text-[#1D1D1F] hover:text-[#780000] text-base block leading-tight">
                                {{ $coach->name }}
                            </a>
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

                <!-- Contact Details -->
                <div class="mt-4 pt-3 border-t border-[#F2F2F7] space-y-1 text-xs text-[#6E6E73]">
                    <div class="flex items-center gap-2 truncate">
                        <svg class="w-3.5 h-3.5 text-[#8E8E93] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        <span class="truncate">{{ $coach->email }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-[#8E8E93] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                        <span>{{ $coach->phone ?: 'No phone recorded' }}</span>
                    </div>
                </div>

                <!-- Schedule & Student Load Stats -->
                <div class="mt-4 p-3 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] space-y-2.5 text-xs">
                    <!-- Upcoming Dives -->
                    <div class="flex items-center justify-between">
                        <span class="text-[#6E6E73]">Upcoming Dive Dates:</span>
                        @if($upcomingCount > 0)
                            <span class="font-bold text-[#1D1D1F]">{{ $upcomingCount }} Date(s)</span>
                        @else
                            <span class="text-[#8E8E93] italic">None scheduled</span>
                        @endif
                    </div>

                    <!-- Next Student Load -->
                    @if($nextAssignment)
                        <div class="space-y-1 pt-1 border-t border-[#E5E5EA]">
                            <div class="flex items-center justify-between">
                                <span class="text-[#6E6E73]">Next Assignment ({{ $nextAssignment->dive_date->format('M d') }}):</span>
                                <span class="font-bold text-[#1D1D1F]">{{ $loadOnNext }} / 4 Pax</span>
                            </div>
                            <div class="w-full bg-[#E5E5EA] rounded-full h-1.5 overflow-hidden">
                                <div class="h-1.5 rounded-full {{ $loadOnNext > 4 ? 'bg-[#FF3B3C]' : ($loadOnNext === 4 ? 'bg-[#34C759]' : 'bg-[#0088FF]') }}" 
                                     style="width: {{ min(100, ($loadOnNext / 4) * 100) }}%"></div>
                            </div>
                            @if($loadOnNext > 4)
                                <span class="text-[11px] font-bold text-[#FF3B3C] block">Override Exception (Over Capacity)</span>
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

            <!-- Card Footer Actions -->
            <div class="pt-2 border-t border-[#F2F2F7] flex items-center justify-between gap-2">
                <a href="{{ route('admin.coaches.show', $coach) }}" 
                   class="w-full py-2 px-3 rounded-xl font-bold text-xs text-center flex items-center justify-center gap-1.5 whitespace-nowrap btn-secondary hover:bg-[#F2F2F7] transition-all">
                    <span>View Profile</span>
                    <span>→</span>
                </a>
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
