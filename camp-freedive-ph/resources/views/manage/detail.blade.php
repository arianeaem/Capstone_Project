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
        <a href="{{ route('manage.index') }}" class="min-h-[44px] -ml-2 px-2.5 py-2 rounded-xl text-sm font-semibold text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] inline-flex items-center gap-1.5 transition-all cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            <span>Switch to another booking</span>
        </a>
        <div class="text-sm sm:text-sm text-[#636366]">
            Booking Created: {{ $booking->created_at->format('M d, Y') }}
        </div>
    </div>

    @if($booking->status === 'pending_downpayment')
    <!-- Downpayment Required Alert -->
    <div class="mb-6 p-4 sm:p-5 rounded-xl bg-amber-100 text-amber-900 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2 font-bold text-sm sm:text-base text-amber-900">
                <span>Downpayment Required</span>
            </div>
            <p class="text-sm text-[#78350F] leading-relaxed">
                Your reservation has not gone through the system yet because the required reservation downpayment of <strong>₱{{ number_format($booking->downpayment_amount, 2) }}</strong> is unpaid. Please complete your payment via PayMongo to confirm your slots.
            </p>
        </div>
        <form action="{{ route('paymongo.checkout', ['booking' => $booking->id]) }}" method="POST" class="shrink-0">
            @csrf
            <button type="submit" class="btn-primary min-h-[44px] w-full sm:w-auto px-5 py-2.5 text-sm font-bold rounded-xl cursor-pointer transition-all active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000] focus-visible:ring-offset-2">
                Pay Downpayment
            </button>
        </form>
    </div>
    @endif

    <!-- Booking Overview -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-8 mb-6 sm:mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 mb-6">
            <div>
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <h1 class="text-xl sm:text-3xl font-extrabold text-[#1D1D1F] font-mono tracking-wide">
                        Reservation {{ $booking->booking_number }}
                    </h1>
                    <span class="px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full text-sm font-bold {{ $booking->status_badge['bg'] }}">
                        {{ $booking->status_badge['label'] }}
                    </span>
                </div>
                <p class="text-sm text-[#6E6E73] mt-1">Booking overview, scheduled dates, and management actions.</p>
            </div>

            <!-- Security PIN -->
            <div class="bg-[#F8EAEA] rounded-xl p-3 sm:p-3.5 flex sm:flex-col items-center sm:items-end justify-between gap-2 shrink-0">
                <div>
                    <span class="text-xs font-bold text-[#780000] block">Security PIN</span>
                    <span class="text-base sm:text-lg font-mono font-extrabold text-[#780000] tracking-widest">{{ $booking->pin }}</span>
                </div>
                <button type="button" 
                        @click="navigator.clipboard.writeText('{{ $booking->pin }}'); copiedPin = true; setTimeout(() => copiedPin = false, 2000)"
                        aria-label="Copy Security PIN"
                        class="min-h-[34px] px-2.5 py-1.5 rounded-lg bg-white/90 hover:bg-white text-xs font-bold text-[#780000] shadow-2xs flex items-center gap-1.5 transition-all cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]">
                    <img src="{{ asset('icons/icons8-copy-60.png') }}" class="w-4.5 h-4.5 object-contain shrink-0" alt="" aria-hidden="true" x-show="!copiedPin">
                    <svg x-show="copiedPin" x-cloak class="w-4.5 h-4.5 text-emerald-700 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                    <span x-text="copiedPin ? 'Copied' : 'Copy'"></span>
                </button>
            </div>
        </div>

        <!-- Booking Key Information -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 text-sm">
            <div class="p-3.5 sm:p-4 rounded-xl bg-[#F2F2F7] shadow-2xs">
                <span class="text-sm text-[#6E6E73] block mb-1">Lead Booker:</span>
                <strong class="text-sm text-[#1D1D1F] block">{{ $booking->contact_name }}</strong>
                <span class="text-sm text-[#6E6E73] break-all">{{ $booking->contact_email }}</span>
            </div>

            <div class="p-3.5 sm:p-4 rounded-xl bg-[#F2F2F7] shadow-2xs">
                <span class="text-sm text-[#6E6E73] block mb-1">Class Package:</span>
                <strong class="text-sm text-[#1D1D1F] block">{{ $booking->formatted_class_type }}</strong>
                <span class="text-sm text-[#6E6E73]">{{ $booking->participants->count() }} Participant(s)</span>
            </div>

            <div class="p-3.5 sm:p-4 rounded-xl bg-[#F2F2F7] shadow-2xs">
                <span class="text-sm text-[#6E6E73] block mb-1">Trip Dates:</span>
                <strong class="text-sm text-[#1D1D1F] block">{{ $booking->start_date->format('M d, Y') }} - {{ $booking->end_date->format('M d, Y') }}</strong>
                <span class="text-sm text-[#780000] font-semibold">
                    @if($policy['days_until_dive'] > 0)
                        {{ $policy['days_until_dive'] }} days until dive trip
                    @elseif($policy['days_until_dive'] === 0)
                        Dive trip is today
                    @else
                        Completed trip
                    @endif
                </span>
            </div>

            <div class="p-3.5 sm:p-4 rounded-xl bg-[#F2F2F7] shadow-2xs">
                <span class="text-sm text-[#6E6E73] block mb-1">Payment Status:</span>
                @if($booking->status === 'pending_downpayment')
                    <strong class="text-sm text-amber-700 block">₱{{ number_format($booking->downpayment_amount, 2) }} Downpayment Unpaid</strong>
                    <span class="text-sm text-[#D70015] font-bold">Unconfirmed Reservation</span>
                @else
                    <strong class="text-sm text-[#34C759] block">₱{{ number_format($booking->downpayment_amount, 2) }} Downpayment Paid</strong>
                    <span class="text-sm text-[#780000] font-bold">₱{{ number_format($booking->balance_amount, 2) }} balance due at camp</span>
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
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-6 space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h3 class="text-sm sm:text-base font-bold text-[#1D1D1F]">Price Breakdown &amp; Applied Rules</h3>
                </div>

                <div class="space-y-2 text-xs sm:text-sm">
                    <div class="flex flex-wrap sm:flex-nowrap justify-between items-center gap-1 sm:gap-2 text-[#6E6E73]">
                        <span class="shrink-0">Base Class Rate:</span>
                        <span class="font-bold text-[#1D1D1F] whitespace-nowrap">₱{{ number_format($booking->priceAdjustments->first()->base_price ?? 4250, 2) }} <span class="font-normal text-[#636366]">/ person</span></span>
                    </div>

                    <div class="space-y-2 py-2.5 border-y border-[#E5E5EA]/60">
                        @foreach($booking->priceAdjustments as $adj)
                        <div class="flex flex-wrap sm:flex-nowrap justify-between items-start sm:items-center gap-1 sm:gap-3">
                            <div class="flex flex-wrap items-center gap-1.5 min-w-0">
                                <span class="font-medium text-[#1D1D1F]">{{ $adj->rule_name }}</span>
                                <span class="text-[#6E6E73] text-xs">({{ $adj->condition_summary }})</span>
                            </div>
                            <span class="font-bold shrink-0 whitespace-nowrap {{ $adj->adjustment_amount >= 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                                {{ $adj->adjustment_amount >= 0 ? '+' : '−' }}₱{{ number_format(abs($adj->adjustment_amount), 2) }} <span class="font-normal text-xs text-[#636366]">/ person</span>
                            </span>
                        </div>
                        @endforeach
                    </div>

                    <div class="flex flex-wrap sm:flex-nowrap justify-between items-center gap-1 sm:gap-2 font-extrabold text-[#1D1D1F] pt-1">
                        <span class="shrink-0">Final Adjusted Rate:</span>
                        <span class="text-sm sm:text-base font-extrabold text-[#780000] whitespace-nowrap">₱{{ number_format($booking->participants->first()->price_per_person ?? $booking->priceAdjustments->first()->adjusted_price, 2) }} <span class="font-normal text-xs text-[#636366]">/ person</span></span>
                    </div>
                </div>
            </div>
            @endif

            <!-- Participant List -->
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-8">
                <h3 class="text-base font-bold text-[#1D1D1F] mb-4">Divers in this Booking ({{ $booking->participants->count() }})</h3>
                <div class="divide-y divide-[#E5E5EA]">
                    @foreach($booking->participants as $p)
                    <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-sm">
                        <div>
                            <span class="font-bold text-[#1D1D1F]">{{ $p->name }}</span>
                            <span class="text-sm text-[#6E6E73] ml-1.5">Age {{ $p->age }}</span>
                            <div class="text-sm text-[#6E6E73] mt-0.5">
                                <strong>Medical Notes:</strong> {{ $p->health_condition ?: 'None declared' }}
                            </div>
                        </div>
                        <div class="text-left sm:text-right">
                            <span class="px-2.5 py-0.5 rounded text-sm font-semibold bg-[#F2F2F7] text-[#1D1D1F]">
                                {{ ucfirst(str_replace('_', ' ', $p->swimmer_status ?: 'Swimmer')) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Logistics and Add-ons -->
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-8">
                <h3 class="text-base font-bold text-[#1D1D1F] mb-4">Transportation & Add-ons</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div class="p-4 rounded-xl bg-[#F2F2F7] shadow-2xs">
                        <span class="text-sm text-[#6E6E73] block mb-1">Transportation:</span>
                        <strong class="text-sm text-[#1D1D1F] block">
                            {{ $booking->pickup_option === 'carpool' ? 'Manila Carpool Service' : 'Own Transportation' }}
                        </strong>
                        @if($booking->pickup_location)
                            <span class="text-sm text-[#780000] font-medium block mt-1">Pickup Hub: {{ $booking->pickup_location }}</span>
                        @endif
                    </div>

                    <div class="p-4 rounded-xl bg-[#F2F2F7] shadow-2xs">
                        <span class="text-sm text-[#6E6E73] block mb-1">Boat Dive:</span>
                        <strong class="text-sm text-[#1D1D1F] block">
                            {{ $booking->boat_dive ? 'Included (+₱600 / person)' : 'Not Included' }}
                        </strong>
                        <span class="text-sm text-[#6E6E73] block mt-1">Mabini LGU pass included</span>
                    </div>
                </div>
            </div>

            <!-- Request History -->
            @if($booking->rescheduleRequests->isNotEmpty() || $booking->cancellationRequests->isNotEmpty())
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-8 space-y-4">
                <h3 class="text-base font-bold text-[#1D1D1F]">Request History</h3>

                @foreach($booking->rescheduleRequests as $req)
                <div class="p-4 rounded-xl bg-[#FFFBEB] text-sm text-[#92400E] space-y-1 shadow-2xs">
                    <div class="flex items-center justify-between font-bold">
                        <span>Reschedule Request ({{ $req->created_at->format('M d, Y') }})</span>
                        <span class="uppercase tracking-wider px-2 py-0.5 rounded bg-white text-sm font-bold">{{ $req->status }}</span>
                    </div>
                    <p>Requested Move: {{ $req->requested_start_date->format('M d, Y') }} - {{ $req->requested_end_date->format('M d, Y') }}</p>
                    @if($req->reason)
                        <p class="text-sm text-[#78350F]">Reason: {{ $req->reason }}</p>
                    @endif
                </div>
                @endforeach

                @foreach($booking->cancellationRequests as $cReq)
                <div class="p-4 rounded-xl bg-[#FEF2F2] text-sm text-[#991B1B] space-y-1 shadow-2xs">
                    <div class="flex items-center justify-between font-bold">
                        <span>Cancellation Request ({{ $cReq->created_at->format('M d, Y') }})</span>
                        <span class="uppercase tracking-wider px-2 py-0.5 rounded bg-white text-sm font-bold">{{ $cReq->status }}</span>
                    </div>
                    <p>Calculated Refund: ₱{{ number_format($cReq->calculated_refund_amount, 2) }}</p>
                    @if($cReq->reason)
                        <p class="text-sm">Reason: {{ $cReq->reason }}</p>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

        </div>

        <!-- Policy Engine and Actions -->
        <div class="space-y-6 text-sm">

            <!-- Policy Status -->
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-8 space-y-5">
                <div>
                    <span class="text-sm font-bold uppercase tracking-wider text-[#780000]">Cancellation & Rescheduling Policy</span>
                    <p class="text-sm text-[#6E6E73] mt-1">Policy based on days before your dive date.</p>
                </div>

                @if($booking->status === 'pending_downpayment')
                <!-- Unpaid Downpayment Notice -->
                <div class="p-4 rounded-xl bg-amber-100 text-amber-900 space-y-3 shadow-2xs">
                    <div class="font-bold text-sm text-amber-900">
                        Downpayment Required
                    </div>
                    <p class="text-sm text-[#78350F] leading-relaxed">
                        Self-service rescheduling and cancellations are enabled once your required downpayment of <strong>₱{{ number_format($booking->downpayment_amount, 2) }}</strong> is paid.
                    </p>
                    <form action="{{ route('paymongo.checkout', ['booking' => $booking->id]) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-primary min-h-[44px] w-full py-2.5 px-4 text-sm font-bold rounded-xl cursor-pointer transition-all active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000] focus-visible:ring-offset-2">
                            Pay ₱{{ number_format($booking->downpayment_amount, 2) }}
                        </button>
                    </form>
                </div>
                @else
                <!-- Reschedule Status -->
                <div class="p-4 rounded-xl bg-[#F2F2F7] space-y-2 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-[#1D1D1F]">Reschedule</span>
                        <span class="text-sm font-bold px-2 py-0.5 rounded {{ $policy['reschedule_allowed'] ? 'bg-[#ECFDF5] text-emerald-800' : 'bg-[#E5E5EA] text-[#6E6E73]' }}">
                            {{ $policy['reschedule_allowed'] ? 'Allowed' : 'Closed' }}
                        </span>
                    </div>
                    <p class="text-sm text-[#6E6E73] leading-relaxed">{{ $policy['reschedule_message'] }}</p>
                    
                    @if($policy['reschedule_allowed'])
                        <button type="button" 
                                @click="openRescheduleModal = true" 
                                class="btn-secondary min-h-[44px] w-full py-2.5 px-4 text-sm font-bold rounded-xl cursor-pointer transition-all active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000] focus-visible:ring-offset-2 mt-1">
                            Reschedule Booking Date
                        </button>
                    @endif
                </div>

                <!-- Cancellation Status -->
                <div class="p-4 rounded-xl bg-[#F2F2F7] space-y-2 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-[#1D1D1F]">Cancel / Refund</span>
                        <span class="text-sm font-bold px-2 py-0.5 rounded {{ $policy['cancel_allowed'] ? 'bg-[#FEF3C7] text-amber-900' : 'bg-[#E5E5EA] text-[#6E6E73]' }}">
                            {{ $policy['cancel_allowed'] ? 'Eligible' : 'Non-Refundable' }}
                        </span>
                    </div>
                    <p class="text-sm text-[#6E6E73] leading-relaxed">{{ $policy['cancel_message'] }}</p>

                    @if($policy['cancel_allowed'] || $booking->status === 'confirmed')
                        <button type="button" 
                                @click="openCancelModal = true" 
                                class="btn-secondary min-h-[44px] w-full py-2.5 px-4 text-sm font-bold rounded-xl cursor-pointer transition-all active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000] focus-visible:ring-offset-2 mt-1">
                            Request Cancellation
                        </button>
                    @endif
                </div>
                @endif

                <!-- Marine Safety Advisory Notice -->
                @if($policy['is_force_majeure'])
                <div class="p-4 rounded-xl bg-sky-50 text-sky-950 space-y-1.5 shadow-2xs">
                    <div class="font-bold text-sm text-sky-950 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-sky-700 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        <span>Advisory: Weather Force Majeure Active</span>
                    </div>
                    <p class="text-sm text-sky-900 leading-relaxed">
                        PAGASA gale warnings or severe sea surges have triggered camp-wide safety protocols. All rescheduling restrictions are waived. Full refunds are eligible upon cancellation.
                    </p>
                </div>
                @endif
            </div>

            <!-- Coordinator Contact -->
            <div class="p-5 sm:p-6 rounded-xl bg-white text-sm text-[#6E6E73] space-y-3 shadow-2xs border border-[#E5E5EA]">
                <h4 class="font-bold text-[#1D1D1F] text-sm sm:text-base">Need Special Assistance?</h4>
                <p class="text-xs sm:text-sm text-[#6E6E73]">You may contact us through any of our support channels:</p>
                <div class="space-y-2.5 text-xs sm:text-sm text-[#1D1D1F] pt-1">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[#6E6E73] font-medium shrink-0">Facebook:</span>
                        <a href="https://www.facebook.com/Campfreediveph/" target="_blank" rel="noopener noreferrer" aria-label="Facebook (opens in a new tab)" class="text-[#780000] font-bold hover:underline inline-flex items-center gap-1.5">
                            <span>@Campfreediveph</span>
                            <img src="{{ asset('icons/icons8-linking-60.png') }}" class="w-4.5 h-4.5 object-contain shrink-0" alt="" aria-hidden="true">
                        </a>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[#6E6E73] font-medium shrink-0">Instagram:</span>
                        <a href="https://www.instagram.com/campfreediveph/" target="_blank" rel="noopener noreferrer" aria-label="Instagram (opens in a new tab)" class="text-[#780000] font-bold hover:underline inline-flex items-center gap-1.5">
                            <span>@campfreediveph</span>
                            <img src="{{ asset('icons/icons8-linking-60.png') }}" class="w-4.5 h-4.5 object-contain shrink-0" alt="" aria-hidden="true">
                        </a>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[#6E6E73] font-medium shrink-0">Email:</span>
                        <a href="mailto:campfreediveph@gmail.com" aria-label="Email Camp FreedivePH" class="text-[#780000] font-bold hover:underline">campfreediveph@gmail.com</a>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[#6E6E73] font-medium shrink-0">Phone:</span>
                        <a href="tel:+639278879894" aria-label="Call Camp FreedivePH" class="text-[#780000] font-bold hover:underline whitespace-nowrap">+63 927 887 9894</a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Reschedule Modal -->
    <div x-show="openRescheduleModal" 
         x-cloak 
         role="dialog" 
         aria-modal="true" 
         aria-labelledby="reschedule-modal-title" 
         @keydown.escape.window="openRescheduleModal = false" 
         class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white rounded-xl max-w-lg w-full p-5 sm:p-8 space-y-5 sm:space-y-6 shadow-2xl border border-[#E5E5EA]" @click.outside="openRescheduleModal = false">
            <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-4">
                <div>
                    <h3 id="reschedule-modal-title" class="text-lg font-bold text-[#1D1D1F]">Reschedule Dive Dates</h3>
                    <p class="text-sm text-[#6E6E73]">Pick a new 2D1N date pair.</p>
                </div>
                <button type="button" 
                        @click="openRescheduleModal = false" 
                        aria-label="Close reschedule modal" 
                        class="w-11 h-11 min-h-[44px] min-w-[44px] -mr-2 rounded-full flex items-center justify-center text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] transition-all cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form action="{{ route('manage.reschedule', $booking->booking_number) }}" method="POST" class="space-y-4 text-sm">
                @csrf
                <input type="hidden" name="pin" value="{{ $booking->pin }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-[#1D1D1F] mb-2">New Start Date <span class="text-[#D70015]">*</span></label>
                        <input type="date" 
                               name="requested_start_date" 
                               x-model="rescheduleStartDate" 
                               @change="onRescheduleDateChange()"
                               min="{{ date('Y-m-d', strtotime('+3 days')) }}"
                               required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] focus:border-[#780000] focus:ring-2 focus:ring-[#780000]/20 focus:outline-none text-sm font-medium text-[#1D1D1F] bg-white transition-colors">
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

                <!-- Weather Loading State -->
                <div x-show="weatherLoading" x-cloak class="p-3.5 rounded-xl bg-[#F2F2F7] text-xs sm:text-sm text-[#1D1D1F] flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-[#00C3D0] shrink-0" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span class="font-medium text-[#1D1D1F]">Checking sea safety &amp; weather conditions for selected dates...</span>
                </div>

                <!-- Weather Forecast Check -->
                <div x-show="rescheduleForecast && !weatherLoading" x-cloak class="p-3.5 rounded-xl text-sm space-y-1.5"
                     :style="'background-color: ' + (rescheduleForecast?.bg_color || '#F2F2F7') + '; color: ' + (rescheduleForecast?.text_color || '#1D1D1F')">
                    <div class="font-bold flex items-center justify-between">
                        <span x-text="'Safety: ' + (rescheduleForecast?.title || '')"></span>
                        <span x-text="rescheduleForecast?.is_bookable ? 'Safe' : 'Storm Warning'"></span>
                    </div>
                    <template x-if="rescheduleForecast?.confidence === 'low' || rescheduleForecast?.confidence_advisory">
                        <div class="p-2 rounded-lg bg-amber-100 text-amber-900 font-semibold text-xs flex items-center gap-1.5 border border-amber-300">
                            <svg class="w-4 h-4 text-amber-700 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                            <span x-text="rescheduleForecast?.confidence_advisory || 'Confidence is low this far out, recheck in 2 days.'"></span>
                        </div>
                    </template>
                    <p class="text-sm" x-text="rescheduleForecast?.description"></p>
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-2">Reason for Rescheduling (Optional)</label>
                    <textarea name="reason" rows="2" placeholder="e.g. Work schedule change" class="w-full px-3.5 py-2 rounded-xl border border-[#D1D1D6] focus:border-[#780000] focus:ring-2 focus:ring-[#780000]/20 focus:outline-none text-sm text-[#1D1D1F] bg-white transition-colors"></textarea>
                </div>

                <div class="p-3.5 bg-[#FFFBEB] rounded-xl text-sm text-[#92400E]">
                    <strong>Note:</strong> On submission, your request status is set to <strong>Pending Approval</strong>. The camp will review coach availability and notify you via email.
                </div>

                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="button" @click="openRescheduleModal = false" class="btn-secondary min-h-[44px] px-4 py-2.5 text-sm font-semibold rounded-xl cursor-pointer transition-all active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]">Cancel</button>
                    <button type="submit" 
                            :disabled="weatherLoading || (rescheduleForecast && !rescheduleForecast.is_bookable)" 
                            class="btn-primary min-h-[44px] px-5 py-2.5 text-sm font-bold rounded-xl cursor-pointer transition-all active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000] focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        Submit Reschedule Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cancellation Modal -->
    <div x-show="openCancelModal" 
         x-cloak 
         role="dialog" 
         aria-modal="true" 
         aria-labelledby="cancel-modal-title" 
         @keydown.escape.window="openCancelModal = false" 
         class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white rounded-xl max-w-lg w-full p-5 sm:p-8 space-y-5 sm:space-y-6 shadow-2xl border border-[#E5E5EA]" @click.outside="openCancelModal = false">
            <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-4">
                <div>
                    <h3 id="cancel-modal-title" class="text-lg font-bold text-[#1D1D1F]">Request Booking Cancellation</h3>
                    <p class="text-sm text-[#6E6E73]">Review your refund calculation according to camp policy.</p>
                </div>
                <button type="button" 
                        @click="openCancelModal = false" 
                        aria-label="Close cancellation modal" 
                        class="w-11 h-11 min-h-[44px] min-w-[44px] -mr-2 rounded-full flex items-center justify-center text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] transition-all cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form action="{{ route('manage.cancel', $booking->booking_number) }}" method="POST" class="space-y-4 text-sm">
                @csrf
                <input type="hidden" name="pin" value="{{ $booking->pin }}">

                <!-- Refund Calculation Breakdown -->
                <div class="p-4 rounded-xl bg-[#F2F2F7] space-y-2 text-sm shadow-2xs">
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
                    <p class="text-sm text-[#6E6E73] mt-1">{{ $policy['cancel_message'] }}</p>
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] text-sm mb-1.5">Reason for Cancellation</label>
                    <textarea name="reason" rows="2" placeholder="Please let us know why you need to cancel" class="w-full px-3.5 py-2 rounded-xl border border-[#D1D1D6] focus:border-[#780000] focus:ring-2 focus:ring-[#780000]/20 focus:outline-none text-sm text-[#1D1D1F] bg-white transition-colors"></textarea>
                </div>

                <!-- Cancellation Confirmation Checkbox -->
                <div class="pt-1">
                    <label tabindex="0"
                           role="checkbox"
                           aria-checked="false"
                           @keydown.space.prevent="$refs.cancelCheck.click()" 
                           @keydown.enter.prevent="$refs.cancelCheck.click()" 
                           class="flex items-start gap-2.5 cursor-pointer p-1 rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000] focus-visible:ring-offset-2 select-none">
                        <input type="checkbox" 
                               x-ref="cancelCheck" 
                               name="confirm_cancel_ack" 
                               required 
                               class="w-4 h-4 rounded text-[#780000] focus:ring-[#780000] mt-0.5 shrink-0">
                        <span class="text-sm text-[#1D1D1F] leading-relaxed">
                            I confirm that I want to cancel this booking and understand the refund amount will be reviewed by the camp. <span class="text-[#D70015]">*</span>
                        </span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="openCancelModal = false" class="btn-secondary min-h-[44px] px-4 py-2.5 text-sm font-semibold rounded-xl cursor-pointer transition-all active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]">Keep My Booking</button>
                    <button type="submit" class="btn-danger min-h-[44px] px-5 py-2.5 text-sm font-bold rounded-xl cursor-pointer transition-all active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#D70015] focus-visible:ring-offset-2">
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
        weatherLoading: false,
        copiedPin: false,

        onRescheduleDateChange() {
            if (!this.rescheduleStartDate) return;
            const start = new Date(this.rescheduleStartDate);
            const end = new Date(start);
            end.setDate(start.getDate() + 1);
            this.rescheduleEndDate = end.toISOString().split('T')[0];

            this.weatherLoading = true;
            this.rescheduleForecast = null;

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
            })
            .catch(err => {
                console.error('Weather check failed:', err);
            })
            .finally(() => {
                this.weatherLoading = false;
            });
        }
    };
}
</script>
@endpush
