@extends('layouts.admin')

@section('title', 'Availability Calendar | Coach Portal')

@section('content')
<div class="space-y-6" x-data="coachAvailabilityCalendar()">
    
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Availability Calendar -->
        <div class="lg:col-span-8 xl:col-span-9 space-y-4">
            
            <div class="bg-white rounded-2xl border border-[#E5E5EA] shadow-sm overflow-hidden">
                
                <!-- Calendar Controls -->
                <div class="p-4 sm:p-5 border-b border-[#E5E5EA] flex flex-wrap items-center justify-between gap-4 bg-[#FAFAFC]">
                    <!-- Month Navigator -->
                    <div class="flex items-center gap-2">
                        <div class="flex items-center bg-white rounded-xl border border-[#E5E5EA] p-1 shadow-xs">
                            <a href="{{ route('coach.availability.index', ['year' => $prevMonth->year, 'month' => $prevMonth->month]) }}" 
                               class="p-2 rounded-lg hover:bg-[#F2F2F7] text-[#1D1D1F] transition-colors" title="Previous Month">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                            </a>
                            <span class="px-4 text-xs sm:text-sm font-extrabold text-[#1D1D1F] min-w-[130px] sm:min-w-[150px] text-center">
                                {{ $currentMonth->format('F Y') }}
                            </span>
                            <a href="{{ route('coach.availability.index', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}" 
                               class="p-2 rounded-lg hover:bg-[#F2F2F7] text-[#1D1D1F] transition-colors" title="Next Month">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                            </a>
                        </div>
                        <a href="{{ route('coach.availability.index') }}" 
                           class="text-xs font-bold px-3 py-2 rounded-xl bg-white border border-[#E5E5EA] text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] transition-all shadow-xs">
                            Today
                        </a>
                    </div>

                    <!-- Bulk Edit Toggle -->
                    <button type="button" 
                            @click="toggleBulkMode()"
                            :class="bulkMode ? 'bg-[#780000] text-white border-[#780000] shadow-sm' : 'bg-white text-[#1D1D1F] border-[#E5E5EA] hover:bg-[#F2F2F7] shadow-xs'"
                            class="px-4 py-2 rounded-xl border text-xs font-bold transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        <span x-text="bulkMode ? 'Exit Bulk Mode' : 'Bulk Edit Mode'"></span>
                    </button>
                </div>

                <!-- Day of Week Header -->
                <div class="grid grid-cols-7 border-b border-[#E5E5EA] bg-[#FAFAFC] text-center text-xs font-bold text-[#8E8E93] py-3">
                    <span class="text-rose-600">Sun</span>
                    <span>Mon</span>
                    <span>Tue</span>
                    <span>Wed</span>
                    <span>Thu</span>
                    <span>Fri</span>
                    <span class="text-[#00C3D0]">Sat</span>
                </div>

                <!-- Calendar Days -->
                <div class="grid grid-cols-7 gap-px bg-[#E5E5EA]">
                    @foreach($calendarDays as $day)
                        @php
                            $isOtherMonth = !$day['is_current_month'];
                            $isPast = $day['is_past'];
                            $status = $day['status'];
                            $isAssigned = $day['is_assigned'];
                            $dateStr = $day['date_str'];
                        @endphp

                        <div class="min-h-[110px] sm:min-h-[130px] p-2 sm:p-3 bg-white flex flex-col justify-between transition-colors relative group
                                    {{ $isOtherMonth ? 'bg-gray-50/60 opacity-40' : '' }}
                                    {{ $day['is_today'] ? 'ring-2 ring-inset ring-[#00C3D0]' : '' }}"
                             :class="{
                                 'ring-2 ring-[#780000] bg-rose-50/40': isSelectedInBulk('{{ $dateStr }}'),
                                 'hover:bg-[#F2F2F7] cursor-pointer': !{{ $isPast ? 'true' : 'false' }} && !bulkMode && !{{ $isAssigned ? 'true' : 'false' }},
                                 'cursor-pointer': bulkMode && !{{ $isPast ? 'true' : 'false' }} && !{{ $isAssigned ? 'true' : 'false' }}
                             }"
                             @click="handleDayClick('{{ $dateStr }}', {{ $isAssigned ? 'true' : 'false' }}, {{ $isPast ? 'true' : 'false' }}, {{ json_encode($day) }})">
                            
                            <!-- Day Number & Indicators -->
                            <div class="flex items-center justify-between gap-1">
                                <span class="font-extrabold text-sm {{ $day['is_today'] ? 'text-[#00C3D0]' : ($isOtherMonth ? 'text-gray-400' : 'text-[#1D1D1F]') }}">
                                    {{ $day['day_number'] }}
                                </span>

                                @if($day['is_today'])
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-[#00C3D0] text-white">Today</span>
                                @endif

                                @if($day['has_release_request'])
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 border border-amber-300" title="Release request pending">
                                        Pending Release
                                    </span>
                                @endif

                                <!-- Bulk Mode Selection Indicator -->
                                <template x-if="bulkMode && !{{ $isPast ? 'true' : 'false' }} && !{{ $isAssigned ? 'true' : 'false' }}">
                                    <input type="checkbox" 
                                           :checked="isSelectedInBulk('{{ $dateStr }}')"
                                           class="rounded text-[#780000] focus:ring-[#780000] pointer-events-none">
                                </template>
                            </div>

                            <!-- Day Availability Status -->
                            <div class="my-2">
                                @if($isAssigned)
                                    <div class="p-2 rounded-xl bg-blue-50 border border-blue-200 text-blue-900 space-y-1">
                                        <div class="flex items-center gap-1.5 font-bold text-[11px]">
                                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                            <span>Assigned</span>
                                        </div>
                                        <div class="text-[10px] text-blue-700 truncate font-semibold">
                                            {{ $day['batch']?->batch_number ?? 'Dive Batch' }} ({{ $day['students_count'] }} pax)
                                        </div>
                                    </div>
                                @elseif($status === 'available')
                                    <div class="p-1.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] font-bold flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                        <span>Available</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Day Actions -->
                            <div class="text-[10px] text-right">
                                @if($isAssigned && !$isPast)
                                    <button type="button" 
                                            @click.stop="openReleaseModal({{ json_encode($day) }})"
                                            class="text-[10px] font-bold text-blue-600 hover:text-rose-600 underline">
                                        Release Request
                                    </button>
                                @elseif(!$isPast && !$isOtherMonth)
                                    <span class="text-[#AEAEB2] opacity-0 group-hover:opacity-100 transition-opacity">
                                        {{ $status === 'available' ? 'Click to unselect' : 'Click to select' }}
                                    </span>
                                @endif
                            </div>

                        </div>
                    @endforeach
                </div>

            </div>
        </div>

        <!-- Availability Status & Legend -->
        <div class="lg:col-span-4 xl:col-span-3 space-y-6">
            
            <!-- Calendar Overview -->
            <div class="bg-white rounded-2xl p-5 sm:p-6 border border-[#E5E5EA] shadow-xs space-y-3">
                <h1 class="text-xl font-black text-[#1D1D1F]">My Availability Calendar</h1>
                <p class="text-xs text-[#6E6E73] leading-relaxed">
                    Click any date or weekend to select or unselect your availability. Assigned dates are locked and require an emergency release request.
                </p>
            </div>

            <!-- Status Legend -->
            <div class="bg-white rounded-2xl p-5 sm:p-6 border border-[#E5E5EA] shadow-xs space-y-4 text-xs">
                <span class="font-bold text-[#8E8E93] uppercase tracking-wider text-[11px] block">Status Legend</span>
                
                <div class="space-y-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-3.5 h-3.5 rounded-md bg-emerald-500 border border-emerald-600 inline-block shrink-0"></span>
                        <span class="font-semibold text-[#1D1D1F]">Available</span>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <span class="w-3.5 h-3.5 rounded-md bg-blue-600 border border-blue-700 inline-block shrink-0"></span>
                        <span class="font-semibold text-[#1D1D1F]">Assigned</span>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <span class="w-3.5 h-3.5 rounded-md bg-amber-400 border border-amber-500 inline-block shrink-0"></span>
                        <span class="font-semibold text-amber-800">Release Requested</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Bulk Edit Action Bar -->
    <div x-show="bulkMode" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-10"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="fixed bottom-6 inset-x-4 max-w-3xl mx-auto z-40 bg-[#1D1D1F] text-white p-4 rounded-2xl shadow-2xl border border-white/20 flex flex-col sm:flex-row items-center justify-between gap-3">
        <div>
            <div class="font-bold text-sm text-white">Bulk Edit Mode Active</div>
            <div class="text-xs text-gray-300">
                <span x-text="selectedDates.length" class="font-bold text-[#E0F9FB]"></span> date(s) selected.
            </div>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            <button type="button" 
                    @click="applyBulk('available')"
                    :disabled="selectedDates.length === 0 || bulkSubmitting"
                    class="flex-1 sm:flex-none px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white font-bold text-xs shadow-xs transition-all">
                Mark Available
            </button>
            <button type="button" 
                    @click="applyBulk('remove')"
                    :disabled="selectedDates.length === 0 || bulkSubmitting"
                    class="flex-1 sm:flex-none px-4 py-2 rounded-xl bg-gray-700 hover:bg-gray-600 disabled:opacity-50 text-white font-bold text-xs shadow-xs transition-all">
                Remove Availability
            </button>
            <button type="button" 
                    @click="clearBulkSelection()"
                    class="px-3 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs transition-all">
                Clear
            </button>
        </div>
    </div>

    <!-- Emergency Release Request Modal -->
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

            <template x-if="selectedAssignedDay">
                <div class="space-y-4">
                    <!-- Session Details -->
                    <div class="p-4 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[#1D1D1F]" x-text="selectedAssignedDay.batch?.batch_number || 'Dive Batch'"></span>
                            <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-bold" x-text="selectedAssignedDay.students_count + ' Student(s) Assigned'"></span>
                        </div>
                        <div><strong>Dive Date:</strong> <span x-text="selectedAssignedDay.date_str"></span> (2D1N Session)</div>
                        <div x-text="'Hours until dive departure: ' + selectedAssignedDay.hours_until_dive + 'h'"></div>
                    </div>

                    <!-- Staffing Policy Notice -->
                    <template x-if="!selectedAssignedDay.can_request_release">
                        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-900 space-y-2">
                            <div class="font-bold flex items-center gap-2">
                                <svg class="w-5 h-5 text-rose-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                <span>48-Hour Cutoff Enforced</span>
                            </div>
                            <p class="leading-relaxed">
                                Self-service release requests cannot be submitted within 48 hours of dive departure to protect student safety and staffing continuity.
                            </p>
                            <p class="font-semibold text-rose-950">
                                If you are experiencing a medical or personal emergency, please contact Camp Administration or Owner directly.
                            </p>
                        </div>
                    </template>

                    <!-- Emergency Release Form -->
                    <template x-if="selectedAssignedDay.can_request_release">
                        <form action="{{ route('coach.availability.release') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="batch_id" :value="selectedAssignedDay.batch?.id">
                            <input type="hidden" name="dive_date" :value="selectedAssignedDay.date_str">

                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-[#1D1D1F]">
                                    Reason for Emergency Release <span class="text-rose-500">*</span>
                                </label>
                                <textarea name="reason" 
                                          rows="4" 
                                          required 
                                          placeholder="Please explain the emergency, illness, or unavoidable circumstance requiring reassignment..."
                                          class="w-full text-xs rounded-xl border border-[#D1D1D6] focus:border-[#780000] focus:ring-[#780000] p-3"></textarea>
                                <span class="text-[11px] text-[#8E8E93]">Your request will be submitted to Camp Admin for review and student reassignment.</span>
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
            </template>

        </div>
    </div>

</div>

@push('scripts')
<script>
function coachAvailabilityCalendar() {
    return {
        bulkMode: false,
        selectedDates: [],
        bulkSubmitting: false,
        releaseModalOpen: false,
        selectedAssignedDay: null,

        toggleBulkMode() {
            this.bulkMode = !this.bulkMode;
            if (!this.bulkMode) {
                this.selectedDates = [];
            }
        },

        isSelectedInBulk(dateStr) {
            return this.selectedDates.includes(dateStr);
        },

        handleDayClick(dateStr, isAssigned, isPast, dayData) {
            if (isPast) return;

            if (isAssigned) {
                this.openReleaseModal(dayData);
                return;
            }

            if (this.bulkMode) {
                // Toggle in bulk array (expand 2D1N pair)
                const d = new Date(dateStr + 'T00:00:00');
                let pairStr = '';
                if (d.getDay() === 6) { // Saturday -> Sunday
                    const next = new Date(d);
                    next.setDate(next.getDate() + 1);
                    pairStr = next.toISOString().split('T')[0];
                } else if (d.getDay() === 0) { // Sunday -> Saturday
                    const prev = new Date(d);
                    prev.setDate(prev.getDate() - 1);
                    pairStr = prev.toISOString().split('T')[0];
                } else {
                    const next = new Date(d);
                    next.setDate(next.getDate() + 1);
                    pairStr = next.toISOString().split('T')[0];
                }

                if (this.selectedDates.includes(dateStr)) {
                    this.selectedDates = this.selectedDates.filter(x => x !== dateStr && x !== pairStr);
                } else {
                    if (!this.selectedDates.includes(dateStr)) this.selectedDates.push(dateStr);
                    if (pairStr && !this.selectedDates.includes(pairStr)) this.selectedDates.push(pairStr);
                }
                return;
            }

            // Normal single 2D1N toggle
            this.executeToggle(dateStr);
        },

        async executeToggle(dateStr) {
            try {
                const res = await fetch("{{ route('coach.availability.toggle') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ date: dateStr })
                });

                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to update availability.');
                }
            } catch (err) {
                console.error(err);
                window.location.reload();
            }
        },

        async applyBulk(status) {
            if (this.selectedDates.length === 0) return;
            this.bulkSubmitting = true;

            try {
                const res = await fetch("{{ route('coach.availability.bulk') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        dates: this.selectedDates,
                        status: status
                    })
                });

                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to apply bulk update.');
                    this.bulkSubmitting = false;
                }
            } catch (err) {
                console.error(err);
                window.location.reload();
            }
        },

        clearBulkSelection() {
            this.selectedDates = [];
        },

        openReleaseModal(dayData) {
            this.selectedAssignedDay = dayData;
            this.releaseModalOpen = true;
        }
    };
}
</script>
@endpush
@endsection
