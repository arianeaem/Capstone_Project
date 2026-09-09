@extends('layouts.admin')

@section('title', 'Coach Dashboard | Camp FreedivePH')

@section('content')
<div class="space-y-6" x-data="{ openReleaseModal: false, openApplyModal: false, selectedOpeningId: null, selectedOpeningDate: '' }">
    
    <!-- Top Command Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 py-1">
        <div class="space-y-1">
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-[#1D1D1F]">
                Welcome back, {{ $coach->name }}!
            </h1>
            <p class="text-xs sm:text-sm text-[#6E6E73] flex items-center gap-1.5">
                <span>It's {{ now('Asia/Manila')->format('l, F d, Y') }}</span>
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('coach.availability.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-white text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA] transition-all flex items-center gap-2">
                <span>Manage Availability</span>
            </a>
            <a href="{{ route('coach.requests.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-[#780000] hover:bg-[#5E0000] text-white transition-all flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span>Open Camp Slots ({{ $activeOpeningsCount }})</span>
            </a>
        </div>
    </div>

    <!-- Coach Summary KPIs (Icons Removed, Clean Minimal Numbers) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        
        <!-- Assigned Dives -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-[#E5E5EA]">
            <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Assigned Dives</span>
            <div class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] mt-1">{{ $upcomingConfirmedDivesCount }}</div>
        </div>

        <!-- Available Dates Offered -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-[#E5E5EA]">
            <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Available Dates</span>
            <div class="text-2xl sm:text-3xl font-extrabold text-emerald-700 mt-1">{{ $availableDaysCount }}</div>
        </div>

        <!-- Open Camp Slots -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-[#E5E5EA]">
            <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Open Camp Slots</span>
            <div class="text-2xl sm:text-3xl font-extrabold text-amber-700 mt-1">{{ $activeOpeningsCount }}</div>
        </div>

        <!-- Total Divers Mentored -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-[#E5E5EA]">
            <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Students Coached</span>
            <div class="text-2xl sm:text-3xl font-extrabold text-[#780000] mt-1">{{ $totalStudentsMentored }}</div>
        </div>

    </div>

    <!-- PRIMARY OPERATIONAL SECTION: Left Column (Batch + Open Slots) & Right Column (Student Roster) -->
    @if($nextSessionData)
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- LEFT COLUMN (col-span-4): Upcoming Dive Batch Card + Open Camp Volunteer Slots Below It -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- Upcoming Dive Batch Card -->
                <div class="bg-white rounded-2xl border border-[#E5E5EA] p-5 sm:p-6 flex flex-col justify-between space-y-5">
                    <div class="space-y-3.5">
                        <!-- Weather Safety Badge & Description -->
                        <div class="flex items-center gap-2.5 flex-wrap">
                            @php
                                $wClass = strtolower(trim($nextSessionData['weather_class'] ?? 'Safe'));
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
                            <span class="px-2.5 py-1 rounded-lg text-xs font-black {{ $wBg }} inline-flex items-center gap-1.5 shadow-2xs shrink-0">
                                <span class="w-2 h-2 rounded-full {{ $wDot }} animate-pulse"></span>
                                <span>{{ $nextSessionData['weather_class'] }}</span>
                            </span>
                            <span class="text-xs text-[#6E6E73] font-medium leading-tight">
                                {{ \App\Services\WeatherForecastService::MEANING_MAP[$nextSessionData['weather_class']] ?? 'Standard marine safety protocols in effect.' }}
                            </span>
                        </div>

                        <!-- Batch Title -->
                        <div>
                            <span class="text-[11px] uppercase font-bold text-[#8E8E93] tracking-wider block">Upcoming Dive Batch</span>
                            <h2 class="text-2xl font-black text-[#1D1D1F] tracking-tight mt-0.5">
                                {{ $nextSessionData['batch']->batch_number }}
                            </h2>
                        </div>

                        <!-- Dive Dates & Students Line -->
                        <div class="space-y-1 text-xs text-[#6E6E73] pt-1">
                            <p>
                                Dive Dates: <strong class="text-[#1D1D1F]">{{ $nextSessionData['batch']->start_date->format('M d') }} - {{ $nextSessionData['batch']->end_date->format('d, Y') }} ({{ $nextSessionData['batch']->start_date->format('D') }} - {{ $nextSessionData['batch']->end_date->format('D') }})</strong>
                            </p>
                            <p>
                                Assigned Students: <strong class="text-[#1D1D1F]">{{ $nextSessionData['students_count'] }} Student(s)</strong>
                                @if(!empty($nextSessionData['class_breakdown']))
                                    <span class="text-[#8E8E93] mx-1">·</span>
                                    <span class="text-[#1D1D1F] font-semibold">
                                        @php
                                            $dashBreakdownStrs = [];
                                            foreach ($nextSessionData['class_breakdown'] as $cType => $cnt) {
                                                $dashBreakdownStrs[] = "{$cnt} {$cType}";
                                            }
                                        @endphp
                                        {{ implode(', ', $dashBreakdownStrs) }}
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Action Area: Primary & Secondary (No Separator Line) -->
                    <div class="pt-3 flex flex-col sm:flex-row lg:flex-col xl:flex-row items-stretch gap-2.5">
                        <!-- Primary: Request Release -->
                        @if($nextSessionData['can_request_release'])
                            <button type="button" 
                                    @click="openReleaseModal = true"
                                    class="flex-1 px-4 py-2.5 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-bold transition-all flex items-center justify-center gap-1.5">
                                <span>Request Release</span>
                            </button>
                        @else
                            <span class="flex-1 px-3.5 py-2.5 rounded-xl text-xs font-bold bg-[#F2F2F7] text-[#8E8E93] text-center" title="Release requests are locked within 48 hours of dive start">
                                Locked (&lt;48h to dive)
                            </span>
                        @endif

                        <!-- Secondary: View Full Schedule -->
                        <a href="{{ route('coach.schedule.index') }}" class="flex-1 px-4 py-2.5 rounded-xl bg-white hover:bg-[#F2F2F7] text-[#1D1D1F] border border-[#E5E5EA] text-xs font-bold transition-all inline-flex items-center justify-center gap-1.5">
                            <span>Full Schedule</span>
                        </a>
                    </div>
                </div>

                <!-- OPEN CAMP VOLUNTEER SLOTS (Placed below the batch card on the left side) -->
                <div class="bg-white rounded-2xl border border-[#E5E5EA] p-5 sm:p-6 flex flex-col justify-between space-y-4">
                    <div class="space-y-4">
                        <!-- Header with Subtitle and View Board Button (No separator line) -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-1">
                            <div>
                                <h3 class="text-base font-black text-[#1D1D1F]">Open Camp Volunteer Slots</h3>
                                <p class="text-xs text-[#6E6E73]">Batches seeking extra coach support</p>
                            </div>
                            <a href="{{ route('coach.requests.index') }}" class="px-3 py-1.5 rounded-xl bg-white hover:bg-[#F2F2F7] text-[#1D1D1F] border border-[#E5E5EA] text-xs font-bold transition-all shadow-2xs inline-flex items-center gap-1.5 self-start sm:self-auto">
                                <span>Board · {{ $activeOpeningsCount }}</span>
                                <svg class="w-3.5 h-3.5 text-[#6E6E73]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg>
                            </a>
                        </div>

                        <!-- Clean Horizontal Openings Cards -->
                        <div class="space-y-3">
                            @forelse($openCoachOpenings as $opening)
                                @php
                                    $hasApplied = $opening->requests->isNotEmpty();
                                    $b = $opening->batch;
                                    $datesDisplay = ($b && $b->start_date && $b->end_date && $b->start_date->ne($b->end_date))
                                        ? $b->start_date->format('M d') . ' - ' . $b->end_date->format('d, Y') . ' (' . $b->start_date->format('D') . ' - ' . $b->end_date->format('D') . ')'
                                        : $opening->dive_date->format('M d, Y · l');
                                @endphp
                                <div class="p-4 rounded-2xl bg-[#FAFAFC] flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-[#D1D1D6] transition-colors">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-black text-sm text-[#1D1D1F]">{{ $opening->batch?->batch_number ?? 'Batch' }}</span>
                                            <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-black bg-amber-100 text-amber-900">
                                                NEEDS SUPPORT
                                            </span>
                                        </div>
                                        <p class="text-xs text-[#1D1D1F] font-semibold">
                                            Needs {{ $opening->needed_students_count }} student slots
                                        </p>
                                        <p class="text-xs text-[#6E6E73]">
                                            Dive dates: <strong class="text-[#1D1D1F]">{{ $datesDisplay }}</strong>
                                        </p>
                                    </div>

                                    <div class="shrink-0 pt-2 sm:pt-0">
                                        @if($hasApplied)
                                            <span class="px-3 py-1.5 rounded-xl text-xs font-black bg-blue-100 text-blue-900 inline-block">
                                                Applied (Pending)
                                            </span>
                                        @else
                                            <form action="{{ route('coach.requests.store', $opening) }}" method="POST" onsubmit="return confirm('Volunteer for this coaching slot for {{ $datesDisplay }}?');">
                                                @csrf
                                                <button type="submit" class="btn-primary px-4 py-2 text-xs font-bold">
                                                    Volunteer
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="py-6 text-center text-xs text-[#6E6E73] bg-[#FAFAFC] rounded-2xl border border-dashed border-[#E5E5EA]">
                                    No open broadcast volunteer slots right now.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT: Assigned Student Roster in Card Format (col-span-8, Unboxed Container) -->
            <div class="lg:col-span-8 bg-transparent border-0 p-0 shadow-none space-y-3.5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-1">
                    <div>
                        <h3 class="text-base sm:text-lg font-black text-[#1D1D1F]">Assigned Student Roster</h3>
                        <p class="text-xs text-[#6E6E73]">Review student swimming abilities prior to boat departure</p>
                    </div>
                    <span class="text-xs font-extrabold text-[#1D1D1F] bg-[#F2F2F7] px-3 py-1 rounded-xl self-start sm:self-auto shadow-2xs">
                        {{ $nextSessionData['students_count'] }} Student(s) Assigned
                    </span>
                </div>

                <!-- Student Cards Grid (Brand-Aligned Card Format) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                    @foreach($nextSessionData['students'] as $student)
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
                        
                        <!-- Individual Student Card (Mobile-Optimized Padding & Clean Spacing) -->
                        <div class="bg-white rounded-2xl border border-[#E5E5EA] p-4 sm:p-5 hover:border-[#D1D1D6] hover:shadow-sm transition-all space-y-4 flex flex-col justify-between">
                            
                            <!-- Card Header: Student Name & Compact Package Badge (No border) -->
                            <div class="flex items-start justify-between gap-2.5">
                                <div class="min-w-0 flex-1">
                                    <span class="text-[10px] uppercase font-bold text-[#8E8E93] tracking-wider block">Student</span>
                                    <h4 class="font-black text-[#1D1D1F] text-base sm:text-lg tracking-tight mt-0.5 truncate">{{ $student->name }}</h4>
                                </div>
                                <span class="px-2.5 py-1 rounded-lg text-xs font-black bg-[#F8EAEA] text-[#780000] shrink-0">
                                    {{ $shortClassType }}
                                </span>
                            </div>

                            <!-- Information Rows (Semantic Row Grouping prevents vertical height collisions) -->
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
    @else
        <!-- Empty State: No Immediate Dives -->
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-[#E5E5EA] p-8 text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-[#F2F2F7] text-[#8E8E93] flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-[#1D1D1F]">No Immediate Dive Assignments</h3>
                    <p class="text-xs text-[#6E6E73] mt-1 max-w-md mx-auto">
                        You currently have no scheduled batches for this weekend. Keep your availability calendar updated to get matched by camp coordinators.
                    </p>
                </div>
                <div class="pt-2">
                    <a href="{{ route('coach.availability.index') }}" class="px-4 py-2 rounded-xl bg-[#780000] hover:bg-[#5E0000] text-white text-xs font-bold transition-all inline-flex items-center gap-2">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        <span>Update Availability Calendar</span>
                    </a>
                </div>
            </div>

            <!-- Open Camp Volunteer Slots below empty state -->
            @if($openCoachOpenings->isNotEmpty())
                <div class="bg-white rounded-2xl border border-[#E5E5EA] p-5 sm:p-6 space-y-4">
                    <div class="flex items-center justify-between pb-1">
                        <div>
                            <h3 class="text-base font-black text-[#1D1D1F]">Open Camp Volunteer Slots</h3>
                            <p class="text-xs text-[#6E6E73]">Batches seeking extra coach support</p>
                        </div>
                        <a href="{{ route('coach.requests.index') }}" class="px-3 py-1.5 rounded-xl bg-white hover:bg-[#F2F2F7] text-[#1D1D1F] border border-[#E5E5EA] text-xs font-bold transition-all shadow-2xs inline-flex items-center gap-1.5">
                            <span>Board · {{ $activeOpeningsCount }}</span>
                            <svg class="w-3.5 h-3.5 text-[#6E6E73]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg>
                        </a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($openCoachOpenings as $opening)
                            @php 
                                $hasApplied = $opening->requests->isNotEmpty(); 
                                $b = $opening->batch;
                                $datesDisplay = ($b && $b->start_date && $b->end_date && $b->start_date->ne($b->end_date))
                                    ? $b->start_date->format('M d') . ' - ' . $b->end_date->format('d, Y') . ' (' . $b->start_date->format('D') . ' - ' . $b->end_date->format('D') . ')'
                                    : $opening->dive_date->format('M d, Y · l');
                            @endphp
                            <div class="p-4 rounded-2xl bg-[#FAFAFC] flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-[#D1D1D6] transition-colors">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-black text-sm text-[#1D1D1F]">{{ $opening->batch?->batch_number ?? 'Batch' }}</span>
                                        <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-black bg-amber-100 text-amber-900">NEEDS SUPPORT</span>
                                    </div>
                                    <p class="text-xs text-[#1D1D1F] font-semibold">Needs {{ $opening->needed_students_count }} student slots</p>
                                    <p class="text-xs text-[#6E6E73]">Dive dates: <strong class="text-[#1D1D1F]">{{ $datesDisplay }}</strong></p>
                                </div>
                                <div class="shrink-0">
                                    @if($hasApplied)
                                        <span class="px-3 py-1.5 rounded-xl text-xs font-black bg-blue-100 text-blue-900">Applied (Pending)</span>
                                    @else
                                        <form action="{{ route('coach.requests.store', $opening) }}" method="POST" onsubmit="return confirm('Volunteer for this coaching slot for {{ $datesDisplay }}?');">
                                            @csrf
                                            <button type="submit" class="btn-primary px-4 py-2 text-xs font-bold">Volunteer</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- FULL WIDTH SECTION: Upcoming Availability Snapshot (One Horizontal Line · Whole Card Clickable) -->
    <div class="bg-white rounded-2xl border border-[#E5E5EA] p-5 sm:p-6 flex flex-col justify-between space-y-4">
        <div class="space-y-4">
            <!-- Header with Title, Subtitle, and Full Month View Button (No separator line) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-1">
                <div>
                    <h3 class="text-base font-black text-[#1D1D1F]">Upcoming Availability Snapshot</h3>
                    <p class="text-xs text-[#6E6E73]">Click any date card to toggle your availability</p>
                </div>
                <a href="{{ route('coach.availability.index') }}" class="px-3 py-1.5 rounded-xl bg-white hover:bg-[#F2F2F7] text-[#1D1D1F] border border-[#E5E5EA] text-xs font-bold transition-all shadow-2xs inline-flex items-center gap-1.5 self-start sm:self-auto">
                    <span>Full Month View</span>
                </a>
            </div>

            <!-- Single Horizontal Line of 7 Dates (Responsive Scrollable on Mobile, Grid on Tablet/Desktop) -->
            <div class="overflow-x-auto pb-2 scrollbar-none -mx-2 px-2 sm:mx-0 sm:px-0">
                <div class="grid grid-cols-7 gap-2 sm:gap-3 min-w-[580px] sm:min-w-0">
                    @foreach($quickDays as $day)
                        @if($day['status'] === 'assigned')
                            @php
                                $assignedDayNum = $day['assigned_day_number'] ?? 1;
                                $bgClass = ($assignedDayNum === 1) ? 'bg-[#780000]' : 'bg-[#00C3D0]';
                                $assignedLabel = ($assignedDayNum === 1) ? 'Day 1 Assigned' : 'Day 2 Assigned';
                            @endphp
                            <!-- Assigned: #780000 for Day 1 or #00C3D0 for Day 2 (White text, whole card colored) -->
                            <div class="w-full rounded-2xl p-3 sm:p-4 text-center space-y-1.5 {{ $bgClass }} text-white select-none flex flex-col justify-between" title="{{ $assignedLabel }} (Locked)">
                                <div>
                                    <span class="text-[11px] sm:text-xs font-black uppercase tracking-wider block text-white/80 whitespace-nowrap">
                                        {{ $day['date']->format('D') }}
                                    </span>
                                    <span class="text-sm sm:text-lg font-black block text-white mt-0.5 whitespace-nowrap">
                                        {{ $day['date']->format('M j') }}
                                    </span>
                                </div>
                                <span class="text-[9px] sm:text-[10px] font-extrabold uppercase tracking-wider block text-white/95 pt-0.5 whitespace-nowrap">
                                    {{ $assignedLabel }}
                                </span>
                            </div>
                        @elseif($day['status'] === 'available')
                            <!-- Available: Green Background with White Text (Whole Card is Button) -->
                            <form action="{{ route('coach.availability.toggle') }}" method="POST" class="w-full">
                                @csrf
                                <input type="hidden" name="date" value="{{ $day['date_str'] }}">
                                <button type="submit" class="w-full rounded-2xl p-3 sm:p-4 text-center space-y-1.5 bg-emerald-600 hover:bg-emerald-700 text-white transition-all cursor-pointer flex flex-col justify-between group" title="Click to remove availability">
                                    <div>
                                        <span class="text-[11px] sm:text-xs font-black uppercase tracking-wider block text-emerald-100 group-hover:text-white whitespace-nowrap">
                                            {{ $day['date']->format('D') }}
                                        </span>
                                        <span class="text-sm sm:text-lg font-black block text-white mt-0.5 whitespace-nowrap">
                                            {{ $day['date']->format('M j') }}
                                        </span>
                                    </div>
                                    <span class="text-[9px] sm:text-[10px] font-extrabold uppercase tracking-wider block text-emerald-100 group-hover:text-white pt-0.5 whitespace-nowrap">
                                        Available
                                    </span>
                                </button>
                            </form>
                        @else
                            <!-- Not Set: No Background Color (Whole Card is Button) -->
                            <form action="{{ route('coach.availability.toggle') }}" method="POST" class="w-full">
                                @csrf
                                <input type="hidden" name="date" value="{{ $day['date_str'] }}">
                                <button type="submit" class="w-full rounded-2xl p-3 sm:p-4 text-center space-y-1.5 bg-white hover:bg-[#F2F2F7] text-[#1D1D1F] border border-[#E5E5EA] hover:border-[#780000] transition-all cursor-pointer flex flex-col justify-between group" title="Click to mark as available">
                                    <div>
                                        <span class="text-[11px] sm:text-xs font-black uppercase tracking-wider block text-[#8E8E93] group-hover:text-[#780000] whitespace-nowrap">
                                            {{ $day['date']->format('D') }}
                                        </span>
                                        <span class="text-sm sm:text-lg font-black block text-[#1D1D1F] mt-0.5 whitespace-nowrap">
                                            {{ $day['date']->format('M j') }}
                                        </span>
                                    </div>
                                    <span class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider block text-[#8E8E93] group-hover:text-[#780000] pt-0.5 whitespace-nowrap">
                                        Not Set
                                    </span>
                                </button>
                            </form>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Release Request Modal -->
    @if($nextSessionData && $nextSessionData['can_request_release'])
    <div x-show="openReleaseModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-5 sm:p-6 space-y-4 shadow-2xl border border-[#E5E5EA]" @click.outside="openReleaseModal = false">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-black text-[#1D1D1F]">Request Release from Assignment</h3>
                    <p class="text-xs text-[#6E6E73]">{{ $nextSessionData['batch']->batch_number }} • {{ $nextSessionData['dive_date']->format('M d, Y') }}</p>
                </div>
                <button type="button" @click="openReleaseModal = false" class="text-[#8E8E93] hover:text-[#1D1D1F] font-bold text-base">✕</button>
            </div>

            <form action="{{ route('coach.availability.release') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="batch_id" value="{{ $nextSessionData['batch']->id }}">
                <input type="hidden" name="dive_date" value="{{ $nextSessionData['dive_date']->format('Y-m-d') }}">

                <div class="p-3.5 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 leading-relaxed">
                    <strong>Notice:</strong> Your request will be submitted to the camp coordinator for approval. A replacement coach will be assigned from the availability pool.
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-1.5">Reason for Release <span class="text-[#780000]">*</span></label>
                    <textarea name="reason" rows="3" required placeholder="e.g. Medical emergency, urgent personal conflict" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white focus:border-[#780000] focus:ring-[#780000]"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" @click="openReleaseModal = false" class="btn-secondary px-4 py-2 text-xs">Cancel</button>
                    <button type="submit" class="btn-danger px-4 py-2 text-xs font-bold">Submit Release Request</button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection
