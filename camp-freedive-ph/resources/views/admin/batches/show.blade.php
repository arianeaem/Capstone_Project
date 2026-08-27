@extends('layouts.admin')

@section('title', $batch->display_name . ' - Batch Dashboard | Camp FreedivePH')

@section('content')
<div class="space-y-6 text-sm" x-data="{ 
    openCancelModal: false, 
    openRescheduleModal: false,
    openMoveModal: false,
    selectedBookingId: null,
    selectedBookingNumber: ''
}">
    
    <!-- Top Breadcrumb & Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-5">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.batches.index') }}" class="text-xs text-[#6E6E73] hover:text-[#1D1D1F]">
                    ← Back to Batches
                </a>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight mt-1">{{ $batch->batch_number }}</h1>
            <span class="text-xs sm:text-sm text-[#1D1D1F] font-bold block mt-0.5">
                {{ $batch->start_date->format('F d, Y (l)') }} - {{ $batch->end_date->format('F d, Y (l)') }} (2 Days)
            </span>
        </div>

        <!-- Whole-Batch Status Actions -->
        <div class="flex items-center gap-2.5 flex-wrap">
            
            <!-- Confirmed / Active State Options -->
            @if(in_array($batch->status, ['confirmed', 'open']))
                
                <!-- Complete Action -->
                <form action="{{ route('admin.batches.update_status', $batch) }}" method="POST" onsubmit="return confirm('Mark this batch as Completed? This will conclude the 2D1N dive schedule and mark active connected bookings as completed.');">
                    @csrf
                    <input type="hidden" name="status" value="completed">
                    <input type="hidden" name="note" value="2D1N dive schedule concluded successfully.">
                    <button type="submit" class="btn-primary px-4 py-2 text-xs sm:text-sm font-bold shadow-md flex items-center gap-1.5">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span>Mark as Completed</span>
                    </button>
                </form>

                <!-- Reschedule Action Trigger -->
                <button type="button" 
                        @click="openRescheduleModal = true"
                        class="btn-secondary px-3.5 py-2 text-xs sm:text-sm font-semibold flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-[#FF8D28]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    <span>Reschedule Batch</span>
                </button>

                <!-- Cancel by Camp Action Trigger -->
                <button type="button" 
                        @click="openCancelModal = true"
                        class="px-3.5 py-2 text-xs sm:text-sm font-bold text-[#FF3B3C] bg-[#FEF2F2] hover:bg-[#FEE2E2] rounded-xl border border-[#FECACA] transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                    <span>Cancel Batch (by Camp)</span>
                </button>

            @else
                
                <!-- Reopen / Reconfirm Option -->
                <form action="{{ route('admin.batches.update_status', $batch) }}" method="POST" onsubmit="return confirm('Reactivate this batch as Confirmed?');">
                    @csrf
                    <input type="hidden" name="status" value="confirmed">
                    <input type="hidden" name="note" value="Reactivated batch to Confirmed status.">
                    <button type="submit" class="btn-secondary px-4 py-2 text-xs sm:text-sm font-bold">
                        Reactivate Batch
                    </button>
                </form>

            @endif

        </div>
    </div>

    <!-- Batch Summary Banner -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-2">
            <div class="flex items-center gap-3 flex-wrap">
                <span class="px-3 py-1 rounded-full text-xs font-extrabold border {{ $batch->status_badge['class'] }}">
                    Batch Status: {{ $batch->status_badge['label'] }}
                </span>
            </div>

            @if($batch->capacity_note)
                <p class="text-xs text-[#6E6E73] font-medium italic">
                    Note: {{ $batch->capacity_note }}
                </p>
            @endif

            @if($batch->status === 'cancelled_by_camp' && $batch->cancellation_reason)
                <div class="p-3 bg-[#FEF2F2] rounded-xl border border-[#FECACA] text-xs text-[#991B1B]">
                    <strong>Camp Cancellation Advisory:</strong> {{ $batch->cancellation_reason }} (Logged at {{ $batch->cancelled_at ? $batch->cancelled_at->format('M d, Y h:i A') : 'N/A' }})
                </div>
            @endif
        </div>

        <!-- Honest Occupancy Display -->
        <div class="p-5 rounded-xl text-right space-y-1.5 shrink-0 min-w-[260px]">
            <span class="text-xs uppercase font-bold text-[#6E6E73] tracking-wider block">Staffing & Occupancy</span>
            
            @if($batch->is_coach_pending)
                <div class="p-2.5 bg-[#FFFBEB] rounded-xl border border-[#FDE68A] text-left text-xs text-[#92400E]">
                    <strong class="block">Instructor Pending</strong>
                    <span class="text-xs opacity-90">Occupancy not yet calculable until at least 1 coach is assigned.</span>
                </div>
            @else
                <div class="text-2xl font-extrabold text-[#1D1D1F]">
                    {{ $batch->total_participants_count }} <span class="text-sm font-semibold text-[#6E6E73]">/ {{ $batch->computed_capacity }} Pax Capacity</span>
                </div>
                
                <div class="w-full bg-[#E5E5EA] rounded-full h-2 overflow-hidden">
                    <div class="h-2 rounded-full {{ ($batch->occupancy_percentage ?? 0) >= 100 ? 'bg-[#FF3B3C]' : (($batch->occupancy_percentage ?? 0) > 70 ? 'bg-[#FF8D28]' : 'bg-[#34C759]') }}" 
                         style="width: {{ min(100, $batch->occupancy_percentage ?? 0) }}%"></div>
                </div>

                <div class="flex items-center justify-between text-xs font-bold text-[#6E6E73]">
                    <span>{{ $batch->assigned_coaches_count }} Coach(es) Assigned</span>
                    <span>{{ $batch->occupancy_percentage }}% Full</span>
                </div>
            @endif
        </div>
    </div>

    <!-- 2 COLUMN LAYOUT -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- LEFT 2 COLUMNS: CONNECTED BOOKINGS -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Connected Bookings Table -->
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                    <div>
                        <h3 class="text-base font-bold text-[#1D1D1F]">Connected Customer Bookings</h3>
                    </div>

                    <div class="text-xs font-bold text-[#780000] text-right">
                        <div>{{ $batch->bookings->whereNotIn('status', ['cancelled_by_camp', 'cancelled_by_guest'])->count() }} Active Booking(s)</div>
                        <div class="text-[#6E6E73] font-medium">{{ $batch->total_participants_count }} Student(s)</div>
                    </div>
                </div>

                <div class="divide-y divide-[#E5E5EA]">
                    @forelse($batch->bookings as $booking)
                    <div class="py-4 space-y-2.5 text-xs sm:text-sm bg-[#FAFAFC] p-3 rounded-xl border border-[#E5E5EA]">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.bookings.show', $booking) }}" class="font-mono font-extrabold text-[#780000] hover:underline text-sm">
                                        {{ $booking->booking_number }}
                                    </a>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-[#EFF6FF] text-[#1E40AF] border border-[#BFDBFE]">
                                        {{ ucfirst($booking->class_type ?? 'Discovery') }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="text-right">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $booking->status_badge['class'] }}">
                                        {{ $booking->status_badge['label'] }}
                                    </span>
                                    <span class="text-xs font-bold text-[#1D1D1F] block mt-1">
                                        ₱{{ number_format($booking->total_amount, 2) }}
                                    </span>
                                </div>

                                <button type="button" 
                                        @click="selectedBookingId = {{ $booking->id }}; selectedBookingNumber = '{{ $booking->booking_number }}'; openMoveModal = true"
                                        class="btn-secondary px-2.5 py-1 text-xs font-semibold text-[#6E6E73] hover:text-[#1D1D1F] shrink-0"
                                        title="Move to another batch">
                                    Move ⇄
                                </button>
                            </div>
                        </div>

                        <!-- Participants List with Assigned Coach -->
                        <div class="text-xs space-y-1.5">
                            <div class="text-[#6E6E73] font-bold uppercase text-xs">
                                Participants ({{ $booking->participants->count() }}):
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($booking->participants as $p)
                                    @php $coach = $p->coach; @endphp
                                    <div class="text-[#1D1D1F] flex items-center justify-between">
                                        <span><strong>{{ $p->name }}</strong></span>
                                        @if($coach)
                                            <span class="text-xs font-bold text-[#065F46] bg-[#ECFDF5] px-2 py-0.5 rounded-md border border-[#A7F3D0]">
                                                Coach {{ $coach->name }}
                                            </span>
                                        @else
                                            <span class="text-xs font-bold text-[#92400E] bg-[#FEF3C7] px-2 py-0.5 rounded-md border border-[#FDE68A]">
                                                Unassigned
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-[#6E6E73] py-6 text-center">
                        No customer bookings connected to this batch yet.
                    </p>
                    @endforelse
                </div>
            </div>

            <!-- Batch Status Timeline / History -->
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 shadow-sm space-y-4">
                <h3 class="text-base font-bold text-[#1D1D1F] border-b border-[#E5E5EA] pb-3">Batch Status History</h3>

                <div class="space-y-4">
                    @forelse($batch->statusLogs as $log)
                    <div class="flex items-start gap-3 text-xs">
                        <div class="w-2.5 h-2.5 rounded-full bg-[#780000] mt-1.5 shrink-0"></div>
                        <div class="space-y-0.5 flex-grow">
                            <div class="flex items-center justify-between">
                                <strong class="text-[#1D1D1F]">
                                    Status: <span class="uppercase">{{ str_replace('_', ' ', $log->new_status) }}</span>
                                </strong>
                                <span class="text-[#8E8E93] text-xs">{{ $log->created_at->format('M d, Y h:i A') }}</span>
                            </div>
                            @if($log->note)
                                <p class="text-[#6E6E73]">{{ $log->note }}</p>
                            @endif
                            <span class="text-xs text-[#8E8E93] block">
                                By: {{ $log->changer ? $log->changer->name : 'System Trigger' }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-[#6E6E73] py-2 text-center">No status history logged yet.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- RIGHT 1 COLUMN: COACHES & PAYMENTS SUMMARY -->
        <div class="space-y-6">
            
            <!-- Connected Coaches Card -->
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                    <div>
                        <h3 class="text-base font-bold text-[#1D1D1F]">Assigned Coaches</h3>
                        <p class="text-xs text-[#6E6E73] mt-0.5">Staffing for this 2D1N dive schedule.</p>
                    </div>

                    <a href="{{ route('admin.coaches.matching') }}" class="btn-primary px-3 py-1.5 text-xs font-bold shadow-sm">
                        Matching Queue →
                    </a>
                </div>

                @if($unassignedStudentsCount > 0)
                    <div class="p-3 bg-[#FEF2F2] rounded-xl border border-[#FECACA] text-xs text-[#991B1B] flex items-center justify-between gap-2">
                        <span><strong>{{ $unassignedStudentsCount }} student(s)</strong> need a coach!</span>
                        <a href="{{ route('admin.coaches.matching') }}" class="font-bold underline shrink-0">Assign →</a>
                    </div>
                @endif

                <div class="space-y-2">
                    @forelse($batch->assigned_coaches as $coach)
                    @php
                        $coachLoad = $coach->assignedCountForDate($batch->start_date);
                    @endphp
                    <div class="p-3 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-[#780000] text-white flex items-center justify-center font-bold text-xs">
                                {{ substr($coach->name, 0, 1) }}
                            </div>
                            <div>
                                <a href="{{ route('admin.coaches.show', $coach) }}" class="font-bold text-[#1D1D1F] hover:underline block">
                                    {{ $coach->name }}
                                </a>
                                <span class="text-xs text-[#6E6E73]">{{ $coach->email }}</span>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="font-bold text-[#065F46] block">{{ $coachLoad }} / 4 Pax</span>
                            <span class="text-xs text-[#6E6E73]">+4 Cap</span>
                        </div>
                    </div>
                    @empty
                    <div class="p-4 bg-[#FFFBEB] rounded-xl border border-[#FDE68A] text-xs text-[#92400E] text-center">
                        <strong>0 Coaches Assigned:</strong> Use the matching queue to staff this batch.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Payments & Refunds Summary (Read-Only) -->
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                    <div>
                        <h3 class="text-base font-bold text-[#1D1D1F]">Payments & Refunds Summary</h3>
                        <p class="text-xs text-[#6E6E73] mt-0.5">Aggregate financial overview for this batch.</p>
                    </div>

                    <a href="{{ route('admin.payments.index') }}" class="text-xs font-bold text-[#780000] hover:underline">
                        View All →
                    </a>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA]">
                        <span class="text-[#6E6E73]">Total Collected:</span>
                        <strong class="font-mono text-sm font-extrabold text-[#1D1D1F]">
                            ₱{{ number_format($batch->total_collected_amount, 2) }}
                        </strong>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA]">
                        <span class="text-[#6E6E73]">Bookings with Balance:</span>
                        <strong class="text-sm font-extrabold text-[#FF8D28]">
                            {{ $batch->outstanding_balance_bookings_count }} Booking(s)
                        </strong>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA]">
                        <span class="text-[#6E6E73]">Pending Refund Requests:</span>
                        <strong class="text-sm font-extrabold text-[#FF3B3C]">
                            {{ $batch->pending_refunds_count }} Pending
                        </strong>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 1: CANCEL BATCH BY CAMP (CASCADES TO REFUNDS & NOTIFICATIONS) -->
    <!-- ========================================================================= -->
    <div x-show="openCancelModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-[#E5E5EA]" @click.outside="openCancelModal = false">
            <h3 class="text-lg font-bold text-[#FF3B3C]">Cancel Batch (by Camp)</h3>
            <p class="text-xs text-[#6E6E73]">
                Cancelling <strong class="text-[#1D1D1F]">{{ $batch->display_name }}</strong> will automatically cascade to all connected bookings, set them to <strong>Cancelled by Camp</strong>, trigger <strong>100% force majeure refund eligibility</strong>, and send custom cancellation emails to all customers.
            </p>

            <form action="{{ route('admin.batches.update_status', $batch) }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <input type="hidden" name="status" value="cancelled_by_camp">

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-1">
                        Camp Advisory / Cancellation Reason <span class="text-[#780000]">*</span>
                    </label>
                    <textarea name="note" required rows="3" placeholder="e.g. Typhoon storm signal #2 in Batangas / Severe localized marine surge" class="w-full px-3.5 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#E5E5EA]">
                    <button type="button" @click="openCancelModal = false" class="btn-secondary px-3.5 py-1.5 text-xs">Close</button>
                    <button type="submit" class="btn-primary px-5 py-1.5 text-xs font-bold bg-[#FF3B3C] hover:bg-[#D32F2F] shadow-sm">
                        Confirm Whole-Batch Cancellation
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 2: RESCHEDULE BATCH (CASCADES TO CUSTOMER DATE SELECTION NOTIFICATIONS) -->
    <!-- ========================================================================= -->
    <div x-show="openRescheduleModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-[#E5E5EA]" @click.outside="openRescheduleModal = false">
            <h3 class="text-lg font-bold text-[#1D1D1F]">Reschedule Batch (by Camp)</h3>
            <p class="text-xs text-[#6E6E73]">
                Rescheduling <strong class="text-[#1D1D1F]">{{ $batch->display_name }}</strong> will set connected bookings to <strong>Rescheduled</strong> and send a custom email notifying customers to pick their preferred new date through the <strong>Manage Booking</strong> portal.
            </p>

            <form action="{{ route('admin.batches.update_status', $batch) }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <input type="hidden" name="status" value="rescheduled">

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-1">
                        Reschedule Explanation / Instructions <span class="text-[#780000]">*</span>
                    </label>
                    <textarea name="note" required rows="3" placeholder="e.g. Venue maintenance on resort / Weather shift. Please choose a new weekend." class="w-full px-3.5 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#E5E5EA]">
                    <button type="button" @click="openRescheduleModal = false" class="btn-secondary px-3.5 py-1.5 text-xs">Close</button>
                    <button type="submit" class="btn-primary px-5 py-1.5 text-xs font-bold shadow-sm">
                        Confirm Batch Reschedule
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 3: MOVE BOOKING TO ANOTHER BATCH -->
    <!-- ========================================================================= -->
    <div x-show="openMoveModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-[#E5E5EA]" @click.outside="openMoveModal = false">
            <h3 class="text-lg font-bold text-[#1D1D1F]">Move Booking to Another Batch</h3>
            <p class="text-xs text-[#6E6E73]">
                Reassign booking <strong class="text-[#780000] font-mono" x-text="selectedBookingNumber"></strong> to another scheduled 2D1N batch or unbatch it.
            </p>

            <form action="{{ route('admin.batches.move_booking', $batch) }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <input type="hidden" name="booking_id" :value="selectedBookingId">

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-1">Target Batch</label>
                    <select name="target_batch_id" class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white font-medium">
                        <option value="">-- Remove from Batch (Unbatch) --</option>
                        @foreach($otherBatches as $ob)
                            <option value="{{ $ob->id }}">
                                {{ $ob->display_name }} ({{ $ob->start_date->format('M d') }} – {{ $ob->end_date->format('M d, Y') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-1">Reason / Note</label>
                    <input type="text" name="reason" placeholder="e.g. Correcting booking grouping misassignment" class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#E5E5EA]">
                    <button type="button" @click="openMoveModal = false" class="btn-secondary px-3.5 py-1.5 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary px-5 py-1.5 text-xs font-bold shadow-sm">
                        Confirm Move
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
