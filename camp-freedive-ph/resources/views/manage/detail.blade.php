@extends('layouts.app')

@section('title', 'Booking #' . $booking->booking_number . ' | Camp FreedivePH')
@section('meta_description', 'View and manage your Camp FreedivePH booking details, reschedule dates, or cancel.')

@section('content')
<div class="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8 py-6 sm:py-12 text-sm"
     x-data="manageBooking({
         bookingNumber: '{{ $booking->booking_number }}',
         pin: '{{ $booking->pin }}',
         csrfToken: '{{ csrf_token() }}',
         checkWeatherUrl: '{{ route('api.weather.check') }}',
         currentStartDate: '{{ $booking->start_date->format('Y-m-d') }}',
         currentEndDate: '{{ $booking->end_date->format('Y-m-d') }}'
     })">

    <!-- Top Navigation / Breadcrumb -->
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('manage.index') }}" class="text-xs sm:text-sm font-semibold text-[#6E6E73] hover:text-[#1D1D1F] flex items-center gap-1.5">
            ← Switch Booking
        </a>
        <div class="text-xs sm:text-sm text-[#8E8E93]">
            Booking Created: {{ $booking->created_at->format('M d, Y') }}
        </div>
    </div>

    <!-- Booking Overview Header -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-8 shadow-sm mb-6 sm:mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-6 mb-6">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-[#780000]">Reservation Details</span>
                <div class="flex flex-wrap items-center gap-2 sm:gap-3 mt-1">
                    <h1 class="text-xl sm:text-3xl font-extrabold text-[#1D1D1F] font-mono tracking-wide">
                        {{ $booking->booking_number }}
                    </h1>
                    <span class="px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full text-xs font-bold border {{ $booking->status_badge['bg'] }}">
                        {{ $booking->status_badge['label'] }}
                    </span>
                </div>
            </div>

            <!-- PIN Badge -->
            <div class="bg-[#F8EAEA] border border-[#780000]/20 rounded-xl p-3 sm:p-3.5 sm:text-right shrink-0">
                <span class="text-xs font-bold uppercase tracking-wider text-[#780000] block">Security PIN</span>
                <span class="text-base sm:text-lg font-mono font-extrabold text-[#780000] tracking-widest">{{ $booking->pin }}</span>
            </div>
        </div>

        <!-- Key Specs Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 text-sm">
            <div class="p-3.5 sm:p-4 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA]">
                <span class="text-xs text-[#6E6E73] block mb-1">Lead Booker:</span>
                <strong class="text-sm text-[#1D1D1F] block">{{ $booking->contact_name }}</strong>
                <span class="text-xs text-[#6E6E73] break-all">{{ $booking->contact_email }}</span>
            </div>

            <div class="p-3.5 sm:p-4 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA]">
                <span class="text-xs text-[#6E6E73] block mb-1">Class Package:</span>
                <strong class="text-sm text-[#1D1D1F] block">{{ $booking->formatted_class_type }}</strong>
                <span class="text-xs text-[#6E6E73]">{{ $booking->participants->count() }} Participant(s)</span>
            </div>

            <div class="p-3.5 sm:p-4 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA]">
                <span class="text-xs text-[#6E6E73] block mb-1">Trip Dates (2D1N):</span>
                <strong class="text-sm text-[#1D1D1F] block">{{ $booking->start_date->format('M d, Y') }} - {{ $booking->end_date->format('M d, Y') }}</strong>
                <span class="text-xs text-[#780000] font-semibold">
                    @if($policy['days_until_dive'] > 0)
                        {{ $policy['days_until_dive'] }} days until dive trip
                    @elseif($policy['days_until_dive'] === 0)
                        Dive trip is today
                    @else
                        Completed trip
                    @endif
                </span>
            </div>

            <div class="p-3.5 sm:p-4 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA]">
                <span class="text-xs text-[#6E6E73] block mb-1">Payment Status:</span>
                <strong class="text-sm text-[#34C759] block">₱{{ number_format($booking->downpayment_amount, 2) }} Downpayment Paid</strong>
                <span class="text-xs text-[#780000] font-bold">₱{{ number_format($booking->balance_amount, 2) }} balance due at camp</span>
            </div>
        </div>
    </div>

    <!-- MAIN TWO COLUMN GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">

        <!-- LEFT 2 COLUMNS: BOOKING DETAILS & LOGISTICS -->
        <div class="lg:col-span-2 space-y-6 sm:space-y-8">
            
            <!-- Participant List -->
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-8 shadow-sm">
                <h3 class="text-base font-bold text-[#1D1D1F] mb-4">Divers in this Booking ({{ $booking->participants->count() }})</h3>
                <div class="divide-y divide-[#E5E5EA]">
                    @foreach($booking->participants as $p)
                    <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-sm">
                        <div>
                            <span class="font-bold text-[#1D1D1F]">{{ $p->name }}</span>
                            <span class="text-xs text-[#6E6E73] ml-1.5">Age {{ $p->age }}</span>
                            <div class="text-xs text-[#6E6E73] mt-0.5">
                                <strong>Medical Notes:</strong> {{ $p->health_condition ?: 'None declared' }}
                            </div>
                        </div>
                        <div class="text-left sm:text-right">
                            <span class="px-2.5 py-0.5 rounded text-xs font-semibold bg-[#F2F2F7] text-[#1D1D1F]">
                                {{ ucfirst(str_replace('_', ' ', $p->swimmer_status ?: 'Swimmer')) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Logistics & Add-ons -->
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-8 shadow-sm">
                <h3 class="text-base font-bold text-[#1D1D1F] mb-4">Transportation & Add-ons</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div class="p-4 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA]">
                        <span class="text-xs text-[#6E6E73] block mb-1">Transportation:</span>
                        <strong class="text-sm text-[#1D1D1F] block">
                            {{ $booking->pickup_option === 'carpool' ? 'Manila Carpool Service' : 'Own Transportation' }}
                        </strong>
                        @if($booking->pickup_location)
                            <span class="text-xs text-[#780000] font-medium block mt-1">Pickup Hub: {{ $booking->pickup_location }}</span>
                        @endif
                    </div>

                    <div class="p-4 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA]">
                        <span class="text-xs text-[#6E6E73] block mb-1">Boat Dive:</span>
                        <strong class="text-sm text-[#1D1D1F] block">
                            {{ $booking->boat_dive ? 'Included (+₱600 / person)' : 'Shore Entry Dive' }}
                        </strong>
                        <span class="text-xs text-[#6E6E73] block mt-1">Mabini LGU pass included</span>
                    </div>
                </div>
            </div>

            <!-- Pending Requests History (if any) -->
            @if($booking->rescheduleRequests->isNotEmpty() || $booking->cancellationRequests->isNotEmpty())
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-8 shadow-sm space-y-4">
                <h3 class="text-base font-bold text-[#1D1D1F]">Request History</h3>

                @foreach($booking->rescheduleRequests as $req)
                <div class="p-4 rounded-xl bg-[#FFFBEB] border border-[#FDE68A] text-sm text-[#92400E] space-y-1">
                    <div class="flex items-center justify-between font-bold">
                        <span>Reschedule Request ({{ $req->created_at->format('M d, Y') }})</span>
                        <span class="uppercase tracking-wider px-2 py-0.5 rounded bg-white text-xs font-bold">{{ $req->status }}</span>
                    </div>
                    <p>Requested Move: {{ $req->requested_start_date->format('M d, Y') }} - {{ $req->requested_end_date->format('M d, Y') }}</p>
                    @if($req->reason)
                        <p class="text-xs text-[#78350F]">Reason: {{ $req->reason }}</p>
                    @endif
                </div>
                @endforeach

                @foreach($booking->cancellationRequests as $cReq)
                <div class="p-4 rounded-xl bg-[#FEF2F2] border border-[#FECACA] text-sm text-[#991B1B] space-y-1">
                    <div class="flex items-center justify-between font-bold">
                        <span>Cancellation Request ({{ $cReq->created_at->format('M d, Y') }})</span>
                        <span class="uppercase tracking-wider px-2 py-0.5 rounded bg-white text-xs font-bold">{{ $cReq->status }}</span>
                    </div>
                    <p>Calculated Refund: ₱{{ number_format($cReq->calculated_refund_amount, 2) }}</p>
                    @if($cReq->reason)
                        <p class="text-xs">Reason: {{ $cReq->reason }}</p>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

        </div>

        <!-- RIGHT 1 COLUMN: LIVE POLICY ENGINE & ACTIONS -->
        <div class="space-y-6 text-sm">

            <!-- Policy Engine Status Box -->
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-8 shadow-sm space-y-5">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-[#780000]">Automated Policy Engine</span>
                    <h3 class="text-lg font-bold text-[#1D1D1F] mt-0.5">Self-Service Actions</h3>
                    <p class="text-xs text-[#6E6E73] mt-1">Live policy based on days before your dive date.</p>
                </div>

                <!-- Reschedule Status Card -->
                <div class="p-4 rounded-xl border {{ $policy['reschedule_allowed'] ? 'border-[#34C759]/40 bg-[#ECFDF5]/50' : 'border-[#E5E5EA] bg-[#FAFAFC]' }} space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-[#1D1D1F]">Reschedule Dive Date</span>
                        <span class="text-xs font-bold px-2 py-0.5 rounded {{ $policy['reschedule_allowed'] ? 'bg-[#34C759] text-white' : 'bg-[#E5E5EA] text-[#6E6E73]' }}">
                            {{ $policy['reschedule_allowed'] ? 'Allowed' : 'Closed' }}
                        </span>
                    </div>
                    <p class="text-xs text-[#6E6E73] leading-relaxed">{{ $policy['reschedule_message'] }}</p>
                    
                    @if($policy['reschedule_allowed'])
                        <button type="button" 
                                @click="openRescheduleModal = true" 
                                class="btn-primary w-full py-2.5 text-xs font-bold mt-2">
                            Reschedule Booking Date →
                        </button>
                    @endif
                </div>

                <!-- Cancellation Status Card -->
                <div class="p-4 rounded-xl border {{ $policy['cancel_allowed'] ? 'border-[#FF8D28]/40 bg-[#FFFBEB]/50' : 'border-[#E5E5EA] bg-[#FAFAFC]' }} space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-[#1D1D1F]">Cancel / Refund</span>
                        <span class="text-xs font-bold px-2 py-0.5 rounded {{ $policy['cancel_allowed'] ? 'bg-[#FF8D28] text-white' : 'bg-[#E5E5EA] text-[#6E6E73]' }}">
                            {{ $policy['cancel_allowed'] ? 'Eligible' : 'Non-Refundable' }}
                        </span>
                    </div>
                    <p class="text-xs text-[#6E6E73] leading-relaxed">{{ $policy['cancel_message'] }}</p>

                    @if($policy['cancel_allowed'] || $booking->status === 'confirmed')
                        <button type="button" 
                                @click="openCancelModal = true" 
                                class="w-full py-2.5 rounded-lg border border-[#FF3B3C] text-[#FF3B3C] hover:bg-[#FEF2F2] text-xs font-bold transition-colors mt-2">
                            Request Cancellation →
                        </button>
                    @endif
                </div>

                <!-- Force Majeure Notice if applicable -->
                @if($policy['is_force_majeure'])
                <div class="p-4 rounded-xl bg-[#FEF2F2] border border-[#FECACA] text-sm text-[#991B1B] space-y-1">
                    <div class="font-bold">
                        Marine Safety Advisory Active
                    </div>
                    <p class="text-xs leading-relaxed">
                        A storm signal or high risk marine advisory is active for your dive dates. Free reschedules and full refunds are enabled under camp policy.
                    </p>
                </div>
                @endif
            </div>

            <!-- Help Box -->
            <div class="p-5 sm:p-6 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] text-sm text-[#6E6E73] space-y-2">
                <h4 class="font-bold text-[#1D1D1F] text-sm">Need Special Assistance?</h4>
                <p class="text-xs">For custom requests, contact our coordinators:</p>
                <div class="pt-1 text-sm font-bold text-[#780000]">
                    0927 887 9894
                </div>
                <div class="text-xs">
                    <a href="https://www.facebook.com/Campfreediveph/" target="_blank" rel="noopener noreferrer" class="underline hover:text-[#780000]">Facebook Messenger</a>
                </div>
            </div>

        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- RESCHEDULE MODAL -->
    <!-- ========================================================================= -->
    <div x-show="openRescheduleModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white rounded-xl max-w-lg w-full p-5 sm:p-8 space-y-5 sm:space-y-6 shadow-2xl border border-[#E5E5EA]" @click.outside="openRescheduleModal = false">
            <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-4">
                <div>
                    <h3 class="text-lg font-bold text-[#1D1D1F]">Reschedule Dive Dates</h3>
                    <p class="text-xs text-[#6E6E73]">Pick a new 2D1N date pair.</p>
                </div>
                <button type="button" @click="openRescheduleModal = false" class="text-[#8E8E93] hover:text-[#1D1D1F] font-bold text-lg">✕</button>
            </div>

            <form action="{{ route('manage.reschedule', $booking->booking_number) }}" method="POST" class="space-y-4 text-sm">
                @csrf
                <input type="hidden" name="pin" value="{{ $booking->pin }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-[#1D1D1F] mb-1">New Start Date <span class="text-[#780000]">*</span></label>
                        <input type="date" 
                               name="requested_start_date" 
                               x-model="rescheduleStartDate" 
                               @change="onRescheduleDateChange()"
                               min="{{ date('Y-m-d', strtotime('+3 days')) }}"
                               required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-sm font-medium text-[#1D1D1F] bg-white">
                    </div>
                    <div>
                        <label class="block font-bold text-[#6E6E73] mb-1">New End Date (Auto)</label>
                        <input type="date" 
                               name="requested_end_date" 
                               x-model="rescheduleEndDate" 
                               readonly
                               required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#E5E5EA] bg-[#F2F2F7] text-sm font-medium text-[#6E6E73]">
                    </div>
                </div>

                <!-- Weather Check for Reschedule -->
                <div x-show="rescheduleForecast" x-cloak class="p-3.5 rounded-xl text-xs space-y-1"
                     :style="'background-color: ' + (rescheduleForecast?.bg_color || '#F2F2F7') + '; color: ' + (rescheduleForecast?.text_color || '#1D1D1F')">
                    <div class="font-bold flex items-center justify-between">
                        <span x-text="'Safety: ' + (rescheduleForecast?.title || '')"></span>
                        <span x-text="rescheduleForecast?.is_bookable ? 'Safe' : 'Storm Warning'"></span>
                    </div>
                    <p class="text-xs" x-text="rescheduleForecast?.description"></p>
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-1">Reason for Rescheduling (Optional)</label>
                    <textarea name="reason" rows="2" placeholder="e.g. Work schedule change" class="w-full px-3.5 py-2 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white"></textarea>
                </div>

                <div class="p-3.5 bg-[#FFFBEB] rounded-xl text-xs text-[#92400E] border border-[#FDE68A]">
                    <strong>Note:</strong> On submission, your request status is set to <strong>Pending Approval</strong>. The camp will review coach availability and notify you via email.
                </div>

                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="button" @click="openRescheduleModal = false" class="btn-secondary px-4 py-2 text-sm">Cancel</button>
                    <button type="submit" 
                            :disabled="rescheduleForecast && !rescheduleForecast.is_bookable" 
                            class="btn-primary px-5 py-2 text-sm font-bold shadow-sm">
                        Submit Reschedule Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- CANCELLATION MODAL -->
    <!-- ========================================================================= -->
    <div x-show="openCancelModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white rounded-xl max-w-lg w-full p-5 sm:p-8 space-y-5 sm:space-y-6 shadow-2xl border border-[#E5E5EA]" @click.outside="openCancelModal = false">
            <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-4">
                <div>
                    <h3 class="text-lg font-bold text-[#FF3B3C]">Request Booking Cancellation</h3>
                    <p class="text-xs text-[#6E6E73]">Review your refund calculation according to camp policy.</p>
                </div>
                <button type="button" @click="openCancelModal = false" class="text-[#8E8E93] hover:text-[#1D1D1F] font-bold text-lg">✕</button>
            </div>

            <form action="{{ route('manage.cancel', $booking->booking_number) }}" method="POST" class="space-y-4 text-sm">
                @csrf
                <input type="hidden" name="pin" value="{{ $booking->pin }}">

                <!-- Refund calculation breakdown -->
                <div class="p-4 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-[#6E6E73]">Downpayment Paid:</span>
                        <span class="font-bold text-[#1D1D1F]">₱{{ number_format($booking->downpayment_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#6E6E73]">Days Before Dive Date:</span>
                        <span class="font-bold text-[#1D1D1F]">{{ $policy['days_until_dive'] }} days</span>
                    </div>
                    <div class="flex justify-between border-t border-[#E5E5EA] pt-2 text-sm">
                        <span class="font-bold text-[#1D1D1F]">Calculated Refund Amount:</span>
                        <span class="font-extrabold text-[#780000]">₱{{ number_format($policy['calculated_refund'], 2) }}</span>
                    </div>
                    <p class="text-xs text-[#6E6E73] mt-1">{{ $policy['cancel_message'] }}</p>
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-1">Reason for Cancellation</label>
                    <textarea name="reason" rows="2" placeholder="Please let us know why you need to cancel" class="w-full px-3.5 py-2 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white"></textarea>
                </div>

                <!-- 2-Step Confirmation Checkbox -->
                <div class="p-3.5 bg-[#FEF2F2] border border-[#FECACA] rounded-xl">
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" name="confirm_cancel_ack" required class="w-4 h-4 rounded text-[#FF3B3C] focus:ring-[#FF3B3C] mt-0.5">
                        <span class="text-xs font-bold text-[#991B1B]">
                            I confirm that I want to cancel this booking and understand the refund amount will be reviewed by the camp.
                        </span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="openCancelModal = false" class="btn-secondary px-4 py-2 text-sm">Keep My Booking</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-[#FF3B3C] hover:bg-[#E02E2F] text-white text-sm font-bold shadow-sm transition-colors">
                        Confirm Cancellation Request
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function manageBooking(config) {
    return {
        openRescheduleModal: false,
        openCancelModal: false,
        rescheduleStartDate: '',
        rescheduleEndDate: '',
        rescheduleForecast: null,

        onRescheduleDateChange() {
            if (!this.rescheduleStartDate) return;
            const start = new Date(this.rescheduleStartDate);
            const end = new Date(start);
            end.setDate(start.getDate() + 1);
            this.rescheduleEndDate = end.toISOString().split('T')[0];

            fetch(config.checkWeatherUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken
                },
                body: JSON.stringify({
                    start_date: this.rescheduleStartDate,
                    end_date: this.rescheduleEndDate
                })
            })
            .then(res => res.json())
            .then(data => {
                this.rescheduleForecast = data;
            });
        }
    };
}
</script>
@endpush
