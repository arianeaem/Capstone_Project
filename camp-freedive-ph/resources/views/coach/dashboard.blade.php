@extends('layouts.admin')

@section('title', 'Coach Dashboard | Camp FreedivePH')

@section('content')
<div class="space-y-8">
    
    <!-- Top Greeting Banner -->
    <div class="bg-gradient-to-r from-[#008E98] to-[#004D54] rounded-2xl p-6 sm:p-8 text-white shadow-md flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden">
        <div class="space-y-1 relative z-10">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Welcome back, {{ $coach->name }}!</h1>
            <p class="text-sm text-[#E0F9FB]/90 max-w-xl">
                Manage your 2D1N availability calendar, review assigned student rosters, and volunteer for open camp slots.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3 relative z-10">
            <a href="{{ route('coach.availability.index') }}" class="px-4 py-2.5 rounded-xl bg-white text-[#004D54] font-bold text-xs shadow-sm hover:bg-[#E0F9FB] transition-all flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <span>Update Calendar</span>
            </a>
            <a href="{{ route('coach.requests.index') }}" class="px-4 py-2.5 rounded-xl bg-white/15 text-white font-bold text-xs border border-white/25 hover:bg-white/25 transition-all flex items-center gap-2">
                <img src="{{ asset('icons/icons8-coach-60.png') }}" class="w-4 h-4 invert brightness-200" alt="Slots">
                <span>Open Slots ({{ $activeOpeningsCount }})</span>
            </a>
        </div>
    </div>

    <!-- KPI Summary Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        
        <!-- Next Upcoming Assignment -->
        <div class="bg-white rounded-2xl p-5 border border-[#E5E5EA] shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-[#8E8E93]">Next Dive</span>
                <span class="p-2 rounded-xl bg-[#ECFDF5] text-[#065F46]">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </span>
            </div>
            <div class="mt-4">
                @if($nextSessionData)
                    <div class="text-lg font-extrabold text-[#1D1D1F]">{{ $nextSessionData['dive_date']->format('M d, Y') }}</div>
                    <div class="text-xs text-[#008E98] font-semibold mt-0.5">
                        {{ $nextSessionData['batch']->batch_number }} • {{ $nextSessionData['students_count'] }} Student(s)
                    </div>
                @else
                    <div class="text-base font-bold text-[#8E8E93]">No Upcoming Dives</div>
                    <div class="text-xs text-[#AEAEB2] mt-0.5">Mark calendar available to get matched</div>
                @endif
            </div>
        </div>

        <!-- Available Dates Offered -->
        <div class="bg-white rounded-2xl p-5 border border-[#E5E5EA] shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-[#8E8E93]">Offered Available</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="m9 12 2 2 4-4"></path></svg>
                </span>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-black text-[#1D1D1F]">{{ $availableDaysCount }} <span class="text-xs font-normal text-[#8E8E93]">Day(s)</span></div>
                <div class="text-xs text-[#6E6E73] mt-0.5">Marked Available (Unassigned)</div>
            </div>
        </div>

        <!-- Pending Slot Requests -->
        <div class="bg-white rounded-2xl p-5 border border-[#E5E5EA] shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-[#8E8E93]">Slot Requests</span>
                <span class="p-2 rounded-xl bg-amber-50 text-amber-600">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </span>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-black text-[#1D1D1F]">{{ $pendingRequestsCount }}</div>
                <div class="text-xs text-[#6E6E73] mt-0.5">Pending Admin Approval</div>
            </div>
        </div>

        <!-- Confirmed Upcoming Sessions -->
        <div class="bg-white rounded-2xl p-5 border border-[#E5E5EA] shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-[#8E8E93]">Confirmed Dives</span>
                <span class="p-2 rounded-xl bg-blue-50 text-blue-600">
                    <img src="{{ asset('icons/icons8-booking-60.png') }}" class="w-5 h-5 object-contain" alt="Bookings">
                </span>
            </div>
            <div class="mt-4">
                <div class="text-2xl font-black text-[#1D1D1F]">{{ $upcomingConfirmedDivesCount }} <span class="text-xs font-normal text-[#8E8E93]">Session(s)</span></div>
                <div class="text-xs text-[#6E6E73] mt-0.5">Assigned Future Batches</div>
            </div>
        </div>

    </div>

    <!-- Spotlight: Next Assigned Dive Session -->
    @if($nextSessionData)
    <div class="bg-white rounded-2xl border border-[#E5E5EA] shadow-sm overflow-hidden">
        <div class="p-6 border-b border-[#E5E5EA] bg-[#FAFAFC] flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-[#008E98]/10 text-[#008E98] border border-[#008E98]/20 font-mono">
                        {{ $nextSessionData['batch']->batch_number }}
                    </span>
                    <span class="text-xs font-semibold text-[#8E8E93]">• 2D1N Dive Session</span>
                </div>
                <h3 class="text-lg sm:text-xl font-black text-[#1D1D1F] mt-1">{{ $nextSessionData['batch']->name }}</h3>
                <p class="text-xs text-[#6E6E73]">
                    Dive Dates: <strong>{{ $nextSessionData['batch']->start_date->format('M d, Y') }} — {{ $nextSessionData['batch']->end_date->format('M d, Y') }}</strong> (Mabini Coastline Base)
                </p>
            </div>

            <!-- Weather Safety Badge -->
            <div class="flex items-center gap-3">
                <div class="px-3.5 py-2 rounded-xl border text-xs font-bold flex items-center gap-2 {{ $nextSessionData['weather_badge']['class'] }}">
                    <img src="{{ asset('icons/icons8-warning-shield-32.png') }}" class="w-4 h-4 shrink-0" alt="Safety">
                    <span>Weather: {{ $nextSessionData['weather_class'] }}</span>
                </div>
                <a href="{{ route('coach.schedule.index') }}" class="px-4 py-2 rounded-xl bg-[#1D1D1F] text-white text-xs font-bold hover:bg-[#3A3A3C] transition-all">
                    Full Roster
                </a>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <!-- Student Roster for this Session -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-[#8E8E93]">Your Assigned Students ({{ $nextSessionData['students_count'] }})</h4>
                    <div class="flex items-center gap-2 text-xs">
                        @foreach($nextSessionData['class_breakdown'] as $cType => $cnt)
                            <span class="px-2 py-0.5 rounded-md bg-[#F2F2F7] text-[#1D1D1F] font-semibold">{{ $cnt }} {{ $cType }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($nextSessionData['students'] as $s)
                    <div class="p-4 rounded-xl border border-[#E5E5EA] bg-[#FAFAFC] flex flex-col justify-between gap-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="font-bold text-sm text-[#1D1D1F]">{{ $s->name }}</div>
                                <div class="text-xs text-[#6E6E73]">{{ $s->age }} yrs old • {{ ucfirst(str_replace('_', ' ', $s->swimmer_status ?: 'Swimmer')) }}</div>
                            </div>
                            <span class="text-[11px] font-bold px-2 py-0.5 rounded bg-white text-[#780000] border border-[#E5E5EA]">
                                {{ $s->booking?->formatted_class_type ?? 'Discovery' }}
                            </span>
                        </div>

                        <!-- Medical / Health Alert -->
                        @if($s->health_condition && !in_array(strtolower(trim($s->health_condition)), ['none', 'none declared', 'no', 'n/a']))
                        <div class="p-2 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-900 flex items-center gap-2">
                            <span class="font-bold">⚠️ Health Note:</span>
                            <span>{{ $s->health_condition }}</span>
                        </div>
                        @else
                        <div class="text-[11px] text-[#AEAEB2]">No medical conditions declared.</div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Emergency Release Notice -->
            @if(!$nextSessionData['can_request_release'])
            <div class="p-3.5 rounded-xl bg-[#F2F2F7] border border-[#E5E5EA] text-xs text-[#6E6E73] flex items-center gap-3">
                <svg class="w-5 h-5 text-[#8E8E93] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span><strong>48-Hour Cutoff:</strong> Dive departs in {{ $nextSessionData['hours_until_dive'] }} hours. Direct self-service release is locked. Contact Camp Admin directly for urgent emergencies.</span>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Quick Navigation Hub -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Calendar Hub -->
        <a href="{{ route('coach.availability.index') }}" class="group bg-white rounded-2xl p-6 border border-[#E5E5EA] shadow-xs hover:border-[#008E98] hover:shadow-md transition-all flex flex-col justify-between">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-xl bg-[#E0F9FB] text-[#008E98] flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                </div>
                <h3 class="text-base font-bold text-[#1D1D1F] group-hover:text-[#008E98] transition-colors">Availability Calendar</h3>
                <p class="text-xs text-[#6E6E73] leading-relaxed">
                    Set and update your 2D1N weekend availability. Bulk-select multiple dates or toggle single pairs.
                </p>
            </div>
            <div class="mt-6 flex items-center text-xs font-bold text-[#008E98] gap-1 group-hover:translate-x-1 transition-transform">
                <span>Manage Availability</span>
                <span>→</span>
            </div>
        </a>

        <!-- Schedule & Students Hub -->
        <a href="{{ route('coach.schedule.index') }}" class="group bg-white rounded-2xl p-6 border border-[#E5E5EA] shadow-xs hover:border-[#780000] hover:shadow-md transition-all flex flex-col justify-between">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-xl bg-[#FEE2E2] text-[#780000] flex items-center justify-center group-hover:scale-110 transition-transform">
                    <img src="{{ asset('icons/icons8-booking-60.png') }}" class="w-6 h-6 object-contain" alt="Schedule">
                </div>
                <h3 class="text-base font-bold text-[#1D1D1F] group-hover:text-[#780000] transition-colors">My Schedule & History</h3>
                <p class="text-xs text-[#6E6E73] leading-relaxed">
                    View upcoming assigned student lists, health condition tags, weather context, and past coaching records.
                </p>
            </div>
            <div class="mt-6 flex items-center text-xs font-bold text-[#780000] gap-1 group-hover:translate-x-1 transition-transform">
                <span>View Full Schedule</span>
                <span>→</span>
            </div>
        </a>

        <!-- Open Requests Hub -->
        <a href="{{ route('coach.requests.index') }}" class="group bg-white rounded-2xl p-6 border border-[#E5E5EA] shadow-xs hover:border-[#008E98] hover:shadow-md transition-all flex flex-col justify-between">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-xl bg-[#ECFDF5] text-[#065F46] flex items-center justify-center group-hover:scale-110 transition-transform">
                    <img src="{{ asset('icons/icons8-coach-60.png') }}" class="w-6 h-6 object-contain" alt="Requests">
                </div>
                <h3 class="text-base font-bold text-[#1D1D1F] group-hover:text-[#065F46] transition-colors">Open Slot Requests</h3>
                <p class="text-xs text-[#6E6E73] leading-relaxed">
                    Browse dates where camp is short-staffed and volunteer to take open student groups with one click.
                </p>
            </div>
            <div class="mt-6 flex items-center text-xs font-bold text-[#065F46] gap-1 group-hover:translate-x-1 transition-transform">
                <span>Browse Open Slots ({{ $activeOpeningsCount }})</span>
                <span>→</span>
            </div>
        </a>

    </div>

</div>
@endsection
