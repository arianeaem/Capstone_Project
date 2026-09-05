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
                    <button type="submit" class="btn-primary px-4 py-2 text-xs sm:text-sm font-bold shadow-2xs flex items-center gap-1.5">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span>Mark as Completed</span>
                    </button>
                </form>

                <!-- Reschedule Action Trigger -->
                <button type="button" 
                        @click="openRescheduleModal = true"
                        class="btn-secondary px-3.5 py-2 text-xs sm:text-sm font-semibold flex items-center gap-1.5">
                    <span>Reschedule Batch</span>
                </button>

                <!-- Cancel by Camp Action Trigger -->
                <button type="button" 
                        @click="openCancelModal = true"
                        class="btn-danger px-3.5 py-2 text-xs sm:text-sm font-bold flex items-center gap-1.5">
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

    <!-- Batch Details & Operations -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Connected Bookings -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Connected Bookings Section -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-[#1D1D1F]">Connected Customer Bookings</h3>
                        <p class="text-xs text-[#6E6E73] mt-0.5">Guest reservations and assigned coaching groups in this batch.</p>
                    </div>

                    <div class="text-xs font-bold text-[#780000] text-right">
                        <div>{{ $batch->bookings->whereNotIn('status', ['cancelled_by_camp', 'cancelled_by_guest', 'cancelled', 'pending_downpayment'])->count() }} Active Booking(s)</div>
                        <div class="text-[#6E6E73] font-medium">{{ $batch->total_participants_count }} Student(s)</div>
                    </div>
                </div>

                <!-- Bookings List -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($batch->bookings->whereNotIn('status', ['cancelled_by_camp', 'cancelled_by_guest', 'cancelled', 'pending_downpayment']) as $booking)
                    <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-5 shadow-2xs hover:border-[#D1D1D6] transition-all flex flex-col justify-between space-y-4">
                        <!-- Booking Header & Status -->
                        <div class="space-y-3">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <a href="{{ route('admin.bookings.show', $booking) }}" class="font-mono font-extrabold text-sm text-[#780000] hover:underline">
                                            {{ $booking->booking_number }}
                                        </a>
                                        <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-[#FAFAFC] text-[#3A3A3C] border border-[#E5E5EA]">
                                            {{ ucfirst($booking->class_type ?? 'Discovery') }}
                                        </span>
                                    </div>
                                </div>

                                <div class="text-right shrink-0">
                                    <span class="px-2.5 py-0.5 rounded-md text-xs font-bold border {{ $booking->status_badge['class'] }}">
                                        {{ $booking->status_badge['label'] }}
                                    </span>
                                </div>
                            </div>

                            <!-- Payment Summary -->
                            <div class="grid grid-cols-2 gap-2 p-2.5 rounded-lg bg-[#FAFAFC] border border-[#E5E5EA] text-xs">
                                <div>
                                    <span class="text-[#6E6E73] text-xs block">Total Amount</span>
                                    <strong class="text-[#1D1D1F] font-bold text-xs">₱{{ number_format($booking->total_amount, 2) }}</strong>
                                </div>
                                <div class="text-right">
                                    <span class="text-[#6E6E73] text-xs block">
                                        {{ $booking->balance_amount > 0 ? 'Balance Due' : 'Payment Status' }}
                                    </span>
                                    @if($booking->balance_amount > 0)
                                        <strong class="text-amber-700 font-bold text-xs">₱{{ number_format($booking->balance_amount, 2) }}</strong>
                                    @else
                                        <strong class="text-emerald-700 font-bold text-xs">Fully Paid</strong>
                                    @endif
                                </div>
                            </div>

                            <!-- Participants & Assigned Coaches -->
                            <div class="space-y-1.5 pt-1">
                                <div class="text-xs font-bold uppercase tracking-wider text-[#6E6E73] flex items-center justify-between">
                                    <span>Students ({{ $booking->participants->count() }})</span>
                                    <span>Assigned Coach</span>
                                </div>
                                <div class="space-y-1.5">
                                    @foreach($booking->participants as $p)
                                        @php $coach = $p->coach; @endphp
                                        <div class="flex items-center justify-between gap-2 text-xs">
                                            <span class="font-semibold text-[#1D1D1F] truncate">{{ $p->name }}</span>
                                            @if($coach)
                                                <span class="text-xs font-bold text-[#1D1D1F] px-2 py-0.5 rounded-md shrink-0">
                                                    {{ $coach->name }}
                                                </span>
                                            @else
                                                <span class="text-xs font-bold text-[#8E8E93] px-2 py-0.5 rounded-md shrink-0">
                                                    Unassigned
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Booking Actions -->
                        <div class="flex items-center gap-2 pt-3 border-t border-[#E5E5EA]">
                            <button type="button" 
                                    @click="selectedBookingId = {{ $booking->id }}; selectedBookingNumber = '{{ $booking->booking_number }}'; openMoveModal = true"
                                    class="flex-1 btn-secondary py-2 px-3 text-xs font-semibold text-center"
                                    title="Move to another batch">
                                Move Batch
                            </button>

                            <a href="{{ route('admin.bookings.show', $booking) }}" 
                               class="flex-1 btn-secondary py-2 px-3 text-xs font-semibold text-center">
                                View Details
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-full bg-white rounded-xl border border-[#E5E5EA] p-8 text-center text-xs text-[#8E8E93]">
                        No customer bookings connected to this batch yet.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Batch Status History -->
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-5 sm:p-6 shadow-2xs space-y-4">
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

        <!-- Batch Summary & Logistics -->
        <div class="space-y-6">
            
            <!-- Batch Status & Occupancy -->
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-5 sm:p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                    <h3 class="text-base font-bold text-[#1D1D1F]">Batch Status & Capacity</h3>
                    <span class="px-2.5 py-0.5 rounded-md text-xs font-bold border {{ $batch->status_badge['class'] }}">
                        {{ $batch->status_badge['label'] }}
                    </span>
                </div>

                @if($batch->capacity_note)
                    <p class="text-xs text-[#6E6E73] font-medium italic">
                        Note: {{ $batch->capacity_note }}
                    </p>
                @endif

                @if($batch->status === 'cancelled_by_camp' && $batch->cancellation_reason)
                    <div class="p-3 bg-[#FEF2F2] rounded-xl border border-[#FECACA] text-xs text-rose-800">
                        <strong>Camp Cancellation Advisory:</strong> {{ $batch->cancellation_reason }} (Logged at {{ $batch->cancelled_at ? $batch->cancelled_at->format('M d, Y h:i A') : 'N/A' }})
                    </div>
                @endif

                <!-- Occupancy & Capacity -->
                <div class="space-y-2 pt-1">
                    @if($batch->total_participants_count > 0 && $batch->is_coach_pending)
                        <div class="p-2.5 bg-amber-50 rounded-lg border border-amber-200 text-left text-xs text-amber-900">
                            <strong class="block">Instructor Pending</strong>
                        </div>
                    @else
                        <div class="flex items-baseline justify-between">
                            <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider">Occupancy</span>
                            <div class="text-xl font-extrabold text-[#1D1D1F]">
                                {{ $batch->total_participants_count }} <span class="text-xs font-semibold text-[#6E6E73]">/ {{ $batch->computed_capacity }} Pax</span>
                            </div>
                        </div>
                        
                        <div class="w-full bg-[#E5E5EA] rounded-full h-2 overflow-hidden">
                            <div class="h-2 rounded-full {{ ($batch->occupancy_percentage ?? 0) >= 100 ? 'bg-[#780000]' : (($batch->occupancy_percentage ?? 0) > 70 ? 'bg-amber-600' : 'bg-emerald-600') }}" 
                                 style="width: {{ min(100, $batch->occupancy_percentage ?? 0) }}%"></div>
                        </div>
                    @endif
                </div>

                <!-- Assigned Coaches Count & Status -->
                <div class="space-y-2 pt-3 border-t border-[#E5E5EA]">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-[#6E6E73] font-medium">Assigned Instructors:</span>
                        <strong class="font-bold text-[#1D1D1F]">
                            {{ $batch->coachAssignments->unique('coach_id')->count() }} Coach(es)
                        </strong>
                    </div>

                    <div class="pt-2">
                        <a href="{{ route('admin.coaches.matching') }}" class="btn-secondary w-full py-2 text-xs font-semibold text-center block">
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
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-5 sm:p-6 shadow-2xs space-y-3">
                <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                    <h3 class="text-base font-bold text-[#1D1D1F]">Transportation Logistics</h3>
                    @if($totalCarpoolPax > 0)
                        <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-[#F8EAEA] text-[#780000] border border-[#F1D5D5]">
                            {{ $totalCarpoolPax }} Carpool Pax
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-md text-xs font-semibold bg-[#FAFAFC] text-[#6E6E73] border border-[#E5E5EA]">
                            Own Transpo Only
                        </span>
                    @endif
                </div>

                <div class="space-y-2.5 text-xs">

                    @if($carpoolBookingsList->isNotEmpty())
                        <div class="space-y-1.5 pt-1">
                            <span class="text-[11px] font-bold text-[#6E6E73] uppercase tracking-wider block">Pickup Hub Locations:</span>
                            @foreach($carpoolBookingsList as $cb)
                                <div class="p-2 rounded-lg bg-[#FAFAFC] border border-[#E5E5EA] flex items-center justify-between gap-2">
                                    <div class="min-w-0">
                                        <span class="font-bold text-[#780000] block truncate">
                                            {{ $cb->pickup_location ?: 'Metro Manila Hub' }}
                                        </span>
                                        <span class="text-[11px] text-[#6E6E73] block truncate">
                                            {{ $cb->contact_name }} ({{ $cb->participants->count() }} pax)
                                        </span>
                                    </div>
                                    <a href="{{ route('admin.bookings.show', $cb) }}" class="text-[11px] font-bold text-[#780000] hover:underline shrink-0">
                                        View
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-[#8E8E93] text-center py-1">
                            All students are using own transportation.
                        </p>
                    @endif
                </div>
            </div>

            <!-- Financial Overview -->
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-5 sm:p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                    <h3 class="text-base font-bold text-[#1D1D1F]">Financial Overview</h3>
                    <a href="{{ route('admin.payments.index') }}" class="text-xs text-[#780000] font-bold hover:underline">
                        Ledger
                    </a>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-[#6E6E73]">Total Expected Revenue:</span>
                        <strong class="text-sm font-extrabold text-[#1D1D1F]">
                            ₱{{ number_format($batch->total_revenue, 2) }}
                        </strong>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-[#6E6E73]">Verified Collected Amount:</span>
                        <strong class="text-sm font-extrabold text-emerald-700">
                            ₱{{ number_format($batch->collected_revenue, 2) }}
                        </strong>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-[#6E6E73]">Bookings with Balance:</span>
                        <strong class="text-sm font-extrabold text-amber-700">
                            {{ $batch->outstanding_balance_bookings_count }} Booking(s)
                        </strong>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-[#6E6E73]">Pending Refund Requests:</span>
                        <strong class="text-sm font-extrabold text-[#780000]">
                            {{ $batch->pending_refunds_count }} Pending
                        </strong>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Cancel Batch Modal -->
    <div x-show="openCancelModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-[#E5E5EA]" @click.outside="openCancelModal = false">
            <h3 class="text-lg font-bold text-[#780000]">Cancel Batch (by Camp)</h3>
            <p class="text-xs text-[#6E6E73]">
                Cancelling <strong class="text-[#1D1D1F]">{{ $batch->display_name }}</strong> will automatically cascade to all connected bookings, set them to <strong>Cancelled by Camp</strong>, trigger <strong>100% force majeure refund eligibility</strong>, and send custom cancellation emails to all customers.
            </p>

            <form action="{{ route('admin.batches.update_status', $batch) }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <input type="hidden" name="status" value="cancelled_by_camp">

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-2">
                        Camp Advisory / Cancellation Reason <span class="text-[#780000]">*</span>
                    </label>
                    <textarea name="note" required rows="3" placeholder="e.g. Typhoon storm signal #2 in Batangas / Severe localized marine surge" class="w-full px-3 py-2 rounded-lg border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#E5E5EA]">
                    <button type="button" @click="openCancelModal = false" class="btn-secondary px-3.5 py-1.5 text-xs">Close</button>
                    <button type="submit" class="btn-danger px-4 py-1.5 text-xs font-bold shadow-2xs">
                        Confirm Cancellation
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reschedule Batch Modal -->
    <div x-show="openRescheduleModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-[#E5E5EA]" @click.outside="openRescheduleModal = false">
            <h3 class="text-lg font-bold text-[#1D1D1F]">Reschedule Batch (by Camp)</h3>
            <p class="text-xs text-[#6E6E73]">
                Rescheduling <strong class="text-[#1D1D1F]">{{ $batch->display_name }}</strong> will set connected bookings to <strong>Rescheduled</strong> and send a custom email notifying customers to pick their preferred new date through the <strong>Manage Booking</strong> portal.
            </p>

            <form action="{{ route('admin.batches.update_status', $batch) }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <input type="hidden" name="status" value="rescheduled">

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-2">
                        Reschedule Explanation / Instructions <span class="text-[#780000]">*</span>
                    </label>
                    <textarea name="note" required rows="3" placeholder="e.g. Venue maintenance on resort / Weather shift. Please choose a new weekend." class="w-full px-3 py-2 rounded-lg border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#E5E5EA]">
                    <button type="button" @click="openRescheduleModal = false" class="btn-secondary px-3.5 py-1.5 text-xs">Close</button>
                    <button type="submit" class="btn-primary px-4 py-1.5 text-xs font-bold shadow-2xs">
                        Confirm Batch Reschedule
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Move Booking Modal -->
    <div x-show="openMoveModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-[#E5E5EA]" @click.outside="openMoveModal = false">
            <h3 class="text-lg font-bold text-[#1D1D1F]">Move Booking to Another Batch</h3>
            <p class="text-xs text-[#6E6E73]">
                Reassign booking <strong class="text-[#780000] font-mono" x-text="selectedBookingNumber"></strong> to another scheduled 2D1N batch or unbatch it.
            </p>

            <form action="{{ route('admin.batches.move_booking', $batch) }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <input type="hidden" name="booking_id" :value="selectedBookingId">

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-2">Target Batch</label>
                    <select name="target_batch_id" class="w-full px-3 py-2 rounded-lg border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white font-medium">
                        <option value="">-- Remove from Batch (Unbatch) --</option>
                        @foreach($otherBatches as $ob)
                            <option value="{{ $ob->id }}">
                                {{ $ob->display_name }} ({{ $ob->start_date->format('M d') }} – {{ $ob->end_date->format('M d, Y') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-2">Reason / Note</label>
                    <input type="text" name="reason" placeholder="e.g. Correcting booking grouping misassignment" class="w-full px-3 py-2 rounded-lg border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#E5E5EA]">
                    <button type="button" @click="openMoveModal = false" class="btn-secondary px-3.5 py-1.5 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary px-4 py-1.5 text-xs font-bold shadow-2xs">
                        Confirm Move
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
