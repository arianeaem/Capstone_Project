@extends('layouts.admin')

@section('title', $batch->display_name . ' - Batch Dashboard | Camp FreedivePH')

@section('content')
<div class="space-y-6 text-sm" x-data="{ 
    openCancelModal: false, 
    openRescheduleModal: false,
    openMoveModal: false,
    openCompleteModal: false,
    openReactivateModal: false,
    selectedBookingId: null,
    selectedBookingNumber: ''
}">
    
    <!-- Top Breadcrumb & Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.batches.index') }}" class="text-sm text-[#6E6E73] hover:text-[#1D1D1F] flex items-center gap-1.5 font-medium">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    <span>Back to Batches</span>
                </a>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight mt-1">{{ $batch->batch_number }}</h1>
            <span class="text-sm sm:text-sm text-[#1D1D1F] font-bold block mt-0.5">
                {{ $batch->start_date->format('F d, Y (l)') }} - {{ $batch->end_date->format('F d, Y (l)') }}
            </span>
        </div>

        <!-- Whole-Batch Status Actions -->
        <div class="flex items-center gap-2.5 flex-wrap">
            
            <!-- Confirmed / Active State Options -->
            @if(in_array($batch->status, ['confirmed', 'open']))
                
                <!-- Complete Action Trigger -->
                <button type="button" 
                        @click="openCompleteModal = true"
                        class="btn-primary px-4 py-2 text-sm sm:text-sm font-bold shadow-2xs flex items-center gap-1.5">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>Mark as Completed</span>
                </button>

                <!-- Reschedule Action Trigger -->
                <button type="button" 
                        @click="openRescheduleModal = true"
                        class="btn-secondary px-3.5 py-2 text-sm sm:text-sm font-semibold flex items-center gap-1.5">
                    <span>Reschedule Batch</span>
                </button>

                <!-- Cancel by Camp Action Trigger -->
                <button type="button" 
                        @click="openCancelModal = true"
                        class="btn-danger px-3.5 py-2 text-sm sm:text-sm font-bold flex items-center gap-1.5">
                    <span>Cancel Batch (by Camp)</span>
                </button>

            @else
                
                <!-- Reopen / Reconfirm Option Trigger -->
                <button type="button"
                        @click="openReactivateModal = true"
                        class="btn-secondary px-4 py-2 text-sm sm:text-sm font-bold flex items-center gap-1.5">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                    <span>Reactivate Batch</span>
                </button>

            @endif

        </div>
    </div>

    <!-- Batch Details & Operations -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Connected Bookings -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Connected Bookings Section -->
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-3.5">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            @php
                                $connectedBookingsCount = $batch->bookings->whereNotIn('status', ['cancelled_by_camp', 'cancelled_by_guest', 'cancelled', 'pending_downpayment'])->count();
                            @endphp
                            <h3 class="text-base font-extrabold text-[#1D1D1F]">
                                {{ $connectedBookingsCount }} Connected Customer {{ Str::plural('Booking', $connectedBookingsCount) }}
                            </h3>
                        </div>
                    </div>
                </div>

                <!-- Bookings List -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                    @forelse($batch->bookings->whereNotIn('status', ['cancelled_by_camp', 'cancelled_by_guest', 'cancelled', 'pending_downpayment']) as $booking)
                    <div class="bg-white rounded-2xl border border-[#E5E5EA] p-4 sm:p-5 shadow-2xs hover:border-[#780000] transition-all flex flex-col justify-between space-y-4">
                        <!-- Card Content -->
                        <div class="space-y-3.5">
                            <!-- Header: Booking Number & Status with Class Category Together -->
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <a href="{{ route('admin.bookings.show', $booking) }}" class="text-base sm:text-lg font-black text-[#1D1D1F] hover:text-[#780000] transition-colors tracking-tight font-mono whitespace-nowrap">
                                        {{ $booking->booking_number }}
                                    </a>
                                </div>

                                <div class="flex items-center gap-1.5 flex-wrap justify-end shrink-0">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs sm:text-sm font-bold whitespace-nowrap {{ $booking->status_badge['class'] }}">
                                        {{ $booking->status_badge['label'] }}
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-[#E5E5EA] text-[#1D1D1F] whitespace-nowrap">
                                        {{ ucfirst($booking->class_type ?? 'Discovery') }}
                                    </span>
                                </div>
                            </div>

                            <!-- Students & Pod Assignment List -->
                            <div class="space-y-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-[#8E8E93] block">
                                    Students & Coaches ({{ $booking->participants->count() }})
                                </span>

                                <div class="space-y-2.5">
                                    @foreach($booking->participants as $p)
                                        @php 
                                             $coach = $p->coach; 
                                             $rawCondition = trim($p->health_condition ?? '');
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
                                        @endphp
                                        <div class="bg-[#F8F9FA] rounded-xl p-3 sm:p-3.5 space-y-2">
                                            <!-- Line 1: Student Name, Age, Swimmer Badge -->
                                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <strong class="text-sm font-bold text-[#1D1D1F]">{{ $p->name }}</strong>
                                                    <span class="text-xs text-[#8E8E93] font-normal">({{ $p->age }} yrs old)</span>
                                                </div>
                                                <span class="px-2 py-0.5 rounded-md text-xs font-semibold shrink-0 {{ strtolower($p->swimmer_status) === 'non_swimmer' ? 'bg-rose-50 text-rose-700' : 'bg-white text-[#1D1D1F] shadow-2xs' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $p->swimmer_status ?? 'Swimmer')) }}
                                                </span>
                                            </div>

                                            @if($hasMedical)
                                                <div class="text-xs font-medium text-amber-900 bg-amber-50/80 px-2.5 py-1 rounded-lg flex items-start gap-1.5" title="{{ $p->health_condition }}">
                                                    <span class="break-words leading-tight">{{ $p->health_condition }}</span>
                                                </div>
                                            @endif

                                            <!-- Line 2: Coach Assignment on dedicated row -->
                                            <div class="space-y-1 pt-0.5">
                                                <span class="text-xs text-[#8E8E93] font-semibold uppercase tracking-wider block">Assigned Coach:</span>
                                                @if(isset($assignedCoaches) && count($assignedCoaches) > 0)
                                                    <form action="{{ auth()->user()->isOwner() ? route('owner.batches.assign_participant', $batch) : route('admin.batches.assign_participant', $batch) }}" 
                                                          method="POST"
                                                          class="w-full">
                                                        @csrf
                                                        <input type="hidden" name="participant_id" value="{{ $p->id }}">
                                                         <select name="coach_id" 
                                                                onchange="this.form.submit()" 
                                                                class="w-full text-xs sm:text-sm font-semibold py-1.5 px-3 rounded-xl border border-[#D1D1D6] bg-white text-[#1D1D1F] hover:border-[#AEAEB2] focus:border-[#780000] cursor-pointer transition-all shadow-2xs {{ $coach ? 'text-[#780000] font-bold bg-[#FDF5F5] border-[#F1D5D5]' : '' }}">
                                                            <option value="" {{ !$coach ? 'selected' : '' }}>-- Shared Pool (Unassigned) --</option>
                                                            @foreach($assignedCoaches as $batchCoach)
                                                                <option value="{{ $batchCoach->id }}" {{ $coach && $coach->id === $batchCoach->id ? 'selected' : '' }}>
                                                                    Coach {{ $batchCoach->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </form>
                                                @else
                                                    <span class="text-xs sm:text-sm font-medium text-[#8E8E93] italic block">
                                                        {{ $coach ? 'Coach ' . $coach->name : 'Shared Pool' }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer Actions -->
                        <div class="flex items-center justify-between pt-3 border-t border-[#F2F2F7]">
                            <button type="button" 
                                    @click="selectedBookingId = {{ $booking->id }}; selectedBookingNumber = '{{ $booking->booking_number }}'; openMoveModal = true"
                                    class="text-sm font-semibold text-[#6E6E73] hover:text-[#1D1D1F] py-1 px-2 rounded-md hover:bg-gray-100 transition-colors"
                                    title="Move to another batch">
                                Move Batch
                            </button>

                            <a href="{{ route('admin.bookings.show', $booking) }}" 
                               class="btn-secondary py-1.5 px-3 text-sm font-semibold text-center">
                                View Details
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-full bg-white rounded-2xl border border-[#E5E5EA] p-8 text-center text-sm text-[#8E8E93]">
                        No customer bookings connected to this batch yet.
                    </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Batch Summary & Logistics -->
        <div class="space-y-6">
            
            <!-- Batch Status & Occupancy -->
            <div class="bg-white rounded-2xl border border-[#E5E5EA] p-5 sm:p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-[#1D1D1F]">Batch Status & Capacity</h3>
                    <span class="px-2.5 py-0.5 rounded-md text-sm font-bold {{ $batch->status_badge['class'] }}">
                        {{ $batch->status_badge['label'] }}
                    </span>
                </div>

                @if($batch->capacity_note)
                    <p class="text-sm text-[#6E6E73] font-medium italic">
                        Note: {{ $batch->capacity_note }}
                    </p>
                @endif

                @if($batch->status === 'cancelled_by_camp' && $batch->cancellation_reason)
                    <div class="p-3 bg-[#FEF2F2] rounded-xl border border-[#FECACA] text-sm text-rose-800">
                        <strong>Camp Cancellation Advisory:</strong> {{ $batch->cancellation_reason }} (Logged at {{ $batch->cancelled_at ? $batch->cancelled_at->format('M d, Y h:i A') : 'N/A' }})
                    </div>
                @endif

                <!-- Occupancy & Capacity -->
                <div class="space-y-2 pt-1">
                    @if($batch->total_participants_count > 0 && $batch->is_coach_pending)
                        <div class="p-2.5 bg-amber-50 rounded-lg border border-amber-200 text-left text-sm text-amber-900">
                            <strong class="block">Instructor Pending</strong>
                        </div>
                    @else
                        <div class="flex items-baseline justify-between">
                            <span class="text-sm font-bold text-[#6E6E73] uppercase tracking-wider">Occupancy</span>
                            <div class="text-xl font-extrabold text-[#1D1D1F]">
                                {{ $batch->total_participants_count }} <span class="text-sm font-semibold text-[#6E6E73]">/ {{ $batch->computed_capacity }} Pax</span>
                            </div>
                        </div>
                        
                        <div class="w-full bg-[#E5E5EA] rounded-full h-2 overflow-hidden">
                            <div class="h-2 rounded-full {{ ($batch->occupancy_percentage ?? 0) >= 100 ? 'bg-[#780000]' : (($batch->occupancy_percentage ?? 0) > 70 ? 'bg-amber-600' : 'bg-emerald-600') }}" 
                                 style="width: {{ min(100, $batch->occupancy_percentage ?? 0) }}%"></div>
                        </div>
                    @endif
                </div>

                <!-- Assigned Coaches Count & Status -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-[#6E6E73] font-medium">Assigned Instructors:</span>
                        <strong class="font-bold text-[#1D1D1F]">
                            {{ $batch->coachAssignments->unique('coach_id')->count() }} Coach(es)
                        </strong>
                    </div>

                    <div class="pt-2">
                        <a href="{{ route('admin.coaches.matching') }}" class="btn-secondary w-full py-2 text-sm font-semibold text-center block">
                            Manage Coach Assignments
                        </a>
                    </div>
                </div>
            </div>

            <!-- Transportation Logistics -->
            @php
                $carpoolBookingsList = $batch->bookings->where('pickup_option', 'carpool');
                $totalCarpoolPax = $carpoolBookingsList->sum(fn($b) => $b->participants->count());
            @endphp
            <div class="bg-white rounded-2xl border border-[#E5E5EA] p-5 sm:p-6 shadow-2xs space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-[#1D1D1F]">Transportation Logistics</h3>
                    @if($totalCarpoolPax > 0)
                        <span class="px-2.5 py-0.5 rounded-full text-sm font-bold bg-[#F8EAEA] text-[#780000] border border-[#F1D5D5]">
                            {{ $totalCarpoolPax }} Carpool Pax
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-sm font-semibold bg-[#F2F2F7] text-[#6E6E73] border border-[#E5E5EA]">
                            Own Transpo Only
                        </span>
                    @endif
                </div>

                <div class="text-sm">
                    @if($carpoolBookingsList->isNotEmpty())
                        <div class="space-y-2 pt-1">
                            <div class="divide-y divide-[#F2F2F7]">
                                @foreach($carpoolBookingsList as $cb)
                                    <div class="py-2.5 first:pt-0 last:pb-0 flex items-center justify-between gap-2">
                                        <div class="min-w-0">
                                            <span class="font-bold text-[#1D1D1F] block truncate">
                                                {{ $cb->pickup_location ?: 'Metro Manila Hub' }}
                                            </span>
                                            <span class="text-sm text-[#6E6E73] block truncate">
                                                {{ $cb->contact_name }} ({{ $cb->participants->count() }} pax)
                                            </span>
                                        </div>
                                        <a href="{{ route('admin.bookings.show', $cb) }}" class="text-sm font-semibold text-[#780000] hover:underline shrink-0">
                                            View
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-[#8E8E93] text-center py-2">
                            All students are using own transportation.
                        </p>
                    @endif
                </div>
            </div>

            <!-- Financial Overview -->
            <div class="bg-white rounded-2xl border border-[#E5E5EA] p-5 sm:p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-[#1D1D1F]">Financial Overview</h3>
                    <a href="{{ route('admin.payments.index') }}" class="text-sm text-[#780000] font-bold hover:underline">
                        Ledger
                    </a>
                </div>

                <div class="space-y-3.5 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-[#6E6E73] font-medium">Total Expected Revenue:</span>
                        <strong class="text-sm font-extrabold text-[#1D1D1F]">
                            ₱{{ number_format($batch->total_revenue, 2) }}
                        </strong>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-[#6E6E73] font-medium">Verified Collected Amount:</span>
                        <strong class="text-sm font-extrabold text-emerald-700">
                            ₱{{ number_format($batch->collected_revenue, 2) }}
                        </strong>
                    </div>

                    <!-- Bookings with Balance -->
                    <div class="space-y-1.5 pt-2 border-t border-[#F2F2F7]">
                        <div class="flex items-center justify-between">
                            <span class="text-[#6E6E73] font-medium">Bookings with Balance:</span>
                            <strong class="text-sm font-extrabold text-amber-700">
                                {{ $batch->outstanding_balance_bookings_count }} {{ Str::plural('Booking', $batch->outstanding_balance_bookings_count) }}
                            </strong>
                        </div>

                        @if($batch->outstanding_balance_bookings->isNotEmpty())
                            <div class="space-y-1 pt-1">
                                @foreach($batch->outstanding_balance_bookings as $balBooking)
                                    <div class="flex items-center justify-between text-sm px-2.5 py-1 text-amber-900">
                                        <a href="{{ route('admin.bookings.show', $balBooking) }}" class="font-bold hover:underline text-[#780000] flex items-center gap-1 font-mono truncate mr-2">
                                            <span>{{ $balBooking->booking_number }}</span>
                                        </a>
                                        <span class="font-bold text-amber-800 shrink-0">₱{{ number_format($balBooking->balance_amount, 2) }} due</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-[#F2F2F7]">
                        <span class="text-[#6E6E73] font-medium">Pending Refund Requests:</span>
                        <strong class="text-sm font-extrabold {{ $batch->pending_refunds_count > 0 ? 'text-[#780000]' : 'text-[#8E8E93]' }}">
                            {{ $batch->pending_refunds_count }} Pending
                        </strong>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Batch Status History (Placed at bottom for clean mobile layout) -->
    <div class="bg-white rounded-2xl border border-[#E5E5EA] p-5 sm:p-6 shadow-2xs space-y-4">
        <h3 class="text-base font-bold text-[#1D1D1F] border-b border-[#E5E5EA] pb-3">Batch Status History</h3>

        <div class="space-y-4">
            @forelse($batch->statusLogs as $log)
            <div class="flex items-start gap-3 text-sm">
                <div class="w-2.5 h-2.5 rounded-full bg-[#780000] mt-1.5 shrink-0"></div>
                <div class="space-y-0.5 flex-grow">
                    <div class="flex flex-col sm:flex-row sm:items-baseline justify-between gap-1">
                        <strong class="text-[#1D1D1F]">
                            Status: <span class="uppercase">{{ str_replace('_', ' ', $log->new_status) }}</span>
                        </strong>
                        <span class="text-[#8E8E93] text-xs sm:text-sm shrink-0">{{ $log->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                    @if($log->note)
                        <p class="text-[#6E6E73] break-words">{{ $log->note }}</p>
                    @endif
                    <span class="text-xs sm:text-sm text-[#8E8E93] block">
                        By: {{ $log->changer ? $log->changer->name : 'System Trigger' }}
                    </span>
                </div>
            </div>
            @empty
            <p class="text-sm text-[#6E6E73] py-2 text-center">No status history logged yet.</p>
            @endforelse
        </div>
    </div>

    <!-- Cancel Batch Modal -->
    <div x-show="openCancelModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-[#E5E5EA]" @click.outside="openCancelModal = false">
            <h3 class="text-lg font-bold text-[#780000]">Cancel Batch (by Camp)</h3>
            <p class="text-sm text-[#6E6E73]">
                Cancelling <strong class="text-[#1D1D1F]">{{ $batch->display_name }}</strong> will automatically update all connected bookings, set them to <strong>Cancelled by Camp</strong>, trigger <strong>100% force majeure refund eligibility</strong>, and send custom cancellation emails to all customers.
            </p>

            <form action="{{ route('admin.batches.update_status', $batch) }}" method="POST" class="space-y-3 text-sm">
                @csrf
                <input type="hidden" name="status" value="cancelled_by_camp">

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-2">
                        Camp Advisory / Cancellation Reason <span class="text-[#780000]">*</span>
                    </label>
                    <textarea name="note" required rows="3" placeholder="e.g. Typhoon storm signal #2 in Batangas / Severe localized marine surge" class="w-full px-3 py-2 rounded-lg border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3">
                    <button type="button" @click="openCancelModal = false" class="btn-secondary px-3.5 py-1.5 text-sm">Close</button>
                    <button type="submit" class="btn-danger px-4 py-1.5 text-sm font-bold shadow-2xs">
                        Confirm Cancellation
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reschedule Batch Modal -->
    <div x-show="openRescheduleModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-[#E5E5EA]" @click.outside="openRescheduleModal = false">
            <h3 class="text-lg font-bold text-[#1D1D1F]">Reschedule Batch (by Camp)</h3>
            <p class="text-sm text-[#6E6E73]">
                Rescheduling <strong class="text-[#1D1D1F]">{{ $batch->display_name }}</strong> will set connected bookings to <strong>Rescheduled</strong> and send a custom email notifying customers to pick their preferred new date through the <strong>Manage Booking</strong> portal.
            </p>

            <form action="{{ route('admin.batches.update_status', $batch) }}" method="POST" class="space-y-3 text-sm">
                @csrf
                <input type="hidden" name="status" value="rescheduled">

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-2">
                        Reschedule Explanation / Instructions <span class="text-[#780000]">*</span>
                    </label>
                    <textarea name="note" required rows="3" placeholder="e.g. Venue maintenance on resort / Weather shift. Please choose a new weekend." class="w-full px-3 py-2 rounded-lg border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3">
                    <button type="button" @click="openRescheduleModal = false" class="btn-secondary px-3.5 py-1.5 text-sm">Close</button>
                    <button type="submit" class="btn-primary px-4 py-1.5 text-sm font-bold shadow-2xs">
                        Confirm Batch Reschedule
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Move Booking Modal -->
    <div x-show="openMoveModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-[#E5E5EA]" @click.outside="openMoveModal = false">
            <h3 class="text-lg font-bold text-[#1D1D1F]">Move Booking to Another Batch</h3>
            <p class="text-sm text-[#6E6E73]">
                Reassign booking <strong class="text-[#780000] font-mono" x-text="selectedBookingNumber"></strong> to another scheduled 2D1N batch or unbatch it.
            </p>

            <form action="{{ route('admin.batches.move_booking', $batch) }}" method="POST" class="space-y-3 text-sm">
                @csrf
                <input type="hidden" name="booking_id" :value="selectedBookingId">

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-2">Target Batch</label>
                    <select name="target_batch_id" class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white font-medium">
                        <option value="">-- Remove from Batch (Unbatch) --</option>
                        @foreach($otherBatches as $ob)
                            <option value="{{ $ob->id }}">
                                {{ $ob->display_name }} ({{ $ob->start_date->format('M d') }} to {{ $ob->end_date->format('M d, Y') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-2">Reason / Note</label>
                    <input type="text" name="reason" placeholder="e.g. Correcting booking grouping misassignment" class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white font-medium">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3">
                    <button type="button" @click="openMoveModal = false" class="btn-secondary px-3.5 py-1.5 text-sm">Cancel</button>
                    <button type="submit" class="btn-primary px-4 py-1.5 text-sm font-bold shadow-2xs">
                        Confirm Move
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Complete Batch Modal -->
    <div x-show="openCompleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-[#E5E5EA]" @click.outside="openCompleteModal = false">
            <div class="flex items-center gap-3">
                <div>
                    <h3 class="text-lg font-bold text-[#1D1D1F]">Mark Batch as Completed</h3>
                    <p class="text-xs text-[#6E6E73]">{{ $batch->batch_number }} &bull; {{ $batch->start_date->format('M d') }} - {{ $batch->end_date->format('M d, Y') }}</p>
                </div>
            </div>

            <p class="text-sm text-[#6E6E73]">
                Marking this batch as <strong class="text-[#1D1D1F]">Completed</strong> will conclude the 2D1N dive schedule and transition active connected bookings to completed status.
            </p>

            <form action="{{ route('admin.batches.update_status', $batch) }}" method="POST" class="space-y-3 text-sm">
                @csrf
                <input type="hidden" name="status" value="completed">
                <input type="hidden" name="note" value="2D1N dive schedule concluded successfully.">

                <div class="flex items-center justify-end gap-2 pt-3">
                    <button type="button" @click="openCompleteModal = false" class="btn-secondary px-3.5 py-1.5 text-sm">Cancel</button>
                    <button type="submit" class="btn-primary px-4 py-1.5 text-sm font-bold shadow-2xs">
                        Confirm & Complete
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reactivate Batch Modal -->
    <div x-show="openReactivateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-[#E5E5EA]" @click.outside="openReactivateModal = false">
            <div class="flex items-center gap-3">
                <div>
                    <h3 class="text-lg font-bold text-[#1D1D1F]">Reactivate Batch</h3>
                    <p class="text-xs text-[#6E6E73]">{{ $batch->batch_number }} &bull; {{ $batch->start_date->format('M d') }} - {{ $batch->end_date->format('M d, Y') }}</p>
                </div>
            </div>

            <p class="text-sm text-[#6E6E73]">
                Are you sure you want to reactivate <strong class="text-[#1D1D1F]">{{ $batch->batch_number }}</strong> ({{ $batch->start_date->format('M d') }} - {{ $batch->end_date->format('M d, Y') }}) to <strong class="text-emerald-700 font-bold">Confirmed</strong> status? This will reopen the batch for schedule and roster operations.
            </p>

            <form action="{{ route('admin.batches.update_status', $batch) }}" method="POST" class="space-y-3 text-sm">
                @csrf
                <input type="hidden" name="status" value="confirmed">
                <input type="hidden" name="note" value="Reactivated batch to Confirmed status.">

                <div class="flex items-center justify-end gap-2 pt-3">
                    <button type="button" @click="openReactivateModal = false" class="btn-secondary px-3.5 py-1.5 text-sm">Cancel</button>
                    <button type="submit" class="btn-primary px-4 py-1.5 text-sm font-bold shadow-2xs">
                        Confirm Reactivation
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
