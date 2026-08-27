@extends('layouts.admin')

@section('title', 'Students Needing a Coach | Camp FreedivePH')

@section('content')
<div class="space-y-6 text-sm">
    
    <!-- Top Header & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight">Students Needing a Coach</h1>
            <p class="text-xs sm:text-sm text-[#6E6E73] mt-1">
                Assign and balance certified coaches to upcoming weekend batches based on the standard 1:4 instructor-to-student safety ratio.
            </p>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            <a href="{{ route('admin.coaches.requests') }}" class="btn-secondary px-4 py-2.5 text-xs sm:text-sm font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 text-[#FF3B3C]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                <span>Coach Requests</span>
                @if(isset($pendingRequestsCount) && $pendingRequestsCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-[#FF3B3C] text-white">
                        {{ $pendingRequestsCount }}
                    </span>
                @endif
            </a>

            <a href="{{ route('admin.coaches.index') }}" class="btn-secondary px-4 py-2.5 text-xs sm:text-sm font-semibold flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                <span>Coach Roster</span>
            </a>
        </div>
    </div>

    <!-- Batch Groups Queue -->
    @if(isset($batchGroups) && count($batchGroups) > 0)
        <div class="space-y-8">
            @foreach($batchGroups as $group)
            @php
                $batch = $group['batch'];
                $students = $group['unassigned_students'];
                $availableCoaches = $group['available_coaches'];
                $neededCoaches = max(1, (int) ceil($group['unassigned_count'] / 4));
            @endphp
            <div class="bg-white rounded-2xl border border-[#E5E5EA] p-6 space-y-6 shadow-2xs"
                 x-data="batchMatcher({
                     batchId: {{ $batch->id }},
                     unassignedCount: {{ $group['unassigned_count'] }},
                     students: {{ json_encode($group['students_data']) }},
                     availableCoaches: {{ json_encode($availableCoaches) }},
                     neededCoaches: {{ $neededCoaches }}
                 })"
                 x-init="initMatcher()">
                
                <!-- Group Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-4">
                    <div>
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <h2 class="font-extrabold text-lg text-[#1D1D1F]">
                                {{ $batch->batch_number }}
                            </h2>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                {{ $group['unassigned_count'] }} Student(s) Unassigned
                            </span>
                        </div>
                        <p class="text-xs text-[#6E6E73] mt-1">
                            {{ $batch->start_date->format('M d') }} – {{ $batch->end_date->format('M d, Y') }}
                        </p>
                        <p class="text-xs text-[#6E6E73] mt-1">
                            @foreach($group['class_counts'] as $class => $count)
                                <span class="font-semibold text-[#1D1D1F]">{{ ucfirst($class) }}: {{ $count }}pax</span>{{ !$loop->last ? ' | ' : '' }}
                            @endforeach
                        </p>
                    </div>

                    <div class="flex flex-col items-end gap-2">
                        <!-- Broadcast Slot Button -->
                        @if($group['open_broadcast'])
                            <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-green-50 text-green-800 border border-green-200 inline-flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-600 animate-pulse"></span>
                                Slot Shared to Portal
                            </span>
                        @else
                            <form action="{{ route('admin.coaches.matching.broadcast') }}" method="POST" class="inline-block">
                                @csrf
                                <input type="hidden" name="batch_id" value="{{ $batch->id }}">
                                <button type="submit" 
                                        onclick="return confirm('Share an open coaching slot for this batch to all coaches in the portal?')"
                                        class="btn-secondary px-3.5 py-1.5 text-xs font-semibold flex items-center gap-1.5">
                                    <span>Share to Coaches</span>
                                </button>
                            </form>
                        @endif
                        
                        <!-- Mode Selector Toggle -->
                        <div class="inline-flex rounded-xl bg-[#F2F2F7] p-1 border border-[#E5E5EA]">
                            <button type="button" 
                                    @click="mode = 'balanced'"
                                    :class="mode === 'balanced' ? 'bg-white text-[#780000] font-bold shadow-2xs' : 'text-[#6E6E73] hover:text-[#1D1D1F]'"
                                    class="px-3 py-1.5 rounded-lg text-xs transition-all">
                                Balanced Multi-Coach
                            </button>
                            <button type="button" 
                                    @click="mode = 'single'"
                                    :class="mode === 'single' ? 'bg-white text-[#780000] font-bold shadow-2xs' : 'text-[#6E6E73] hover:text-[#1D1D1F]'"
                                    class="px-3 py-1.5 rounded-lg text-xs transition-all">
                                Single Coach
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- MODE 1: BALANCED MULTI-COACH ASSIGNMENT STUDIO -->
                <!-- ========================================================================= -->
                <div x-show="mode === 'balanced'" class="space-y-6">
                    
                    <!-- Coach Count Options & Split Recommendation -->
                    <div class="p-4 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-[#1D1D1F] text-xs uppercase tracking-wider">Coach Staffing Target:</span>
                                <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-extrabold text-xs">
                                    Recommended: {{ $neededCoaches }} Coach{{ $neededCoaches > 1 ? 'es' : '' }}
                                </span>
                            </div>
                        </div>

                        <!-- Choose Number of Coaches to split into -->
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-semibold text-[#1D1D1F]">Coaches to Assign:</label>
                            <select x-model.number="coachCount" @change="onCoachCountChange()" 
                                    class="px-3 py-1.5 rounded-lg border border-[#D1D1D6] text-xs font-bold text-[#1D1D1F] bg-white">
                                <template x-for="num in maxViableCoaches" :key="num">
                                    <option :value="num" x-text="num + (num === neededCoaches ? ' Coaches' : ' Coaches')"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <!-- Balanced Coaches Buckets -->
                    <form action="{{ route('admin.coaches.matching.batch_assign') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="batch_id" value="{{ $batch->id }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                            <template x-for="(coachSlot, index) in coachSlots" :key="index">
                                <div class="rounded-2xl border-2 transition-all p-4.5 space-y-4 bg-white flex flex-col justify-between"
                                     :class="coachSlot.students.length > 4 ? 'border-amber-400 bg-amber-50/20' : 'border-[#E5E5EA]'">
                                    
                                    <!-- Slot Header & Coach Dropdown -->
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold uppercase tracking-wider text-[#6E6E73]" x-text="'Coach Slot #' + (index + 1)"></span>
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold"
                                                  :class="coachSlot.students.length > 4 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'"
                                                  x-text="coachSlot.students.length + ' / 4 Pax'">
                                            </span>
                                        </div>

                                        <!-- Select Coach -->
                                        <div>
                                            <label class="block text-[11px] font-bold text-[#1D1D1F] mb-1">Assigned Instructor:</label>
                                            <select x-model="coachSlot.coach_id" required 
                                                    class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs font-semibold text-[#1D1D1F] bg-[#FAFAFC] focus:bg-white focus:border-[#780000]">
                                                <option value="" disabled>-- Select Coach --</option>
                                                <template x-for="c in availableCoaches" :key="c.id">
                                                    <option :value="c.id" x-text="c.name + ' (' + c.current_load + '/4 current load)'"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <!-- Hidden Inputs for Form Submission -->
                                        <template x-for="student in coachSlot.students" :key="student.id">
                                            <input type="hidden" :name="'assignments[' + coachSlot.coach_id + '][]'" :value="student.id">
                                        </template>

                                        <!-- Students in this bucket -->
                                        <div class="space-y-1.5 pt-2">
                                            <div class="text-[11px] font-bold text-[#6E6E73] uppercase tracking-wider">
                                                Assigned Students (<span x-text="coachSlot.students.length"></span>):
                                            </div>

                                            <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                                                <template x-for="student in coachSlot.students" :key="student.id">
                                                    <div class="p-2.5 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] flex items-center justify-between text-xs hover:border-[#D1D1D6] transition-colors">
                                                        <div>
                                                            <div class="font-bold text-[#1D1D1F]" x-text="student.name"></div>
                                                            <div class="text-[10px] text-[#6E6E73]" x-text="'#' + student.booking_number + ' • ' + student.class_type"></div>
                                                        </div>

                                                        <!-- Move to another coach slot if multiple slots exist -->
                                                        <template x-if="coachSlots.length > 1">
                                                            <select @change="moveStudent(student.id, index, parseInt($event.target.value))"
                                                                    class="text-[10px] py-1 px-1.5 rounded-lg border border-[#D1D1D6] bg-white font-medium text-[#6E6E73]">
                                                                <option value="" disabled selected>Move to ➔</option>
                                                                <template x-for="(otherSlot, otherIndex) in coachSlots" :key="otherIndex">
                                                                    <option x-show="otherIndex !== index" :value="otherIndex" x-text="'Coach #' + (otherIndex + 1)"></option>
                                                                </template>
                                                            </select>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </template>
                        </div>

                        <!-- Confirm Balanced Match Action Bar -->
                        <div class="pt-3 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="text-xs text-[#6E6E73]">
                                <span class="font-bold text-[#1D1D1F]">Status:</span>
                                <span x-show="areAllCoachesSelected()" class="text-emerald-700 font-semibold">All coach slots selected. Ready to finalize.</span>
                                <span x-show="!areAllCoachesSelected()" class="text-amber-700 font-semibold">Please select an instructor for each coach slot above.</span>
                            </div>

                            <button type="submit" 
                                    :disabled="!areAllCoachesSelected()"
                                    class="btn-primary px-7 py-3 text-xs sm:text-sm font-bold shadow-md disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                                <span>Confirm Balanced Match</span>
                                <span x-text="'(' + unassignedCount + ' Students across ' + coachSlots.length + ' Coaches)'"></span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- ========================================================================= -->
                <!-- MODE 2: QUICK SINGLE COACH ASSIGNMENT -->
                <!-- ========================================================================= -->
                <div x-show="mode === 'single'" class="space-y-4">
                    <form action="{{ route('admin.coaches.matching.assign') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="batch_id" value="{{ $batch->id }}">

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Students Selection List -->
                            <div class="lg:col-span-2 space-y-3">
                                <label class="block font-bold text-[#1D1D1F] text-xs">
                                    Select Students to Assign:
                                </label>
                                <div class="border border-[#E5E5EA] rounded-xl divide-y divide-[#E5E5EA] max-h-72 overflow-y-auto bg-[#FAFAFC]">
                                    @foreach($students as $student)
                                    <label class="flex items-center justify-between p-3 hover:bg-white cursor-pointer transition-colors text-xs">
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox" 
                                                   name="participant_ids[]" 
                                                   value="{{ $student->id }}" 
                                                   checked
                                                   class="w-4 h-4 rounded text-[#780000] focus:ring-[#780000]">
                                            <div>
                                                <strong class="text-[#1D1D1F] font-bold block">{{ $student->name }}</strong>
                                                <span class="text-[#6E6E73] text-[11px]">
                                                    Booking #{{ $student->booking->booking_number }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-semibold text-[11px] border border-blue-200">
                                                {{ ucfirst($student->booking->class_type ?? 'Discovery') }}
                                            </span>
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Coach Selection & Submit -->
                            <div class="space-y-3 flex flex-col justify-between">
                                <div class="space-y-3">
                                    <label class="block font-bold text-[#1D1D1F] text-xs">
                                        Assign to Available Coach:
                                    </label>
                                    <select name="coach_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white font-medium">
                                        <option value="" disabled selected>-- Select a Coach --</option>
                                        @forelse($availableCoaches as $c)
                                            <option value="{{ $c['id'] }}">
                                                Coach {{ $c['name'] }} (Current load: {{ $c['current_load'] }}/4 Pax)
                                            </option>
                                        @empty
                                            <option value="" disabled>No coaches currently available for this date</option>
                                        @endforelse
                                    </select>
                                </div>

                                <div>
                                    <button type="submit" 
                                            @if(empty($availableCoaches) || count($availableCoaches) === 0) disabled @endif
                                            class="btn-primary w-full py-2.5 text-xs font-bold shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                        Confirm Single Coach Assignment
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white rounded-2xl border border-[#E5E5EA] p-12 text-center space-y-3">
            <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto text-xl font-bold">
                ✓
            </div>
            <h3 class="text-base font-extrabold text-[#1D1D1F]">All Students Are Assigned!</h3>
            <p class="text-xs text-[#6E6E73] max-w-md mx-auto">
                There are currently no unassigned students in upcoming confirmed batches. All active participants are matched with certified instructors.
            </p>
            <div class="pt-2">
                <a href="{{ route('admin.coaches.index') }}" class="btn-secondary px-5 py-2 text-xs font-semibold inline-block">
                    View Coach Roster →
                </a>
            </div>
        </div>
    @endif

</div>

@push('scripts')
<script>
function batchMatcher(config) {
    return {
        batchId: config.batchId,
        unassignedCount: config.unassignedCount,
        students: config.students,
        availableCoaches: config.availableCoaches,
        neededCoaches: config.neededCoaches,
        coachCount: config.neededCoaches,
        maxViableCoaches: Math.max(config.neededCoaches, Math.min(config.availableCoaches.length || 1, Math.ceil(config.unassignedCount / 2))),
        mode: config.unassignedCount > 4 ? 'balanced' : 'single',
        coachSlots: [],

        initMatcher() {
            this.buildSlots();
        },

        onCoachCountChange() {
            this.buildSlots();
        },

        buildSlots() {
            const count = this.coachCount;
            this.coachSlots = [];

            // Pre-assign distinct coaches if available
            for (let i = 0; i < count; i++) {
                const defaultCoach = this.availableCoaches[i] ? this.availableCoaches[i].id : '';
                this.coachSlots.push({
                    coach_id: defaultCoach,
                    students: []
                });
            }

            // Distribute students round-robin across slots, grouping by class type
            const grouped = {};
            this.students.forEach(s => {
                const type = s.class_type || 'Discovery';
                if (!grouped[type]) grouped[type] = [];
                grouped[type].push(s);
            });

            let currentSlot = 0;
            Object.values(grouped).forEach(studentList => {
                studentList.forEach(student => {
                    this.coachSlots[currentSlot].students.push(student);
                    currentSlot = (currentSlot + 1) % count;
                });
            });
        },

        moveStudent(studentId, fromSlotIndex, toSlotIndex) {
            if (fromSlotIndex === toSlotIndex || !this.coachSlots[toSlotIndex]) return;
            const sIndex = this.coachSlots[fromSlotIndex].students.findIndex(s => s.id === studentId);
            if (sIndex > -1) {
                const [student] = this.coachSlots[fromSlotIndex].students.splice(sIndex, 1);
                this.coachSlots[toSlotIndex].students.push(student);
            }
        },

        areAllCoachesSelected() {
            return this.coachSlots.length > 0 && this.coachSlots.every(slot => !!slot.coach_id);
        }
    };
}
</script>
@endpush
@endsection
