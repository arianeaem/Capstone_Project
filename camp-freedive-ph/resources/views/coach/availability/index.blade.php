@extends('layouts.admin')

@section('title', 'Availability Calendar | Coach Portal')

@section('content')
@php
    $selectableDates = array_values(array_filter(array_map(function($d) {
        return (!$d['is_past'] && !$d['is_assigned'] && $d['is_current_month']) ? $d['date_str'] : null;
    }, $calendarDays)));
@endphp
<div class="space-y-6" x-data="coachAvailabilityCalendar({{ json_encode($selectableDates) }})">
    
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Availability Calendar -->
        <div class="lg:col-span-8 xl:col-span-9 space-y-4">
            
            <div class="bg-white rounded-2xl border border-[#E5E5EA] overflow-hidden">
                
                <!-- Calendar Controls -->
                <div class="p-4 sm:p-5 border-b border-[#E5E5EA] flex flex-wrap items-center justify-between gap-4 bg-[#FAFAFC]">
                    <!-- Month Navigator -->
                    <div class="flex items-center gap-2">
                        <div class="flex items-center bg-white rounded-xl border border-[#E5E5EA] p-1">
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
                           class="text-xs font-bold px-3 py-2 rounded-xl bg-white border border-[#E5E5EA] text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] transition-all">
                            Today
                        </a>
                    </div>

                    <!-- Bulk Edit Toggle -->
                    <button type="button" 
                            @click="toggleBulkMode()"
                            :class="bulkMode ? 'bg-[#780000] text-white border-[#780000]' : 'bg-white text-[#1D1D1F] border-[#E5E5EA] hover:bg-[#F2F2F7]'"
                            class="px-4 py-2 rounded-xl border text-xs font-bold transition-all flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        <span x-text="bulkMode ? 'Exit Bulk Mode' : 'Bulk Edit Mode'"></span>
                    </button>
                </div>

                <!-- Calendar Grid Container with Responsive Horizontal Scroll for Mobile -->
                <div class="overflow-x-auto pb-2 scrollbar-none"
                     @touchstart="touchStartX = $event.touches[0].clientX; touchStartY = $event.touches[0].clientY; touchMoved = false"
                     @touchmove="if (Math.abs($event.touches[0].clientX - touchStartX) > 10 || Math.abs($event.touches[0].clientY - touchStartY) > 10) { touchMoved = true; }">
                    <div class="min-w-[620px] sm:min-w-0">
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
                                    $assignedDayNumber = $day['assigned_day_number'] ?? 1;
                                    $dateStr = $day['date_str'];
                                    $hasRelease = $day['has_release_request'];

                                    // Determine whole card background color & typography (No badges, full date color)
                                    if ($isAssigned) {
                                        if ($assignedDayNumber === 1) {
                                            $cellBg = 'bg-[#780000] text-white border-transparent';
                                            $numColor = 'text-white';
                                            $statusTitle = 'Day 1 Assigned';
                                            $hoverEffect = 'hover:brightness-110';
                                        } else {
                                            $cellBg = 'bg-[#00C3D0] text-white border-transparent';
                                            $numColor = 'text-white';
                                            $statusTitle = 'Day 2 Assigned';
                                            $hoverEffect = 'hover:brightness-105';
                                        }
                                    } elseif ($status === 'available') {
                                        $cellBg = 'bg-emerald-600 text-white border-transparent';
                                        $numColor = 'text-white';
                                        $statusTitle = 'Available';
                                        $hoverEffect = 'hover:bg-emerald-700';
                                    } else {
                                        $cellBg = 'bg-white text-[#1D1D1F]';
                                        $numColor = $isOtherMonth ? 'text-gray-400' : 'text-[#1D1D1F]';
                                        $statusTitle = '';
                                        $hoverEffect = 'hover:bg-[#F2F2F7]';
                                    }
                                @endphp

                                <div class="min-h-[100px] sm:min-h-[130px] p-2.5 sm:p-3.5 flex flex-col justify-between transition-all relative group select-none
                                            {{ $cellBg }}
                                            {{ $isOtherMonth ? 'opacity-30 pointer-events-none' : '' }}
                                            {{ $isPast ? 'opacity-55 cursor-not-allowed' : '' }}
                                            {{ $day['is_today'] ? 'ring-2 ring-inset ring-amber-400' : '' }}"
                                     :class="{
                                         'ring-4 ring-inset ring-amber-400 brightness-105': isSelectedInBulk('{{ $dateStr }}'),
                                         '{{ $hoverEffect }} cursor-pointer': !{{ $isPast ? 'true' : 'false' }} && !bulkMode,
                                         'cursor-pointer': bulkMode && !{{ $isPast ? 'true' : 'false' }} && !{{ $isAssigned ? 'true' : 'false' }}
                                     }"
                                     @click="handleDayClick('{{ $dateStr }}', {{ $isAssigned ? 'true' : 'false' }}, {{ $isPast ? 'true' : 'false' }}, {{ json_encode($day) }})">
                                    
                                    <!-- Day Number & Header Indicators -->
                                    <div class="flex items-start justify-between gap-1">
                                        <span class="font-black text-sm sm:text-base {{ $numColor }} shrink-0">
                                            {{ $day['day_number'] }}
                                        </span>

                                        <div class="flex items-center gap-1 shrink-0">
                                            @if($day['is_today'])
                                                <span class="text-[9px] sm:text-[10px] font-black px-1.5 py-0.5 rounded bg-amber-400 text-amber-950 uppercase tracking-wider whitespace-nowrap shrink-0">Today</span>
                                            @endif

                                            @if($hasRelease)
                                                <span class="text-[9px] sm:text-[10px] font-black px-1.5 py-0.5 rounded bg-amber-400 text-amber-950 shadow-2xs whitespace-nowrap shrink-0" title="Release request pending">
                                                    Pending
                                                </span>
                                            @endif

                                            <!-- Bulk Mode Selection Indicator -->
                                            <template x-if="bulkMode && !{{ $isPast ? 'true' : 'false' }} && !{{ $isAssigned ? 'true' : 'false' }}">
                                                <div class="w-4 h-4 rounded border border-current flex items-center justify-center pointer-events-none transition-colors"
                                                     :class="isSelectedInBulk('{{ $dateStr }}') ? 'bg-amber-400 border-amber-400 text-black' : 'bg-white/30 text-transparent'">
                                                    <svg x-show="isSelectedInBulk('{{ $dateStr }}')" class="w-3 h-3 text-black stroke-[3]" viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Middle: Direct Status Text on Colored Card (No badges) -->
                                    <div class="my-auto py-1">
                                        @if($isAssigned)
                                            <div class="space-y-0.5">
                                                <div class="font-black text-xs sm:text-sm text-white tracking-wide leading-tight whitespace-nowrap truncate">
                                                    {{ $statusTitle }}
                                                </div>
                                                <div class="text-[10px] sm:text-[11px] text-white/90 truncate font-semibold leading-tight whitespace-nowrap">
                                                    {{ $day['batch']?->batch_number ?? 'Dive Batch' }}
                                                    @if($day['students_count'] > 0)
                                                        ({{ $day['students_count'] }} pax)
                                                    @endif
                                                </div>
                                            </div>
                                        @elseif($status === 'available')
                                            <div class="font-black text-xs sm:text-sm text-white tracking-wide whitespace-nowrap">
                                                Available
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Bottom: Action Links & Hover Hints -->
                                    <div class="text-[10px] text-right">
                                        @if($isAssigned && !$isPast)
                                            <button type="button" 
                                                    @click.stop="openReleaseModal({{ json_encode($day) }})"
                                                    class="text-[10px] font-bold text-white/90 hover:text-white underline transition-colors whitespace-nowrap">
                                                Release Request
                                            </button>
                                        @elseif(!$isPast && !$isOtherMonth)
                                            <span class="text-[10px] font-medium transition-opacity whitespace-nowrap {{ $status === 'available' ? 'text-emerald-100 opacity-0 group-hover:opacity-100' : 'text-[#8E8E93] opacity-0 group-hover:opacity-100' }}">
                                                {{ $status === 'available' ? 'Click to unselect' : 'Click to select' }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Toggling Loading Spinner Overlay -->
                                    <div x-show="isToggling && (togglingDate === '{{ $dateStr }}' || get2D1NPair(togglingDate) === '{{ $dateStr }}')"
                                         x-cloak
                                         class="absolute inset-0 bg-black/30 rounded-lg backdrop-blur-2xs flex items-center justify-center z-10">
                                        <svg class="animate-spin w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25"></circle>
                                            <path d="M4 12a8 8 0 018-8" stroke="currentColor"></path>
                                        </svg>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Availability Status & Legend -->
        <div class="lg:col-span-4 xl:col-span-3 space-y-6">
            
            <!-- Calendar Overview -->
            <div class="bg-white rounded-2xl p-5 sm:p-6 border border-[#E5E5EA] space-y-3">
                <h1 class="text-xl font-black text-[#1D1D1F]">My Availability Calendar</h1>
                <p class="text-xs text-[#6E6E73] leading-relaxed">
                    Click any date or weekend to select or unselect your availability. Assigned dates are locked and require an emergency release request.
                </p>
            </div>

            <!-- Status Legend -->
            <div class="bg-white rounded-2xl p-5 sm:p-6 border border-[#E5E5EA] space-y-4 text-xs">
                <span class="font-bold text-[#8E8E93] uppercase tracking-wider text-[11px] block">Status Legend</span>
                
                <div class="space-y-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-4 h-4 rounded-md bg-emerald-600 inline-block shrink-0 shadow-2xs"></span>
                        <span class="font-semibold text-[#1D1D1F]">Available</span>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <span class="w-4 h-4 rounded-md bg-[#780000] inline-block shrink-0 shadow-2xs"></span>
                        <span class="font-semibold text-[#1D1D1F]">Assigned Color for Day 1</span>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <span class="w-4 h-4 rounded-md bg-[#00C3D0] inline-block shrink-0 shadow-2xs"></span>
                        <span class="font-semibold text-[#1D1D1F]">Assigned Color for Day 2</span>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <span class="w-4 h-4 rounded-md bg-amber-400 inline-block shrink-0 shadow-2xs"></span>
                        <span class="font-semibold text-[#1D1D1F]">Release Requested</span>
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
         class="fixed bottom-4 sm:bottom-6 inset-x-3 sm:inset-x-4 max-w-2xl mx-auto z-40 bg-[#1D1D1F] text-white p-3.5 sm:p-4 rounded-2xl shadow-2xl border border-white/20 flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="flex items-center justify-between w-full sm:w-auto gap-3">
            <div>
                <div class="font-bold text-xs sm:text-sm text-white flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                    <span>Bulk Edit Mode Active</span>
                </div>
                <div class="text-[11px] sm:text-xs text-gray-300">
                    <span x-text="selectedDates.length" class="font-black text-amber-300"></span> date(s) selected
                </div>
            </div>
            <button type="button" 
                    @click="toggleSelectAllMonth()"
                    class="text-[11px] font-bold text-[#00C3D0] hover:underline sm:hidden cursor-pointer">
                <span x-text="allSelectableDates.length > 0 && allSelectableDates.every(d => selectedDates.includes(d)) ? 'Deselect All' : 'Select All Month'"></span>
            </button>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            <button type="button" 
                    @click="toggleSelectAllMonth()"
                    class="hidden sm:inline-flex px-3 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs transition-all cursor-pointer">
                <span x-text="allSelectableDates.length > 0 && allSelectableDates.every(d => selectedDates.includes(d)) ? 'Deselect All' : 'Select All Month'"></span>
            </button>
            <button type="button" 
                    @click="applyBulk('available')"
                    :disabled="selectedDates.length === 0 || bulkSubmitting"
                    class="flex-1 sm:flex-none px-3.5 sm:px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 disabled:opacity-40 text-white font-bold text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                <span x-show="!bulkSubmitting">Mark Available</span>
                <span x-show="bulkSubmitting" class="flex items-center gap-1">
                    <svg class="animate-spin w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25"></circle><path d="M4 12a8 8 0 018-8" stroke="currentColor"></path></svg>
                    <span>Saving...</span>
                </span>
            </button>
            <button type="button" 
                    @click="applyBulk('remove')"
                    :disabled="selectedDates.length === 0 || bulkSubmitting"
                    class="flex-1 sm:flex-none px-3.5 sm:px-4 py-2 rounded-xl bg-rose-700 hover:bg-rose-600 disabled:opacity-40 text-white font-bold text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                <span>Remove</span>
            </button>
            <button type="button" 
                    @click="clearBulkSelection()"
                    class="px-2.5 sm:px-3 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs transition-all cursor-pointer">
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
                        <div><strong>Dive Date:</strong> <span x-text="selectedAssignedDay.date_str"></span></div>
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
                                <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 text-white text-xs font-bold hover:bg-rose-700">
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
function coachAvailabilityCalendar(selectableDates = []) {
    return {
        bulkMode: false,
        selectedDates: [],
        allSelectableDates: selectableDates,
        bulkSubmitting: false,
        isToggling: false,
        togglingDate: null,
        releaseModalOpen: false,
        selectedAssignedDay: null,
        touchStartX: 0,
        touchStartY: 0,
        touchMoved: false,

        toggleBulkMode() {
            this.bulkMode = !this.bulkMode;
            if (!this.bulkMode) {
                this.selectedDates = [];
            }
        },

        isSelectedInBulk(dateStr) {
            return this.selectedDates.includes(dateStr);
        },

        get2D1NPair(dateStr) {
            if (!dateStr) return '';
            const parts = dateStr.split('-');
            if (parts.length !== 3) return '';
            const y = parseInt(parts[0], 10);
            const m = parseInt(parts[1], 10) - 1;
            const d = parseInt(parts[2], 10);
            const dateObj = new Date(y, m, d);
            const dayOfWeek = dateObj.getDay(); // 0 = Sun, 6 = Sat
            
            let pairObj = new Date(y, m, d);
            if (dayOfWeek === 6) { // Saturday -> Sunday (+1)
                pairObj.setDate(pairObj.getDate() + 1);
            } else if (dayOfWeek === 0) { // Sunday -> Saturday (-1)
                pairObj.setDate(pairObj.getDate() - 1);
            } else { // Weekday -> Next day (+1)
                pairObj.setDate(pairObj.getDate() + 1);
            }
            
            const py = pairObj.getFullYear();
            const pm = String(pairObj.getMonth() + 1).padStart(2, '0');
            const pd = String(pairObj.getDate()).padStart(2, '0');
            return `${py}-${pm}-${pd}`;
        },

        handleDayClick(dateStr, isAssigned, isPast, dayData) {
            if (this.touchMoved) return; // Disregard tap if user was scrolling horizontally on mobile
            if (isPast) return;

            if (isAssigned) {
                this.openReleaseModal(dayData);
                return;
            }

            if (this.bulkMode) {
                const pairStr = this.get2D1NPair(dateStr);
                const pairDates = pairStr ? [dateStr, pairStr] : [dateStr];

                const alreadySelected = this.selectedDates.includes(dateStr);
                if (alreadySelected) {
                    this.selectedDates = this.selectedDates.filter(d => !pairDates.includes(d));
                } else {
                    pairDates.forEach(d => {
                        if (!this.selectedDates.includes(d)) {
                            this.selectedDates.push(d);
                        }
                    });
                }
                return;
            }

            // Normal single 2D1N toggle
            this.executeToggle(dateStr);
        },

        toggleSelectAllMonth() {
            const allSelected = this.allSelectableDates.length > 0 && this.allSelectableDates.every(d => this.selectedDates.includes(d));
            if (allSelected) {
                this.selectedDates = [];
            } else {
                this.selectedDates = [...this.allSelectableDates];
            }
        },

        async executeToggle(dateStr) {
            if (this.isToggling) return;
            this.isToggling = true;
            this.togglingDate = dateStr;

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
                    this.isToggling = false;
                    this.togglingDate = null;
                }
            } catch (err) {
                console.error(err);
                window.location.reload();
            }
        },

        async applyBulk(status) {
            if (this.selectedDates.length === 0 || this.bulkSubmitting) return;
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
