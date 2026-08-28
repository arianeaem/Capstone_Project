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
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.coaches.index') }}" class="text-xs text-[#6E6E73] hover:text-[#1D1D1F]">
                    ← Back to Coach Roster
                </a>
                <span class="text-[#D1D1D6]">/</span>
                <span class="font-bold text-[#780000]">{{ $coach->name }}</span>
            </div>
            <h1 class="text-2xl font-extrabold text-[#1D1D1F] mt-1">{{ $coach->name }}</h1>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            <a href="{{ route('admin.coaches.matching') }}" class="btn-primary px-4 py-2 text-xs sm:text-sm font-bold shadow-sm">
                + Assign Students
            </a>
            <a href="{{ route('admin.users.edit', $coach) }}" class="btn-secondary px-3.5 py-2 text-xs sm:text-sm font-semibold flex items-center gap-1.5">
                <svg class="w-4 h-4 text-[#6E6E73]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                <span>Edit Account in Settings</span>
            </a>
        </div>
    </div>

    <!-- Coach Header Overview Card -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-xl bg-[#780000] text-white flex items-center justify-center font-extrabold text-2xl shadow-sm shrink-0">
                {{ substr($coach->name, 0, 1) }}
            </div>
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h2 class="text-xl font-bold text-[#1D1D1F]">{{ $coach->name }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $coach->status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                        {{ ucfirst($coach->status) }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#F8EAEA] text-[#780000] border border-[#F1D5D5]">
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

        <div class="p-4 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] text-right space-y-1 shrink-0">
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
            
            <!-- Assigned Students Table -->
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                    <div>
                        <h3 class="text-base font-bold text-[#1D1D1F]">Assigned Students & Participants</h3>
                        <p class="text-xs text-[#6E6E73] mt-0.5">Students currently placed under Coach {{ $coach->name }}'s guidance.</p>
                    </div>

                    <span class="text-xs font-bold text-[#780000]">
                        {{ $activeAssignments->count() }} Student(s) Total
                    </span>
                </div>

                <div class="divide-y divide-[#E5E5EA]">
                    @forelse($activeAssignments as $assignment)
                    @php $p = $assignment->participant; @endphp
                    <div class="py-4 space-y-2 text-xs sm:text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <strong class="font-bold text-[#1D1D1F] text-sm">{{ $p->name }}</strong>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-[#EFF6FF] text-[#1E40AF] border border-[#BFDBFE]">
                                        {{ ucfirst($p->booking->class_type ?? 'Discovery') }}
                                    </span>
                                    @if($assignment->is_ratio_override)
                                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-[#FEF2F2] text-[#991B1B] border border-[#FECACA]">
                                            Ratio Override
                                        </span>
                                    @endif
                                </div>
                                <div class="text-xs text-[#6E6E73] mt-1 space-y-0.5">
                                    <div>Booking: <a href="{{ route('admin.bookings.show', $p->booking) }}" class="font-mono font-bold text-[#780000] hover:underline">{{ $p->booking->booking_number }}</a></div>
                                    <div>Batch: <strong>{{ $assignment->batch?->batch_code ?? 'Ad-hoc' }}</strong></div>
                                    <div>Dive Date: <strong>{{ $assignment->dive_date->format('M d, Y (l)') }}</strong></div>
                                </div>
                            </div>

                            <button type="button" 
                                    @click="selectedParticipantId = {{ $p->id }}; selectedParticipantName = '{{ addslashes($p->name) }}'; openReassignModal = true"
                                    class="btn-secondary px-3 py-1.5 text-xs font-semibold text-[#FF3B3C] hover:bg-[#FEF2F2] shrink-0">
                                Reassign Away
                            </button>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-[#6E6E73] py-6 text-center">
                        No students currently assigned to this coach. Use the <a href="{{ route('admin.coaches.matching') }}" class="text-[#780000] font-bold underline">Matching Queue</a> to assign students.
                    </p>
                    @endforelse
                </div>
            </div>

            <!-- Past Completed History -->
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 shadow-sm space-y-4">
                <h3 class="text-base font-bold text-[#1D1D1F] border-b border-[#E5E5EA] pb-3">Completed Dive History</h3>

                <div class="divide-y divide-[#E5E5EA]">
                    @forelse($pastAssignments as $past)
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div>
                            <strong class="text-[#1D1D1F]">{{ $past->participant->name }}</strong>
                            <span class="text-[#6E6E73] ml-2">({{ ucfirst($past->participant->booking->class_type ?? 'Class') }})</span>
                            <div class="text-xs text-[#8E8E93] mt-0.5 space-y-0.5">
                                <div>Batch: {{ $past->batch?->batch_code }}</div>
                                <div>{{ $past->dive_date->format('M d, Y') }}</div>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-[#F2F2F7] text-[#6E6E73]">
                            Completed
                        </span>
                    </div>
                    @empty
                    <p class="text-xs text-[#6E6E73] py-2 text-center">No past dive records logged yet.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- RIGHT 1 COLUMN: AVAILABILITY CALENDAR -->
        <div class="space-y-6">
            
            <!-- Availability Calendar Card -->
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 shadow-sm space-y-4">
                <div class="border-b border-[#E5E5EA] pb-3">
                    <h3 class="text-base font-bold text-[#1D1D1F]">Availability Calendar</h3>
                    <p class="text-xs text-[#6E6E73] mt-0.5">Dates marked by the coach in the Coach Portal.</p>
                </div>

                <div class="space-y-2">
                    @forelse($coach->coachAvailabilities as $avail)
                    <div class="p-3 rounded-xl border flex items-center justify-between text-xs {{ $avail->status_badge['class'] }}">
                        <div>
                            <strong class="block">{{ $avail->date->format('F d, Y (l)') }}</strong>
                            @if($avail->notes)
                                <span class="text-xs opacity-80 block mt-0.5">{{ $avail->notes }}</span>
                            @endif
                        </div>

                        <span class="px-2 py-0.5 rounded-full text-xs font-extrabold border bg-white shadow-xs">
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
                    <select name="new_coach_id" required class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                        <option value="">-- Choose Active Coach --</option>
                        @foreach($otherCoaches as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-1">Reassignment Reason <span class="text-[#780000]">*</span></label>
                    <textarea name="reason" required rows="3" placeholder="e.g. Original coach reported sick / Schedule balance adjustment" class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#E5E5EA]">
                    <button type="button" @click="openReassignModal = false" class="btn-secondary px-3.5 py-1.5 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary px-5 py-1.5 text-xs font-bold shadow-sm">
                        Confirm Reassignment
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
