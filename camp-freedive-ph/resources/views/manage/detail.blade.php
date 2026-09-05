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

    <!-- Back Navigation -->
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('manage.index') }}" class="text-xs sm:text-sm font-semibold text-[#6E6E73] hover:text-[#1D1D1F] flex items-center gap-1.5">
            ← Switch to another bookings
        </a>
        <div class="text-xs sm:text-sm text-[#8E8E93]">
            Booking Created: {{ $booking->created_at->format('M d, Y') }}
        </div>
    </div>

    @if($booking->status === 'pending_downpayment')
    <!-- Downpayment Required Alert -->
    <div class="mb-6 p-4 sm:p-5 rounded-xl bg-amber-50 border border-amber-300 text-amber-900 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
        <div class="space-y-1">
            <div class="flex items-center gap-2 font-bold text-sm sm:text-base text-amber-900">
                <svg class="w-5 h-5 text-amber-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Downpayment Required</span>
            </div>
            <p class="text-xs text-amber-800 leading-relaxed">
                Your reservation has not gone through the system yet because the required reservation downpayment of <strong>₱{{ number_format($booking->downpayment_amount, 2) }}</strong> is unpaid. Please complete your payment via PayMongo to confirm your slots.
            </p>
        </div>
        <form action="{{ route('paymongo.checkout', ['booking' => $booking->id]) }}" method="POST" class="shrink-0">
            @csrf
            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-[#780000] text-white font-bold text-xs hover:bg-[#5a0000] transition-colors shadow-2xs cursor-pointer">
                Pay Downpayment
            </button>
        </form>
    </div>
    @endif

    <!-- Booking Overview -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-8 shadow-sm mb-6 sm:mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 mb-6">
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

            <!-- Security PIN -->
            <div class="bg-[#F8EAEA] border border-[#780000]/20 rounded-xl p-3 sm:p-3.5 sm:text-right shrink-0">
                <span class="text-xs font-bold uppercase tracking-wider text-[#780000] block">Security PIN</span>
                <span class="text-base sm:text-lg font-mono font-extrabold text-[#780000] tracking-widest">{{ $booking->pin }}</span>
            </div>
        </div>

        <!-- Booking Key Information -->
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
                <span class="text-xs text-[#6E6E73] block mb-1">Trip Dates:</span>
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
                @if($booking->status === 'pending_downpayment')
                    <strong class="text-sm text-amber-700 block">₱{{ number_format($booking->downpayment_amount, 2) }} Downpayment Unpaid</strong>
                    <span class="text-xs text-rose-600 font-bold">Unconfirmed Reservation</span>
                @else
                    <strong class="text-sm text-[#34C759] block">₱{{ number_format($booking->downpayment_amount, 2) }} Downpayment Paid</strong>
                    <span class="text-xs text-[#780000] font-bold">₱{{ number_format($booking->balance_amount, 2) }} balance due at camp</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Booking Content and Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">

        <!-- Booking Details and Logistics -->
        <div class="lg:col-span-2 space-y-6 sm:space-y-8">
            
            <!-- Dynamic Pricing and Rate Breakdown -->
            @if($booking->priceAdjustments && $booking->priceAdjustments->count() > 0)
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-6 shadow-sm space-y-3">
                <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                    <h3 class="text-base font-bold text-[#1D1D1F]">Price Breakdown & Applied Rules</h3>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-[#F8EAEA] text-[#780000]">
                        Dynamic Pricing Applied
                    </span>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between items-center text-[#6E6E73]">
                        <span>Base Class Rate:</span>
                        <span class="font-bold text-[#1D1D1F]">₱{{ number_format($booking->priceAdjustments->first()->base_price ?? 4250, 2) }} / person</span>
                    </div>

                    <div class="space-y-1.5 py-2 border-y border-dashed border-[#E5E5EA]">
                        @foreach($booking->priceAdjustments as $adj)
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full {{ $adj->adjustment_amount >= 0 ? 'bg-rose-500' : 'bg-emerald-500' }}"></span>
                                <span class="font-medium text-[#1D1D1F]">{{ $adj->rule_name }}</span>
                                <span class="text-[10px] text-[#6E6E73]">({{ $adj->condition_summary }})</span>
                            </div>
                            <span class="font-bold {{ $adj->adjustment_amount >= 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                                {{ $adj->adjustment_amount >= 0 ? '+' : '−' }}₱{{ number_format(abs($adj->adjustment_amount), 2) }} / person
                            </span>
                        </div>
                        @endforeach
                    </div>

                    <div class="flex justify-between items-center text-sm font-extrabold text-[#1D1D1F] pt-1">
                        <span>Final Adjusted Rate:</span>
                        <span>₱{{ number_format($booking->participants->first()->price_per_person ?? $booking->priceAdjustments->first()->adjusted_price, 2) }} / person</span>
                    </div>
                </div>
            </div>
            @endif

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

            <!-- Logistics and Add-ons -->
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
                            {{ $booking->boat_dive ? 'Included (+₱600 / person)' : 'Not Included' }}
                        </strong>
                        <span class="text-xs text-[#6E6E73] block mt-1">Mabini LGU pass included</span>
                    </div>
                </div>
            </div>

            <!-- Request History -->
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

        <!-- Policy Engine and Actions -->
        <div class="space-y-6 text-sm">

            <!-- Policy Status -->
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-8 shadow-sm space-y-5">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-[#780000]">Cancellation & Rescheduling Policy</span>
                    <p class="text-xs text-[#6E6E73] mt-1">Policy based on days before your dive date.</p>
                </div>

                @if($booking->status === 'pending_downpayment')
                <!-- Unpaid Downpayment Notice -->
                <div class="p-4 rounded-xl border border-amber-300 bg-amber-50 text-amber-900 space-y-3">
                    <div class="font-bold text-sm text-amber-900">
                        Downpayment Required
                    </div>
                    <p class="text-xs text-amber-800 leading-relaxed">
                        Self-service rescheduling and cancellations are enabled once your required downpayment of <strong>₱{{ number_format($booking->downpayment_amount, 2) }}</strong> is paid.
                    </p>
                    <form action="{{ route('paymongo.checkout', ['booking' => $booking->id]) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-primary w-full py-2.5 text-xs font-bold shadow-2xs cursor-pointer">
                            Pay ₱{{ number_format($booking->downpayment_amount, 2) }}
                        </button>
                    </form>
                </div>
                @else
                <!-- Reschedule Status -->
                <div class="p-4 rounded-xl border border-[#E5E5EA] bg-[#FAFAFC] space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-[#1D1D1F]">Reschedule</span>
                        <span class="text-xs font-bold px-2 py-0.5 rounded {{ $policy['reschedule_allowed'] ? 'bg-[#ECFDF5] text-emerald-800 border border-emerald-200' : 'bg-[#F2F2F7] text-[#6E6E73] border border-[#E5E5EA]' }}">
                            {{ $policy['reschedule_allowed'] ? 'Allowed' : 'Closed' }}
                        </span>
                    </div>
                    <p class="text-xs text-[#6E6E73] leading-relaxed">{{ $policy['reschedule_message'] }}</p>
                    
                    @if($policy['reschedule_allowed'])
                        <button type="button" 
                                @click="openRescheduleModal = true" 
                                class="btn-primary w-full py-2.5 text-xs font-bold mt-1">
                            Reschedule Booking Date
                        </button>
                    @endif
                </div>

                <!-- Cancellation Status -->
                <div class="p-4 rounded-xl border border-[#E5E5EA] bg-[#FAFAFC] space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-[#1D1D1F]">Cancel / Refund</span>
                        <span class="text-xs font-bold px-2 py-0.5 rounded {{ $policy['cancel_allowed'] ? 'bg-[#FEF3C7] text-amber-900 border border-amber-200' : 'bg-[#F2F2F7] text-[#6E6E73] border border-[#E5E5EA]' }}">
                            {{ $policy['cancel_allowed'] ? 'Eligible' : 'Non-Refundable' }}
                        </span>
                    </div>
                    <p class="text-xs text-[#6E6E73] leading-relaxed">{{ $policy['cancel_message'] }}</p>

                    @if($policy['cancel_allowed'] || $booking->status === 'confirmed')
                        <button type="button" 
                                @click="openCancelModal = true" 
                                class="btn-secondary w-full py-2.5 text-xs font-bold mt-1">
                            Request Cancellation
                        </button>
                    @endif
                </div>
                @endif

                <!-- Marine Safety Advisory Notice -->
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

            <!-- Coordinator Contact -->
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

    <!-- Reschedule Modal -->
    <div x-show="openRescheduleModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white rounded-xl max-w-lg w-full p-5 sm:p-8 space-y-5 sm:space-y-6 shadow-2xl border border-[#E5E5EA]" @click.outside="openRescheduleModal = false">
            <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-4">
                <div>
                    <h3 class="text-lg font-bold text-[#1D1D1F]">Reschedules</h3>
                    <p class="text-xs text-[#6E6E73]">Pick a new 2D1N date pair.</p>
                </div>
                <button type="button" @click="openRescheduleModal = false" class="text-[#8E8E93] hover:text-[#1D1D1F] font-bold text-lg">✕</button>
            </div>

            <form action="{{ route('manage.reschedule', $booking->booking_number) }}" method="POST" class="space-y-4 text-sm">
                @csrf
                <input type="hidden" name="pin" value="{{ $booking->pin }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-[#1D1D1F] mb-2">New Start Date <span class="text-[#780000]">*</span></label>
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

                <!-- Weather Forecast Check -->
                <div x-show="rescheduleForecast" x-cloak class="p-3.5 rounded-xl text-xs space-y-1"
                     :style="'background-color: ' + (rescheduleForecast?.bg_color || '#F2F2F7') + '; color: ' + (rescheduleForecast?.text_color || '#1D1D1F')">
                    <div class="font-bold flex items-center justify-between">
                        <span x-text="'Safety: ' + (rescheduleForecast?.title || '')"></span>
                        <span x-text="rescheduleForecast?.is_bookable ? 'Safe' : 'Storm Warning'"></span>
                    </div>
                    <p class="text-xs" x-text="rescheduleForecast?.description"></p>
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-2">Reason for Rescheduling (Optional)</label>
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

    <!-- Cancellation Modal -->
    <div x-show="openCancelModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white rounded-xl max-w-lg w-full p-5 sm:p-8 space-y-5 sm:space-y-6 shadow-2xl border border-[#E5E5EA]" @click.outside="openCancelModal = false">
            <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-4">
                <div>
                    <h3 class="text-lg font-bold text-[#1D1D1F]">Request Booking Cancellation</h3>
                    <p class="text-xs text-[#6E6E73]">Review your refund calculation according to camp policy.</p>
                </div>
                <button type="button" @click="openCancelModal = false" class="text-[#8E8E93] hover:text-[#1D1D1F] font-bold text-lg">✕</button>
            </div>

            <form action="{{ route('manage.cancel', $booking->booking_number) }}" method="POST" class="space-y-4 text-sm">
                @csrf
                <input type="hidden" name="pin" value="{{ $booking->pin }}">

                <!-- Refund Calculation Breakdown -->
                <div class="p-4 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-[#6E6E73]">Downpayment Paid:</span>
                        <span class="font-bold text-[#1D1D1F]">₱{{ number_format($booking->downpayment_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#6E6E73]">Days Before Dive Date:</span>
                        <span class="font-bold text-[#1D1D1F]">{{ $policy['days_until_dive'] }} days</span>
                    </div>
                    <div class="flex justify-between border-t border-[#E5E5EA] pt-2">
                        <span class="font-bold text-[#1D1D1F]">Calculated Refund Amount:</span>
                        <span class="font-extrabold text-[#780000]">₱{{ number_format($policy['calculated_refund'], 2) }}</span>
                    </div>
                    <p class="text-xs text-[#6E6E73] mt-1">{{ $policy['cancel_message'] }}</p>
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] text-xs mb-1.5">Reason for Cancellation</label>
                    <textarea name="reason" rows="2" placeholder="Please let us know why you need to cancel" class="w-full px-3.5 py-2 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white"></textarea>
                </div>

                <!-- Cancellation Confirmation Checkbox -->
                <div class="pt-1">
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" name="confirm_cancel_ack" required class="w-4 h-4 rounded text-[#780000] focus:ring-[#780000] mt-0.5">
                        <span class="text-xs text-[#1D1D1F]">
                            I confirm that I want to cancel this booking and understand the refund amount will be reviewed by the camp.
                        </span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="openCancelModal = false" class="btn-secondary px-4 py-2 text-sm">Keep My Booking</button>
                    <button type="submit" class="btn-primary px-5 py-2 text-sm font-bold shadow-2xs">
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
