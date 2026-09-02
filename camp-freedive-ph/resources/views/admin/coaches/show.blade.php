@extends('layouts.admin')

@section('title', $coach->name . ' - Coach Detail | Camp FreedivePH')

@section('content')
<div class="space-y-6 text-sm" x-data="{ 
    openReassignModal: false, 
    selectedParticipantId: null, 
    selectedParticipantName: '' 
}">
    
    <!-- Top Breadcrumb & Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-4">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <a href="{{ route('admin.coaches.index') }}" class="text-xs font-semibold text-[#6E6E73] hover:text-[#780000] transition-colors flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                    <span>Coach Roster</span>
                </a>
                <span class="text-[#D1D1D6]">/</span>
                <span class="font-bold text-[#780000] text-xs">{{ $coach->name }}</span>
            </div>
            <h1 class="text-2xl font-extrabold text-[#1D1D1F] tracking-tight">{{ $coach->name }}</h1>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.coaches.matching') }}" class="btn-primary px-4 py-2 text-xs sm:text-sm font-bold shadow-2xs">
                + Assign Students
            </a>
            <a href="{{ route('admin.users.edit', $coach) }}" class="btn-secondary px-3.5 py-2 text-xs sm:text-sm font-semibold flex items-center gap-1.5">
                <svg class="w-4 h-4 text-[#6E6E73]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                <span>Edit Account in Settings</span>
            </a>
        </div>
    </div>

    <!-- Coach Header Overview Card -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] p-5 sm:p-6 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-[#F8EAEA] text-[#780000] border-2 border-[#780000] flex items-center justify-center font-black text-xl shadow-2xs shrink-0">
                {{ substr($coach->name, 0, 1) }}
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-lg font-bold text-[#1D1D1F]">{{ $coach->name }}</h2>
                    <span class="px-2 py-0.5 rounded-md text-xs font-bold border {{ $coach->status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                        {{ ucfirst($coach->status) }}
                    </span>
                    <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-[#F8EAEA] text-[#780000] border border-[#F1D5D5]">
                        Freediving Coach
                    </span>
                </div>
                <div class="text-xs text-[#6E6E73] mt-1 space-y-0.5">
                    <div>{{ $coach->email }}</div>
                    <div>{{ $coach->phone ?: 'No phone recorded' }}</div>
                    <div>Joined {{ $coach->created_at->format('M Y') }}</div>
                </div>
            </div>
        </div>

        <div class="p-3.5 rounded-lg bg-[#FAFAFC] border border-[#E5E5EA] text-right space-y-0.5 shrink-0">
            <span class="text-xs uppercase font-bold text-[#6E6E73] tracking-wider block">Assigned Workload</span>
            <div class="text-xl font-extrabold text-[#1D1D1F]">
                {{ $activeAssignments->count() }} Active Student(s)
            </div>
            <span class="text-xs text-[#6E6E73] block">
                Across {{ $upcomingAssignments->pluck('dive_date')->unique()->count() }} Upcoming Dive Date(s)
            </span>
        </div>
    </div>

    <!-- 2 COLUMN LAYOUT -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- LEFT 2 COLUMNS: ASSIGNED STUDENTS & SCHEDULE -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Assigned Students Card Grid -->
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-5 sm:p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                    <div>
                        <h3 class="text-base font-bold text-[#1D1D1F]">Assigned Students & Participants</h3>
                        <p class="text-xs text-[#6E6E73] mt-0.5">Students currently placed under Coach {{ $coach->name }}'s guidance.</p>
                    </div>

                    <span class="text-xs font-bold px-2.5 py-1 rounded-md bg-[#F8EAEA] text-[#780000] border border-[#F1D5D5]">
                        {{ $activeAssignments->count() }} Student(s) Total
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($activeAssignments as $assignment)
                    @php 
                        $p = $assignment->participant; 
                        $classType = $p->booking?->formatted_class_type ?? ucfirst($p->booking?->class_type ?? 'Discovery');
                    @endphp
                    <div class="p-4 rounded-lg border border-[#E5E5EA] bg-[#FAFAFC] hover:border-[#780000] transition-all flex flex-col justify-between space-y-3 shadow-2xs">
                        <div class="space-y-2">
                            <!-- Student Header & Course Badge -->
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h4 class="font-bold text-sm text-[#1D1D1F]">{{ $p->name }}</h4>
                                    <span class="text-xs text-[#6E6E73]">Age {{ $p->age }} • {{ ucfirst($p->swimmer_status ?? 'Swimmer') }}</span>
                                </div>
                                <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-[#F8EAEA] text-[#780000] border border-[#F1D5D5] shrink-0">
                                    {{ $p->booking?->class_type === 'discovery' ? 'Discovery' : ($p->booking?->class_type === 'fundive' ? 'Fundive' : 'Refinement') }}
                                </span>
                            </div>

                            @if($assignment->is_ratio_override)
                                <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    ⚠️ Ratio Override Active
                                </div>
                            @endif

                            @if($p->health_condition)
                                <div class="p-2 rounded-md bg-amber-50 border border-amber-200 text-xs text-amber-900 leading-snug">
                                    <strong>Medical:</strong> {{ $p->health_condition }}
                                </div>
                            @endif

                            <!-- Assignment Details -->
                            <div class="pt-1 text-xs text-[#6E6E73] space-y-1">
                                <div class="flex items-center justify-between">
                                    <span>Booking Ref:</span>
                                    <a href="{{ route('admin.bookings.show', $p->booking) }}" class="font-mono font-bold text-[#780000] hover:underline">
                                        {{ $p->booking->booking_number }}
                                    </a>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>Batch:</span>
                                    <strong class="text-[#1D1D1F]">{{ $assignment->batch?->batch_code ?? 'Ad-hoc' }}</strong>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>Dive Date:</span>
                                    <strong class="text-[#1D1D1F]">{{ $assignment->dive_date->format('M d, Y (D)') }}</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Action Footer -->
                        <div class="pt-2 border-t border-[#E5E5EA] flex items-center justify-end">
                            <button type="button" 
                                    @click="selectedParticipantId = {{ $p->id }}; selectedParticipantName = '{{ addslashes($p->name) }}'; openReassignModal = true"
                                    class="btn-secondary px-2.5 py-1 text-xs font-semibold">
                                Reassign Away
                            </button>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-full py-8 text-center bg-[#FAFAFC] rounded-xl border border-dashed border-[#D1D1D6]">
                        <p class="text-xs text-[#6E6E73]">
                            No students currently assigned to this coach. Use the <a href="{{ route('admin.coaches.matching') }}" class="text-[#780000] font-bold underline">Matching Queue</a> to assign students.
                        </p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Past Completed History -->
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-5 sm:p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                    <h3 class="text-base font-bold text-[#1D1D1F]">Completed Dive History</h3>
                    <span class="text-xs text-[#6E6E73]">{{ $pastAssignments->count() }} past assignment(s)</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @forelse($pastAssignments as $past)
                    <div class="p-3 rounded-lg border border-[#E5E5EA] bg-[#FAFAFC] flex items-center justify-between text-xs">
                        <div class="space-y-0.5">
                            <strong class="text-[#1D1D1F] block text-xs">{{ $past->participant->name }}</strong>
                            <div class="text-xs text-[#6E6E73]">
                                <span>Batch {{ $past->batch?->batch_code }}</span>
                                <span class="mx-1">•</span>
                                <span>{{ $past->dive_date->format('M d, Y') }}</span>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Completed
                        </span>
                    </div>
                    @empty
                    <p class="col-span-full text-xs text-[#6E6E73] py-4 text-center">No past dive records logged yet.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- RIGHT 1 COLUMN: AVAILABILITY CALENDAR -->
        <div class="space-y-6">
            
            <!-- Availability Calendar Card -->
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-5 sm:p-6 shadow-2xs space-y-4">
                <div class="border-b border-[#E5E5EA] pb-3">
                    <h3 class="text-base font-bold text-[#1D1D1F]">Availability Calendar</h3>
                    <p class="text-xs text-[#6E6E73] mt-0.5">Dates marked by the coach in the Coach Portal.</p>
                </div>

                <div class="space-y-2">
                    @forelse($coach->coachAvailabilities as $avail)
                    <div class="p-3 rounded-lg border flex items-center justify-between text-xs {{ $avail->status_badge['class'] }}">
                        <div>
                            <strong class="block">{{ $avail->date->format('F d, Y (l)') }}</strong>
                            @if($avail->notes)
                                <span class="text-xs opacity-80 block mt-0.5">{{ $avail->notes }}</span>
                            @endif
                        </div>

                        <span class="px-2 py-0.5 rounded-md text-xs font-bold border bg-white shadow-2xs">
                            {{ $avail->status_badge['label'] }}
                        </span>
                    </div>
                    @empty
                    <p class="text-xs text-[#6E6E73] py-4 text-center">
                        No availability calendar entries submitted yet by this coach.
                    </p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- MODAL: REASSIGN STUDENT -->
    <!-- ========================================================================= -->
    <div x-show="openReassignModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-[#E5E5EA]" @click.outside="openReassignModal = false">
            <h3 class="text-lg font-bold text-[#1D1D1F]">Reassign Student Away</h3>
            <p class="text-xs text-[#6E6E73]">
                Reassign <strong class="text-[#1D1D1F]" x-text="selectedParticipantName"></strong> away from Coach {{ $coach->name }} to another coach.
            </p>

            <form action="{{ route('admin.coaches.reassign_student', $coach) }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <input type="hidden" name="participant_id" :value="selectedParticipantId">

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-1">Select New Coach <span class="text-[#780000]">*</span></label>
                    <select name="new_coach_id" required class="w-full px-3 py-2 rounded-lg border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                        <option value="">-- Choose Active Coach --</option>
                        @foreach($otherCoaches as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-1">Reassignment Reason <span class="text-[#780000]">*</span></label>
                    <textarea name="reason" required rows="3" placeholder="e.g. Original coach reported sick / Schedule balance adjustment" class="w-full px-3 py-2 rounded-lg border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#E5E5EA]">
                    <button type="button" @click="openReassignModal = false" class="btn-secondary px-3.5 py-1.5 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary px-4 py-1.5 text-xs font-bold shadow-2xs">
                        Confirm Reassignment
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
