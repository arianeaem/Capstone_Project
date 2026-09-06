@extends('layouts.admin')

@section('title', 'My Assigned Schedule | Coach Portal')

@section('content')
<div class="space-y-6" x-data="{ activeTab: '{{ $activeTab }}', releaseModalOpen: false, selectedBatch: null }">
    
    <!-- Top Header & Tabs -->
    <div class="bg-white rounded-2xl p-6 border border-[#E5E5EA] shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-[#1D1D1F] mt-1">My Assigned Schedule & Student Rosters</h1>
            <p class="text-xs text-[#6E6E73]">
                Review your upcoming dive assignments, student health conditions, live weather safety ratings, and historical dive records.
            </p>
        </div>

        <!-- Tab Controls -->
        <div class="flex items-center bg-[#F2F2F7] rounded-xl border border-[#E5E5EA] p-1">
            <button type="button" 
                    @click="activeTab = 'upcoming'"
                    :class="activeTab === 'upcoming' ? 'bg-white text-[#1D1D1F] shadow-xs font-bold' : 'text-[#6E6E73] font-semibold hover:text-[#1D1D1F]'"
                    class="px-4 py-2 rounded-lg text-xs transition-all flex items-center gap-2">
                <svg class="w-4 h-4 text-current" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <span>Upcoming Dives ({{ count($upcomingBatches) }})</span>
            </button>
            <button type="button" 
                    @click="activeTab = 'history'"
                    :class="activeTab === 'history' ? 'bg-white text-[#1D1D1F] shadow-xs font-bold' : 'text-[#6E6E73] font-semibold hover:text-[#1D1D1F]'"
                    class="px-4 py-2 rounded-lg text-xs transition-all flex items-center gap-2">
                <svg class="w-4 h-4 text-current" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                <span>Past History ({{ $totalCompletedBatchesCount }})</span>
            </button>
        </div>
    </div>

    <!-- Upcoming Confirmed Assignments -->
    <div x-show="activeTab === 'upcoming'" class="space-y-6">
        @forelse($upcomingBatches as $item)
            @php
                $batch = $item['batch'];
                $students = $item['students'];
                $weatherBadge = $item['weather_badge'];
                $assessment = $item['assessment'];
                $releaseReq = $item['release_request'];
            @endphp

            <div class="bg-white rounded-2xl border border-[#E5E5EA] shadow-xs overflow-hidden">
                
                <!-- Batch Information -->
                <div class="p-6 border-b border-[#E5E5EA] bg-[#FAFAFC] flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <h3 class="text-xl font-black text-[#1D1D1F]">{{ $batch->batch_number }}</h3>
                        <p class="text-xs text-[#6E6E73]">
                            Dates: <strong>{{ $batch->start_date->format('M d, Y') }} — {{ $batch->end_date->format('M d, Y') }}</strong> • Mabini Coastline Base
                        </p>
                    </div>

                    <!-- Batch Status and Actions -->
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Live Weather Safety Badge -->
                        <div class="px-3.5 py-2 rounded-xl border text-xs font-bold flex items-center gap-2 {{ $weatherBadge['class'] }}">
                            <img src="{{ asset('icons/icons8-warning-shield-32.png') }}" class="w-4 h-4 shrink-0" alt="Safety">
                            <span>Weather: {{ $item['weather_class'] }}</span>
                        </div>

                        <!-- Emergency Release Action -->
                        @if($releaseReq)
                            <span class="px-3 py-2 rounded-xl bg-amber-50 text-amber-800 border border-amber-300 text-xs font-bold">
                                Release Requested (Pending Admin)
                            </span>
                        @elseif($item['can_request_release'])
                            <button type="button" 
                                    @click="selectedBatch = {{ json_encode($item) }}; releaseModalOpen = true"
                                    class="px-3.5 py-2 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 text-xs font-bold transition-all">
                                Request Release
                            </button>
                        @else
                            <span class="px-3 py-2 rounded-xl bg-gray-100 text-gray-500 text-xs font-semibold" title="Release locked within 48h of dive">
                                🔒 Locked ({{ $item['hours_until_dive'] }}h to dive)
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Weather and Safety Conditions -->
                @if($assessment)
                <div class="px-6 py-3 bg-[#F2F2F7]/50 border-b border-[#E5E5EA] flex flex-wrap items-center justify-between gap-3 text-xs text-[#6E6E73]">
                    <div class="flex items-center gap-4">
                        <span><strong>Marine Recommendation:</strong> {{ $assessment->recommended_action }}</span>
                    </div>
                    @if($assessment->worst_hour)
                    <div>
                        <span>Worst Window / Hour: <strong>{{ $assessment->worst_hour->format('g:i A') }}</strong></span>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Assigned Student Roster -->
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-[#8E8E93]">
                            Assigned Student Roster ({{ $item['students_count'] }} Students Matched)
                        </h4>
                        <div class="flex items-center gap-2">
                            @foreach($item['class_counts'] as $cType => $cnt)
                                <span class="px-2 py-0.5 rounded-md bg-[#F2F2F7] text-[#1D1D1F] text-xs font-semibold">
                                    {{ $cnt }} {{ $cType }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div class="overflow-x-auto border border-[#E5E5EA] rounded-xl">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-[#FAFAFC] border-b border-[#E5E5EA] text-[#8E8E93] font-bold uppercase tracking-wider text-[11px]">
                                <tr>
                                    <th class="px-4 py-3">Student Name</th>
                                    <th class="px-4 py-3">Age</th>
                                    <th class="px-4 py-3">Swimmer Status</th>
                                    <th class="px-4 py-3">Class Type</th>
                                    <th class="px-4 py-3">Health & Medical Notes</th>
                                    <th class="px-4 py-3">Emergency Contact</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E5E5EA] bg-white text-[#1D1D1F]">
                                @foreach($students as $s)
                                    @php
                                        $hasMedical = $s->health_condition && !in_array(strtolower(trim($s->health_condition)), ['none', 'none declared', 'no', 'n/a']);
                                    @endphp
                                    <tr class="hover:bg-[#FAFAFC] transition-colors">
                                        <td class="px-4 py-3.5 font-bold">{{ $s->name }}</td>
                                        <td class="px-4 py-3.5 text-[#6E6E73]">{{ $s->age }} yrs</td>
                                        <td class="px-4 py-3.5">
                                            <span class="px-2 py-0.5 rounded font-semibold {{ strtolower($s->swimmer_status) === 'non_swimmer' ? 'bg-rose-50 text-rose-700' : 'bg-gray-100 text-gray-700' }}">
                                                {{ ucfirst(str_replace('_', ' ', $s->swimmer_status ?: 'Swimmer')) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3.5 font-semibold text-[#780000]">
                                            {{ $s->booking?->formatted_class_type ?? 'Discovery' }}
                                        </td>
                                        <td class="px-4 py-3.5">
                                            @if($hasMedical)
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-900 border border-amber-200 font-bold">
                                                    <span>⚠️</span>
                                                    <span>{{ $s->health_condition }}</span>
                                                </span>
                                            @else
                                                <span class="text-[#AEAEB2]">None declared</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 text-[#6E6E73]">
                                            {{ $s->emergency_contact_name ?: ($s->booking?->contact_name ?? 'N/A') }}
                                            @if($s->emergency_contact_phone || $s->booking?->contact_phone)
                                                ({{ $s->emergency_contact_phone ?: $s->booking?->contact_phone }})
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
            <div class="bg-white rounded-2xl p-12 border border-[#E5E5EA] text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-[#F2F2F7] text-[#8E8E93] flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                </div>
                <h3 class="text-base font-bold text-[#1D1D1F]">No Upcoming Confirmed Assignments</h3>
                <p class="text-xs text-[#6E6E73] max-w-md mx-auto">
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

    <!-- Past Dive History -->
    <div x-show="activeTab === 'history'" class="space-y-6">
        
        <!-- History Filters -->
        <form method="GET" action="{{ route('coach.schedule.index') }}" class="bg-white rounded-2xl p-4 border border-[#E5E5EA] shadow-xs flex flex-wrap items-center justify-between gap-4">
            <input type="hidden" name="tab" value="history">

            <div class="flex flex-wrap items-center gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-[#8E8E93] uppercase tracking-wider mb-1">From Date</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="text-xs rounded-xl border-[#E5E5EA] p-2 focus:ring-[#780000] focus:border-[#780000]">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-[#8E8E93] uppercase tracking-wider mb-1">To Date</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="text-xs rounded-xl border-[#E5E5EA] p-2 focus:ring-[#780000] focus:border-[#780000]">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-[#8E8E93] uppercase tracking-wider mb-1">Class Type</label>
                    <select name="class_type" class="text-xs rounded-xl border-[#E5E5EA] p-2 focus:ring-[#780000] focus:border-[#780000]">
                        <option value="">All Class Types</option>
                        <option value="discovery" {{ request('class_type') === 'discovery' ? 'selected' : '' }}>Discovery</option>
                        <option value="open_water" {{ request('class_type') === 'open_water' ? 'selected' : '' }}>Open Water</option>
                        <option value="refinement" {{ request('class_type') === 'refinement' ? 'selected' : '' }}>Refinement</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#1D1D1F] text-white text-xs font-bold hover:bg-[#3A3A3C] transition-colors">
                    Apply Filter
                </button>
                <a href="{{ route('coach.schedule.index', ['tab' => 'history']) }}" class="px-3 py-2 rounded-xl bg-[#F2F2F7] text-[#6E6E73] text-xs font-semibold hover:bg-[#E5E5EA] transition-colors">
                    Reset
                </a>
            </div>
        </form>

        <!-- History Statistics -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="p-4 rounded-xl bg-white border border-[#E5E5EA] shadow-xs flex items-center justify-between">
                <div>
                    <div class="text-xs text-[#8E8E93] font-bold uppercase tracking-wider">Completed Sessions</div>
                    <div class="text-2xl font-black text-[#1D1D1F] mt-1">{{ $totalCompletedBatchesCount }} Batches</div>
                </div>
                <div class="p-3 rounded-xl bg-purple-50 text-purple-700 font-bold">
                    🏊‍♂️
                </div>
            </div>
            <div class="p-4 rounded-xl bg-white border border-[#E5E5EA] shadow-xs flex items-center justify-between">
                <div>
                    <div class="text-xs text-[#8E8E93] font-bold uppercase tracking-wider">Total Students Coached</div>
                    <div class="text-2xl font-black text-[#1D1D1F] mt-1">{{ $totalPastStudentsCount }} Certified/Coached</div>
                </div>
                <div class="p-3 rounded-xl bg-emerald-50 text-emerald-700 font-bold">
                    🎓
                </div>
            </div>
        </div>

        <!-- Past Sessions List -->
        @forelse($historyBatches as $item)
            @php
                $batch = $item['batch'];
                $students = $item['students'];
            @endphp
            <div class="bg-white rounded-2xl border border-[#E5E5EA] shadow-xs overflow-hidden opacity-95">
                <div class="p-5 border-b border-[#E5E5EA] bg-[#FAFAFC] flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-extrabold text-[#1D1D1F]">{{ $batch->batch_number }}</h3>
                        <p class="text-xs text-[#6E6E73] mt-0.5">
                            Concluded: {{ $batch->start_date->format('M d, Y') }} — {{ $batch->end_date->format('M d, Y') }}
                        </p>
                    </div>

                    <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold">
                        ✓ {{ $item['students_count'] }} Student(s) Completed
                    </span>
                </div>

                <div class="p-5">
                    <div class="overflow-x-auto border border-[#E5E5EA] rounded-xl">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-[#FAFAFC] border-b border-[#E5E5EA] text-[#8E8E93] font-bold uppercase text-[11px]">
                                <tr>
                                    <th class="px-4 py-2.5">Student Name</th>
                                    <th class="px-4 py-2.5">Age</th>
                                    <th class="px-4 py-2.5">Class Type</th>
                                    <th class="px-4 py-2.5">Swimmer Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E5E5EA] bg-white">
                                @foreach($students as $s)
                                    <tr>
                                        <td class="px-4 py-2.5 font-bold text-[#1D1D1F]">{{ $s->name }}</td>
                                        <td class="px-4 py-2.5 text-[#6E6E73]">{{ $s->age }} yrs</td>
                                        <td class="px-4 py-2.5 text-[#780000] font-semibold">{{ $s->booking?->formatted_class_type ?? 'Discovery' }}</td>
                                        <td class="px-4 py-2.5 text-[#6E6E73]">{{ ucfirst(str_replace('_', ' ', $s->swimmer_status ?: 'Swimmer')) }}</td>
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
                        <div class="font-bold text-[#1D1D1F]" x-text="selectedBatch.batch?.name"></div>
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
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 text-white text-xs font-bold hover:bg-rose-700 shadow-sm">
                            Submit Release Request
                        </button>
                    </div>
                </form>
            </template>

        </div>
    </div>

</div>
@endsection
