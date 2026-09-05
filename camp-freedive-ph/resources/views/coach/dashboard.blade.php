@extends('layouts.admin')

@section('title', 'Coach Dashboard | Camp FreedivePH')

@section('content')
<div class="space-y-6" x-data="{ openReleaseModal: false, openApplyModal: false, selectedOpeningId: null, selectedOpeningDate: '' }">
    
    <!-- Top Command Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 py-1">
        <div class="space-y-1">
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-[#1D1D1F]">
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#63a5c4] via-[#164B60] to-[#2e80a3]">Welcome back, {{ $coach->name }}!</span>
            </h1>
            <p class="text-xs sm:text-sm text-[#6E6E73]">
                <span>It's {{ now('Asia/Manila')->format('l, F d, Y') }}</span>
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('coach.availability.index') }}" class="px-4 py-2 rounded-lg text-xs font-bold bg-white text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA] transition-colors flex items-center gap-1.5 shadow-2xs">
                <svg class="w-4 h-4 text-[#780000]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <span>Manage Availability</span>
            </a>
            <a href="{{ route('coach.requests.index') }}" class="px-4 py-2 rounded-lg text-xs font-bold bg-[#780000] hover:bg-[#5E0000] text-white transition-colors flex items-center gap-1.5 shadow-2xs">
                <span>Open Camp Slots ({{ $activeOpeningsCount }})</span>
            </a>
        </div>
    </div>

    <!-- Coach Performance KPIs -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 shadow-2xs">
        <div class="grid grid-cols-2 lg:grid-cols-4 items-center gap-y-3">
            
            <!-- Confirmed Upcoming Dives -->
            <div class="px-4 py-1">
                <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Assigned Dives</span>
                <div class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] mt-0.5">{{ $upcomingConfirmedDivesCount }}</div>
            </div>

            <!-- Available Dates Offered -->
            <div class="relative px-4 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Available Dates</span>
                <div class="text-2xl sm:text-3xl font-extrabold text-emerald-700 mt-0.5">{{ $availableDaysCount }}</div>
            </div>

            <!-- Open Camp Slots -->
            <div class="relative px-4 py-1">
                <div class="hidden lg:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Open Camp Slots</span>
                <div class="text-2xl sm:text-3xl font-extrabold text-amber-700 mt-0.5">{{ $activeOpeningsCount }}</div>
            </div>

            <!-- Total Divers Mentored -->
            <div class="relative px-4 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Students Coached</span>
                <div class="text-2xl sm:text-3xl font-extrabold text-[#780000] mt-0.5">{{ $totalStudentsMentored }}</div>
            </div>

        </div>
    </div>

    <!-- Next Dive Session -->
    @if($nextSessionData)
        <div class="bg-white rounded-xl border border-[#E5E5EA] overflow-hidden shadow-2xs space-y-0">
            
            <!-- Session Header -->
            <div class="p-5 sm:p-6 bg-[#FAFAFC] border-b border-[#E5E5EA] flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2.5 py-0.5 rounded-md text-xs font-bold border {{ $nextSessionData['weather_badge']['class'] }}">
                            Marine Safety: {{ $nextSessionData['weather_class'] }}
                        </span>
                        <span class="text-xs font-bold text-[#780000]">
                            Departure: {{ $nextSessionData['dive_date']->format('l, M d, Y') }} (09:30 AM Base Call)
                        </span>
                    </div>

                    <h2 class="text-lg sm:text-xl font-extrabold text-[#1D1D1F]">{{ $nextSessionData['batch']->batch_number }}</h2>
                    <p class="text-xs text-[#6E6E73]">
                        Location: <strong>Mabini Coastline Marine Sanctuary Base</strong> • 
                        Assigned Students: <strong>{{ $nextSessionData['students_count'] }} Student(s) (1:4 Safe Ratio Compliant)</strong>
                    </p>
                </div>

                <div class="flex items-center gap-2.5 flex-wrap shrink-0">
                    @if($nextSessionData['can_request_release'])
                        <button type="button" 
                                @click="openReleaseModal = true"
                                class="btn-secondary px-3.5 py-2 text-xs font-semibold text-[#780000] border-[#780000]/30 hover:bg-[#F8EAEA]">
                            Request Release
                        </button>
                    @else
                        <span class="px-3 py-1.5 rounded-lg text-xs font-bold bg-[#F2F2F7] text-[#8E8E93] border border-[#E5E5EA]" title="Release requests are locked within 48 hours of dive start">
                            🔒 Locked (&lt;48h to dive)
                        </span>
                    @endif

                    <a href="{{ route('coach.schedule.index') }}" class="text-xs font-bold text-[#780000] hover:underline inline-flex items-center gap-0.5">
                        <span>Full Schedule</span>
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg>
                    </a>
                </div>
            </div>

            <!-- Assigned Student Roster -->
            <div class="p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-[#1D1D1F]">Assigned Student Roster & Readiness Profiles</h3>
                    <span class="text-xs text-[#6E6E73]">Review student swimming abilities prior to boat departure</span>
                </div>

                <div class="overflow-x-auto border border-[#E5E5EA] rounded-xl">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-[#FAFAFC] text-[#6E6E73] font-bold uppercase tracking-wider border-b border-[#E5E5EA]">
                                <th class="py-3 px-4">Student Name</th>
                                <th class="py-3 px-4">Age</th>
                                <th class="py-3 px-4">Class Package</th>
                                <th class="py-3 px-4">Swimming Ability</th>
                                <th class="py-3 px-4">Lead Booker & Contact</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5E5EA]">
                            @foreach($nextSessionData['students'] as $student)
                                <tr class="hover:bg-[#FAFAFC] transition-colors">
                                    <td class="py-3 px-4 font-bold text-[#1D1D1F]">
                                        {{ $student->name }}
                                    </td>
                                    <td class="py-3 px-4 text-[#6E6E73]">
                                        {{ $student->age }} yrs
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded-md font-bold bg-[#FAFAFC] text-[#1D1D1F] border border-[#E5E5EA]">
                                            {{ $student->booking?->formatted_class_type ?? 'Freediving Class' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        @php
                                            $swim = strtolower($student->swimmer_status ?? 'basic');
                                        @endphp
                                        @if($swim === 'non_swimmer')
                                            <span class="px-2 py-0.5 rounded-md font-bold bg-amber-50 text-amber-800 border border-amber-300">
                                                Non-Swimmer (Requires Extra Float Line)
                                            </span>
                                        @elseif($swim === 'confident')
                                            <span class="px-2 py-0.5 rounded-md font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Confident Swimmer
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-md font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                Basic Swimmer
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-[#6E6E73]">
                                        <div><strong>{{ $student->booking?->contact_name ?? 'N/A' }}</strong></div>
                                        <div class="text-[11px]">{{ $student->booking?->contact_phone ?? $student->booking?->contact_email }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    @else
        <!-- Empty State: No Immediate Dives -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 text-center space-y-3 shadow-2xs">
            <div>
                <h3 class="text-base font-bold text-[#1D1D1F]">No Immediate Dive Assignments</h3>
                <p class="text-xs text-[#6E6E73] mt-1 max-w-md mx-auto">
                    You currently have no scheduled batches for this weekend. Keep your availability calendar updated to get matched by camp coordinators.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('coach.availability.index') }}" class="text-xs font-bold text-[#780000] hover:underline inline-flex items-center gap-0.5">
                    <span>Update Availability Calendar</span>
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg>
                </a>
            </div>
        </div>
    @endif

    <!-- Availability and Open Camp Slots -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Weekend Availability Snapshot -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-5 shadow-2xs space-y-4 flex flex-col justify-between">
            <div class="space-y-3">
                <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                    <div>
                        <h3 class="text-base font-bold text-[#1D1D1F]">Weekend Availability Snapshot</h3>
                        <p class="text-xs text-[#6E6E73]">Next 3 weekends status & 1-click availability toggling.</p>
                    </div>
                    <a href="{{ route('coach.availability.index') }}" class="text-xs font-bold text-[#780000] hover:underline inline-flex items-center gap-0.5">
                        <span>Calendar</span>
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg>
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                    @foreach($quickWeekendDays as $wDay)
                        <div class="p-3 rounded-xl border {{ $wDay['status'] === 'assigned' ? 'border-[#780000]/40 bg-[#F8EAEA]' : ($wDay['status'] === 'available' ? 'border-emerald-300 bg-emerald-50/50' : 'border-[#E5E5EA] bg-[#FAFAFC]') }} text-center space-y-1.5">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-[#6E6E73] block">
                                {{ $wDay['date']->format('D, M d') }}
                            </span>
                            
                            @if($wDay['status'] === 'assigned')
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-[#780000] text-white block">
                                    Assigned Dive
                                </span>
                            @elseif($wDay['status'] === 'available')
                                <form action="{{ route('coach.availability.toggle') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="date" value="{{ $wDay['date_str'] }}">
                                    <button type="submit" class="w-full px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition-colors">
                                        Available (Click to change)
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('coach.availability.toggle') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="date" value="{{ $wDay['date_str'] }}">
                                    <button type="submit" class="w-full px-2 py-0.5 rounded-md text-[10px] font-bold bg-white text-[#6E6E73] border border-[#D1D1D6] hover:border-[#780000] hover:text-[#780000] transition-colors">
                                        Mark Available
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-3 border-t border-[#E5E5EA] flex items-center justify-between text-xs text-[#6E6E73]">
                <span>Legend: <strong class="text-emerald-700">Green = Available</strong> • <strong class="text-[#780000]">Red = Assigned</strong></span>
                <a href="{{ route('coach.availability.index') }}" class="font-bold text-[#780000] hover:underline inline-flex items-center gap-0.5">
                    <span>Full Month View</span>
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg>
                </a>
            </div>
        </div>

        <!-- Open Camp Slots -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-5 shadow-2xs space-y-4 flex flex-col justify-between">
            <div class="space-y-3">
                <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                    <div>
                        <h3 class="text-base font-bold text-[#1D1D1F]">Open Camp Volunteer Slots</h3>
                        <p class="text-xs text-[#6E6E73]">Batches currently seeking extra coach support.</p>
                    </div>
                    <a href="{{ route('coach.requests.index') }}" class="text-xs font-bold text-[#780000] hover:underline inline-flex items-center gap-0.5">
                        <span>View Board ({{ $activeOpeningsCount }})</span>
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg>
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($openCoachOpenings as $opening)
                        @php
                            $hasApplied = $opening->requests->isNotEmpty();
                        @endphp
                        <div class="p-3.5 rounded-xl border border-[#E5E5EA] bg-[#FAFAFC] flex items-center justify-between gap-3">
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-xs text-[#1D1D1F]">{{ $opening->batch?->batch_number ?? 'Batch' }}</span>
                                    <span class="px-2 py-0.2 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-300">
                                        Needs {{ $opening->needed_students_count }} student slot(s)
                                    </span>
                                </div>
                                <p class="text-xs text-[#6E6E73]">
                                    Dive Date: <strong>{{ $opening->dive_date->format('M d, Y (l)') }}</strong>
                                </p>
                            </div>

                            @if($hasApplied)
                                <span class="px-3 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    Applied (Pending)
                                </span>
                            @else
                                <form action="{{ route('coach.requests.store', $opening) }}" method="POST" onsubmit="return confirm('Volunteer for this coaching slot on {{ $opening->dive_date->format('M d, Y') }}?');">
                                    @csrf
                                    <button type="submit" class="btn-primary px-3 py-1.5 text-xs font-bold shadow-2xs">
                                        Volunteer
                                    </button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <div class="py-6 text-center text-xs text-[#6E6E73] bg-[#FAFAFC] rounded-lg border border-dashed border-[#E5E5EA]">
                            No open broadcast slots right now. All batches are currently covered.
                        </div>
                    @endforelse
                </div>
            </div>

            <a href="{{ route('coach.requests.index') }}" class="text-xs font-bold text-[#780000] hover:underline inline-flex items-center justify-center gap-1 pt-2 w-full text-center">
                <span>Explore Open Requests Board</span>
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg>
            </a>
        </div>

    </div>

    <!-- Release Request Modal -->
    @if($nextSessionData && $nextSessionData['can_request_release'])
    <div x-show="openReleaseModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white rounded-xl max-w-lg w-full p-5 sm:p-6 space-y-4 shadow-2xl border border-[#E5E5EA]" @click.outside="openReleaseModal = false">
            <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                <div>
                    <h3 class="text-base font-bold text-[#1D1D1F]">Request Release from Assignment</h3>
                    <p class="text-xs text-[#6E6E73]">{{ $nextSessionData['batch']->batch_number }} • {{ $nextSessionData['dive_date']->format('M d, Y') }}</p>
                </div>
                <button type="button" @click="openReleaseModal = false" class="text-[#8E8E93] hover:text-[#1D1D1F] font-bold text-base">✕</button>
            </div>

            <form action="{{ route('coach.availability.release') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="batch_id" value="{{ $nextSessionData['batch']->id }}">
                <input type="hidden" name="dive_date" value="{{ $nextSessionData['dive_date']->format('Y-m-d') }}">

                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 leading-relaxed">
                    <strong>Notice:</strong> Your request will be submitted to the camp coordinator for approval. A replacement coach will be assigned from the availability pool.
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-1.5">Reason for Release <span class="text-[#780000]">*</span></label>
                    <textarea name="reason" rows="3" required placeholder="e.g. Medical emergency, urgent personal conflict" class="w-full px-3.5 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" @click="openReleaseModal = false" class="btn-secondary px-4 py-2 text-xs">Cancel</button>
                    <button type="submit" class="btn-danger px-4 py-2 text-xs font-bold shadow-2xs">Submit Release Request</button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection
