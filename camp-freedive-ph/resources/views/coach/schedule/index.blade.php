@extends('layouts.admin')

@section('title', 'My Assigned Schedule | Coach Portal')

@section('content')
<div class="space-y-6" x-data="{ 
    activeTab: '{{ $activeTab }}', 
    releaseModalOpen: false, 
    selectedBatch: null, 
    openFilters: false,
    openBatches: [],
    toggleBatch(batchId) {
        if (this.openBatches.includes(batchId)) {
            this.openBatches = this.openBatches.filter(id => id !== batchId);
        } else {
            this.openBatches.push(batchId);
        }
    },
    isBatchOpen(batchId) {
        return this.openBatches.includes(batchId);
    }
}">
    
    <!-- Top Header & Tabs -->
    <div class="bg-white rounded-2xl p-4 sm:p-6 border border-[#E5E5EA] flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-[#1D1D1F] mt-1">My Assigned Schedule & Student Rosters</h1>
            <p class="text-xs text-[#6E6E73] leading-relaxed">
                Review your upcoming dive assignments, student health conditions, live weather safety ratings, and historical dive records.
            </p>
        </div>

        <!-- Tab Controls -->
        <div class="flex items-center bg-[#F2F2F7] rounded-xl border border-[#E5E5EA] p-1 w-full md:w-auto">
            <button type="button" 
                    @click="activeTab = 'upcoming'"
                    :class="activeTab === 'upcoming' ? 'bg-white text-[#1D1D1F] font-bold' : 'text-[#6E6E73] font-semibold hover:text-[#1D1D1F]'"
                    class="flex-1 md:flex-initial px-3 sm:px-4 py-2 rounded-lg text-xs transition-all flex items-center justify-center gap-2">
                <span>Upcoming Dives ({{ count($upcomingBatches) }})</span>
            </button>
            <button type="button" 
                    @click="activeTab = 'history'"
                    :class="activeTab === 'history' ? 'bg-white text-[#1D1D1F] font-bold' : 'text-[#6E6E73] font-semibold hover:text-[#1D1D1F]'"
                    class="flex-1 md:flex-initial px-3 sm:px-4 py-2 rounded-lg text-xs transition-all flex items-center justify-center gap-2">
                <span>Past History ({{ $totalCompletedBatchesCount }})</span>
            </button>
        </div>
    </div>

    <!-- 1. UPCOMING CONFIRMED ASSIGNMENTS (Batch Card that opens Assigned Student Roster on click) -->
    <div x-show="activeTab === 'upcoming'" class="space-y-4">
        @forelse($upcomingBatches as $item)
            @php
                $batch = $item['batch'];
                $students = $item['students'];
                $weatherBadge = $item['weather_badge'];
                $assessment = $item['assessment'];
                $releaseReq = $item['release_request'];

                $wClass = strtolower(trim($item['weather_class'] ?? 'Safe'));
                if (str_contains($wClass, 'safe')) {
                    $wBg = 'bg-emerald-100 text-emerald-900';
                    $wDot = 'bg-emerald-600';
                } elseif (str_contains($wClass, 'mod') || str_contains($wClass, 'adv') || str_contains($wClass, 'warn') || str_contains($wClass, 'caut')) {
                    $wBg = 'bg-amber-100 text-amber-900';
                    $wDot = 'bg-amber-600';
                } elseif (str_contains($wClass, 'risk') || str_contains($wClass, 'crit') || str_contains($wClass, 'high') || str_contains($wClass, 'dan')) {
                    $wBg = 'bg-rose-100 text-rose-900';
                    $wDot = 'bg-rose-600';
                } else {
                    $wBg = 'bg-emerald-100 text-emerald-900';
                    $wDot = 'bg-emerald-600';
                }
            @endphp

            <div class="bg-white rounded-2xl border border-[#E5E5EA] hover:border-[#D1D1D6] transition-all overflow-hidden">
                
                <!-- Clickable Batch Header Banner -->
                <div class="p-4 sm:p-6 cursor-pointer select-none flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white hover:bg-[#FAFAFC] transition-colors"
                     @click="toggleBatch({{ $batch->id }})">
                    
                    <!-- Left Column: Weather + Batch Title + Dive Dates & Students -->
                    <div class="space-y-2.5 flex-1 min-w-0">
                        <!-- Weather Safety Badge & Description -->
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-black {{ $wBg }} inline-flex items-center gap-1.5 shadow-2xs shrink-0">
                                <span class="w-2 h-2 rounded-full {{ $wDot }} animate-pulse"></span>
                                <span>{{ $item['weather_class'] }}</span>
                            </span>
                            <span class="text-xs text-[#6E6E73] font-medium leading-tight">
                                {{ \App\Services\WeatherForecastService::MEANING_MAP[$item['weather_class']] ?? ($assessment?->recommended_action ?? 'Standard marine safety protocols in effect.') }}
                            </span>
                        </div>

                        <div>
                            <h2 class="text-xl sm:text-2xl font-black text-[#1D1D1F] tracking-tight">
                                {{ $batch->batch_number }}
                            </h2>
                            <div class="space-y-1 mt-1 text-xs text-[#6E6E73]">
                                <p>
                                    Dive Dates: <strong class="text-[#1D1D1F]">{{ $batch->start_date->format('M d') }} - {{ $batch->end_date->format('d, Y') }} ({{ $batch->start_date->format('D') }} - {{ $batch->end_date->format('D') }})</strong>
                                </p>
                                <p>
                                    Assigned Students: <strong class="text-[#1D1D1F]">{{ $item['students_count'] }} Student(s)</strong>
                                    @if(!empty($item['class_counts']))
                                        <span class="text-[#8E8E93] mx-1">·</span>
                                        <span class="text-[#1D1D1F] font-semibold">
                                            @php
                                                $classBreakdownStrs = [];
                                                foreach ($item['class_counts'] as $cType => $cnt) {
                                                    $classBreakdownStrs[] = "{$cnt} {$cType}";
                                                }
                                            @endphp
                                            {{ implode(', ', $classBreakdownStrs) }}
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Release Action & Expand Roster Button -->
                    <div class="flex items-center gap-2.5 shrink-0 self-start md:self-center">
                        <!-- Release Action -->
                        @if($releaseReq)
                            <span class="px-3.5 py-2 rounded-xl bg-amber-50 text-amber-800 border border-amber-300 text-xs font-bold shadow-2xs" @click.stop>
                                Release Requested
                            </span>
                        @elseif($item['can_request_release'])
                            <button type="button" 
                                    @click.stop="selectedBatch = {{ json_encode($item) }}; releaseModalOpen = true"
                                    class="px-3.5 py-2 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-bold transition-all">
                                Request Release
                            </button>
                        @else
                            <span class="px-3 py-2 rounded-xl text-xs font-bold bg-[#F2F2F7] text-[#8E8E93]" title="Release requests are locked within 48 hours of dive start" @click.stop>
                                Locked (&lt;48h)
                            </span>
                        @endif

                        <!-- Expand / Collapse Roster Toggle Button -->
                        <button type="button" 
                                class="px-4 py-2 rounded-xl bg-white hover:bg-[#F2F2F7] border border-[#E5E5EA] text-xs font-bold text-[#1D1D1F] flex items-center gap-2 transition-all cursor-pointer">
                            <span x-text="isBatchOpen({{ $batch->id }}) ? 'Hide Roster' : 'View Student Roster'"></span>
                            <svg class="w-4 h-4 text-[#6E6E73] transition-transform duration-200" 
                                 :class="isBatchOpen({{ $batch->id }}) ? 'rotate-180' : ''"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                    </div>

                </div>

                <!-- Expandable Assigned Student Roster Section (Dashboard Card Format) -->
                <div x-show="isBatchOpen({{ $batch->id }})" 
                     x-cloak 
                     class="border-t border-[#E5E5EA] bg-[#FAFAFC]/60 p-4 sm:p-6 space-y-4">
                    
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-1">
                        <div>
                            <h3 class="text-base font-black text-[#1D1D1F]">Assigned Student Roster</h3>
                            <p class="text-xs text-[#6E6E73]">Review student swimming abilities and health conditions prior to boat departure</p>
                        </div>
                        <span class="text-xs font-extrabold text-[#1D1D1F] bg-white border border-[#E5E5EA] px-3 py-1 rounded-xl self-start sm:self-auto shadow-2xs">
                            {{ $item['students_count'] }} Diver(s) in Group
                        </span>
                    </div>

                    <!-- 2-Column Student Cards Grid (Matching Dashboard Format) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                        @foreach($students as $student)
                            @php
                                $rawCondition = trim($student->health_condition ?? '');
                                $cleanHealth = strtolower(rtrim($rawCondition, '.'));
                                $isNoneOrGeneral = empty($cleanHealth) 
                                    || in_array($cleanHealth, ['none', 'none declared', 'no', 'n/a', 'na', 'nil', 'normal', 'fit for diving', 'fit for diving, no declared medical issues', 'cleared medical waiver', 'first time freediving'])
                                    || str_starts_with($cleanHealth, 'fit for diving')
                                    || str_starts_with($cleanHealth, 'none')
                                    || str_starts_with($cleanHealth, 'cleared medical waiver')
                                    || str_starts_with($cleanHealth, 'first time freediving')
                                    || str_starts_with($cleanHealth, 'certified aida')
                                    || str_starts_with($cleanHealth, 'working on frenzel');
                                $hasMedical = !$isNoneOrGeneral && !empty($rawCondition);

                                $swim = strtolower($student->swimmer_status ?? 'swimmer');
                                $classType = $student->booking?->formatted_class_type ?? 'Discovery';
                                $shortClassType = ucfirst($student->booking?->class_type ?? 'Discovery');
                            @endphp
                            
                            <!-- Individual Student Card -->
                            <div class="bg-white rounded-2xl border border-[#E5E5EA] p-4 sm:p-5 hover:border-[#D1D1D6] hover:shadow-sm transition-all space-y-4 flex flex-col justify-between">
                                
                                <!-- Card Header: Student Name & Compact Package Badge -->
                                <div class="flex items-start justify-between gap-2.5">
                                    <div class="min-w-0 flex-1">
                                        <span class="text-[10px] uppercase font-bold text-[#8E8E93] tracking-wider block">Student</span>
                                        <h4 class="font-black text-[#1D1D1F] text-base sm:text-lg tracking-tight mt-0.5 truncate">{{ $student->name }}</h4>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-black bg-[#F8EAEA] text-[#780000] shrink-0">
                                        {{ $shortClassType }}
                                    </span>
                                </div>

                                <!-- Information Rows (3 Semantic Rows) -->
                                <div class="space-y-3.5 text-xs">
                                    
                                    <!-- Row 1: Age & Package -->
                                    <div class="grid grid-cols-2 gap-3 sm:gap-4 items-start">
                                        <div>
                                            <span class="text-[10px] font-bold text-[#8E8E93] uppercase tracking-wider block mb-0.5">Age</span>
                                            <span class="font-extrabold text-sm sm:text-base text-[#1D1D1F] block">{{ $student->age }} yrs</span>
                                        </div>
                                        <div>
                                            <span class="text-[10px] font-bold text-[#8E8E93] uppercase tracking-wider block mb-0.5">Package</span>
                                            <span class="font-extrabold text-xs sm:text-sm text-[#1D1D1F] block leading-snug">{{ $classType }}</span>
                                        </div>
                                    </div>

                                    <!-- Row 2: Swimming Ability & Health/Medical -->
                                    <div class="grid grid-cols-2 gap-3 sm:gap-4 items-start">
                                        <div>
                                            <span class="text-[10px] font-bold text-[#8E8E93] uppercase tracking-wider block mb-1">Swimming Ability</span>
                                            @if($swim === 'confident' || $swim === 'confident_swimmer')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg font-black text-xs bg-emerald-100 text-emerald-900 shadow-2xs">
                                                    <span class="w-2 h-2 rounded-full bg-emerald-600 shrink-0"></span>
                                                    <span>Confident Swimmer</span>
                                                </span>
                                            @elseif($swim === 'non_swimmer')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg font-black text-xs bg-rose-100 text-rose-900 shadow-2xs">
                                                    <span class="w-2 h-2 rounded-full bg-rose-600 shrink-0"></span>
                                                    <span>Non-Swimmer</span>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg font-black text-xs bg-blue-100 text-blue-900 shadow-2xs">
                                                    <span class="w-2 h-2 rounded-full bg-blue-600 shrink-0"></span>
                                                    <span>Swimmer</span>
                                                </span>
                                            @endif
                                        </div>

                                        <div>
                                            <span class="text-[10px] font-bold text-[#8E8E93] uppercase tracking-wider block mb-1">Health / Medical</span>
                                            @if($hasMedical)
                                                <span class="font-bold text-xs text-amber-900 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-100 shadow-2xs">
                                                    <span>⚠️</span>
                                                    <span class="truncate">{{ $student->health_condition }}</span>
                                                </span>
                                            @else
                                                <span class="text-xs sm:text-sm text-[#8E8E93] font-bold block pt-1">None declared</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Row 3: Lead Booker & Contact -->
                                    <div class="grid grid-cols-2 gap-3 sm:gap-4 items-start">
                                        <div>
                                            <span class="text-[10px] font-bold text-[#8E8E93] uppercase tracking-wider block mb-0.5">Lead Booker</span>
                                            <span class="font-extrabold text-xs sm:text-sm text-[#1D1D1F] truncate block">{{ $student->booking?->contact_name ?? $student->name }}</span>
                                        </div>
                                        <div>
                                            <span class="text-[10px] font-bold text-[#8E8E93] uppercase tracking-wider block mb-0.5">Contact</span>
                                            <a href="tel:{{ $student->booking?->contact_phone ?: '09185559876' }}" class="font-extrabold text-xs sm:text-sm text-[#780000] hover:underline block truncate">
                                                {{ $student->booking?->contact_phone ?: ($student->booking?->contact_email ?: '0918 555 9876') }}
                                            </a>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        @empty
            <div class="bg-white rounded-2xl p-12 border border-[#E5E5EA] text-center space-y-3">
                <h3 class="text-base font-bold text-[#1D1D1F]">No Upcoming Confirmed Assignments</h3>
                <p class="text-xs text-[#6E6E73] max-w-md mx-auto leading-relaxed">
                    You currently have no students assigned for upcoming dates. Make sure your availability calendar is marked free or browse the Open Slot Requests board.
                </p>
                <div class="pt-2">
                    <a href="{{ route('coach.availability.index') }}" class="px-4 py-2 rounded-xl bg-[#00C3D0] text-white text-xs font-bold hover:bg-[#00AAB6] transition-colors inline-flex items-center gap-2">
                        <span>Open Availability Calendar</span>
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- 2. PAST DIVE HISTORY (Admin/Owner Filter Pattern & Expandable Batches) -->
    <div x-show="activeTab === 'history'" class="space-y-6">
        
        <!-- Filter Bar Toolbar (Identical to Admin/Owner Filter Patterns) -->
        <div class="bg-white rounded-2xl p-3 sm:p-4 border border-[#E5E5EA]">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                
                <!-- Quick Class Package Tabs -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 lg:pb-0 scrollbar-none">
                    <a href="{{ request()->fullUrlWithQuery(['class_type' => '', 'tab' => 'history']) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 {{ !request('class_type') ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                        All Classes
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['class_type' => 'discovery', 'tab' => 'history']) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 {{ request('class_type') === 'discovery' ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                        Discovery
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['class_type' => 'fundive', 'tab' => 'history']) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 {{ request('class_type') === 'fundive' ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                        Fundive
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['class_type' => 'refinement', 'tab' => 'history']) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 {{ request('class_type') === 'refinement' ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                        Refinement
                    </a>
                </div>

                <!-- Search & Advanced Filter Controls Dropdown -->
                <div class="flex items-center gap-2 self-end lg:self-auto shrink-0 relative">
                    <button type="button" 
                            @click="openFilters = !openFilters" 
                            class="px-3.5 py-1.5 rounded-xl border border-[#E5E5EA] bg-white hover:bg-[#F2F2F7] text-[#1D1D1F] text-xs font-semibold flex items-center gap-1.5 cursor-pointer transition-all">
                        <svg class="w-3.5 h-3.5 text-[#6E6E73] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                        </svg>
                        <span>Filter</span>
                        @if(request()->filled('date_from') || request()->filled('date_to') || request()->filled('class_type'))
                            <span class="w-2 h-2 rounded-full bg-[#780000] shrink-0"></span>
                        @endif
                    </button>

                    <!-- Filter Dropdown Menu (Admin Format) -->
                    <div x-show="openFilters" 
                         @click.outside="openFilters = false" 
                         x-cloak 
                         class="absolute right-0 top-full mt-2 w-[calc(100vw-2rem)] sm:w-80 max-w-sm bg-white rounded-2xl border border-[#E5E5EA] shadow-xl p-4 z-50 space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-[#E5E5EA]">
                            <h4 class="font-black text-xs text-[#1D1D1F]">Filter Past History</h4>
                            <a href="{{ route('coach.schedule.index', ['tab' => 'history']) }}" class="text-[11px] text-[#780000] hover:underline font-bold">Reset</a>
                        </div>

                        <form method="GET" action="{{ route('coach.schedule.index') }}" class="space-y-3 text-xs">
                            <input type="hidden" name="tab" value="history">

                            <div>
                                <label class="block font-bold text-[#6E6E73] mb-1">From Date</label>
                                <input type="date" 
                                       name="date_from" 
                                       value="{{ request('date_from') }}" 
                                       class="w-full px-2.5 py-1.5 rounded-lg border border-[#D1D1D6] text-xs focus:border-[#780000] focus:ring-[#780000]">
                            </div>

                            <div>
                                <label class="block font-bold text-[#6E6E73] mb-1">To Date</label>
                                <input type="date" 
                                       name="date_to" 
                                       value="{{ request('date_to') }}" 
                                       class="w-full px-2.5 py-1.5 rounded-lg border border-[#D1D1D6] text-xs focus:border-[#780000] focus:ring-[#780000]">
                            </div>

                            <div>
                                <label class="block font-bold text-[#6E6E73] mb-1">Class Type</label>
                                <select name="class_type" class="w-full px-2.5 py-1.5 rounded-lg border border-[#D1D1D6] text-xs focus:border-[#780000] focus:ring-[#780000]">
                                    <option value="">All Class Types</option>
                                    <option value="discovery" {{ request('class_type') === 'discovery' ? 'selected' : '' }}>Discovery</option>
                                    <option value="fundive" {{ request('class_type') === 'fundive' ? 'selected' : '' }}>Fundive</option>
                                    <option value="refinement" {{ request('class_type') === 'refinement' ? 'selected' : '' }}>Refinement</option>
                                </select>
                            </div>

                            <div class="pt-2 border-t border-[#E5E5EA]">
                                <button type="submit" class="w-full py-2 rounded-xl bg-[#780000] hover:bg-[#5E0000] text-white text-xs font-bold transition-all">
                                    Apply Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>

        <!-- History Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="p-4 sm:p-5 rounded-2xl bg-white border border-[#E5E5EA] flex items-center justify-between">
                <div>
                    <div class="text-[11px] text-[#8E8E93] font-bold uppercase tracking-wider">Completed Sessions</div>
                    <div class="text-2xl sm:text-3xl font-black text-[#1D1D1F] mt-1">{{ $totalCompletedBatchesCount }} Batches</div>
                </div>
            </div>
            <div class="p-4 sm:p-5 rounded-2xl bg-white border border-[#E5E5EA] flex items-center justify-between">
                <div>
                    <div class="text-[11px] text-[#8E8E93] font-bold uppercase tracking-wider">Total Students Coached</div>
                    <div class="text-2xl sm:text-3xl font-black text-[#1D1D1F] mt-1">{{ $totalPastStudentsCount }} Students</div>
                </div>
            </div>
        </div>

        <!-- Past Sessions List (Expandable Batch Cards) -->
        @forelse($historyBatches as $item)
            @php
                $batch = $item['batch'];
                $students = $item['students'];
            @endphp
            <div class="bg-white rounded-2xl border border-[#E5E5EA] hover:border-[#D1D1D6] transition-all overflow-hidden">
                
                <!-- Clickable Past Batch Header -->
                <div class="p-4 sm:p-5 cursor-pointer select-none flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white hover:bg-[#FAFAFC] transition-colors"
                     @click="toggleBatch('hist_{{ $batch->id }}')">
                    <div class="min-w-0 flex-1">
                        <h3 class="text-base font-black text-[#1D1D1F]">{{ $batch->batch_number }}</h3>
                        <div class="space-y-0.5 mt-1 text-xs text-[#6E6E73]">
                            <p>
                                Dive Dates: <strong class="text-[#1D1D1F]">{{ $batch->start_date->format('M d') }} - {{ $batch->end_date->format('d, Y') }} ({{ $batch->start_date->format('D') }} - {{ $batch->end_date->format('D') }})</strong>
                            </p>
                            <p>
                                Students Coached: <strong class="text-[#1D1D1F]">{{ $item['students_count'] }} Student(s)</strong>
                                @if(!empty($item['class_counts']))
                                    <span class="text-[#8E8E93] mx-1">·</span>
                                    <span class="text-[#1D1D1F] font-semibold">
                                        @php
                                            $histBreakdownStrs = [];
                                            foreach ($item['class_counts'] as $cType => $cnt) {
                                                $histBreakdownStrs[] = "{$cnt} {$cType}";
                                            }
                                        @endphp
                                        {{ implode(', ', $histBreakdownStrs) }}
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <button type="button" 
                            class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-[#F2F2F7] border border-[#E5E5EA] text-xs font-bold text-[#1D1D1F] flex items-center gap-1.5 transition-all cursor-pointer self-start sm:self-auto">
                        <span x-text="isBatchOpen('hist_{{ $batch->id }}') ? 'Hide Past Roster' : 'View Past Roster'"></span>
                        <svg class="w-3.5 h-3.5 text-[#6E6E73] transition-transform duration-200" 
                             :class="isBatchOpen('hist_{{ $batch->id }}') ? 'rotate-180' : ''"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                </div>

                <!-- Expandable Past Student Roster -->
                <div x-show="isBatchOpen('hist_{{ $batch->id }}')" 
                     x-cloak 
                     class="border-t border-[#E5E5EA] p-4 sm:p-5 bg-[#FAFAFC]/60 space-y-3">
                    <div class="overflow-x-auto border border-[#E5E5EA] rounded-xl bg-white">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-[#FAFAFC] border-b border-[#E5E5EA] text-[#8E8E93] font-bold uppercase text-[11px]">
                                <tr>
                                    <th class="px-4 py-3">Student Name</th>
                                    <th class="px-4 py-3">Age</th>
                                    <th class="px-4 py-3">Class Type</th>
                                    <th class="px-4 py-3">Swimming Ability</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E5E5EA] bg-white text-[#1D1D1F]">
                                @foreach($students as $s)
                                    @php $swimStatus = strtolower($s->swimmer_status ?? 'swimmer'); @endphp
                                    <tr class="hover:bg-[#FAFAFC] transition-colors">
                                        <td class="px-4 py-3 font-bold text-[#1D1D1F]">{{ $s->name }}</td>
                                        <td class="px-4 py-3 text-[#6E6E73] font-semibold">{{ $s->age }} yrs</td>
                                        <td class="px-4 py-3 text-[#780000] font-bold">{{ $s->booking?->formatted_class_type ?? 'Discovery' }}</td>
                                        <td class="px-4 py-3">
                                            @if($swimStatus === 'confident' || $swimStatus === 'confident_swimmer')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-black bg-emerald-100 text-emerald-900 shadow-2xs">
                                                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                                    <span>Confident Swimmer</span>
                                                </span>
                                            @elseif($swimStatus === 'non_swimmer')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-black bg-rose-100 text-rose-900 shadow-2xs">
                                                    <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                                                    <span>Non-Swimmer</span>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-black bg-blue-100 text-blue-900 shadow-2xs">
                                                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                                    <span>Swimmer</span>
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl p-10 border border-[#E5E5EA] text-center text-[#8E8E93] text-xs">
                No past completed dive history found for the selected criteria.
            </div>
        @endforelse

    </div>

    <!-- Emergency Release Modal -->
    <div x-show="releaseModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-[#E5E5EA] space-y-6 relative" @click.away="releaseModalOpen = false">
            
            <div class="flex items-start justify-between border-b border-[#E5E5EA] pb-4">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-rose-600">Emergency Staffing Request</div>
                    <h3 class="text-lg font-black text-[#1D1D1F] mt-0.5">Request Assignment Release</h3>
                </div>
                <button type="button" @click="releaseModalOpen = false" class="text-gray-400 hover:text-gray-600 text-lg font-bold">✕</button>
            </div>

            <template x-if="selectedBatch">
                <form action="{{ route('coach.availability.release') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="batch_id" :value="selectedBatch.batch?.id">
                    <input type="hidden" name="dive_date" :value="selectedBatch.dive_date">

                    <div class="p-3.5 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] text-xs space-y-1">
                        <div class="font-bold text-[#1D1D1F]" x-text="selectedBatch.batch?.name || selectedBatch.batch?.batch_number"></div>
                        <div class="text-[#6E6E73]">Students Assigned: <strong x-text="selectedBatch.students_count"></strong></div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-[#1D1D1F]">
                            Reason for Emergency Release <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="reason" 
                                  rows="4" 
                                  required 
                                  placeholder="Please explain the emergency or unavoidable circumstance..."
                                  class="w-full text-xs rounded-xl border-[#E5E5EA] focus:border-[#780000] focus:ring-[#780000] p-3"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="releaseModalOpen = false" class="px-4 py-2.5 rounded-xl border border-[#E5E5EA] text-xs font-bold text-[#6E6E73] hover:bg-[#F2F2F7]">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 text-white text-xs font-bold hover:bg-rose-700">
                            Submit Release Request
                        </button>
                    </div>
                </form>
            </template>

        </div>
    </div>

</div>
@endsection
