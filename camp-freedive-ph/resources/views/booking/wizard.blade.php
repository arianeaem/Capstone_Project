@extends('layouts.app')

@section('title', 'Book Camp | Camp FreedivePH')
@section('meta_description', 'Book a 2D1N freediving camp in Mabini, Batangas.')

@section('content')
<div class="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8 py-6 sm:py-10 text-sm" 
     x-data="bookingWizard({
         initialClass: '{{ $selectedClass }}',
         pickupPoints: {{ json_encode($pickupPoints) }},
         csrfToken: '{{ csrf_token() }}',
         checkWeatherUrl: '{{ route('api.weather.check') }}',
         pricingQuoteUrl: '{{ route('api.pricing.quote') }}',
         storeBookingUrl: '{{ route('booking.store') }}'
     })"
     x-init="initWizard()">

    <!-- Top Stepper Header -->
    <div class="mb-6 sm:mb-10">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h1 class="text-xl sm:text-3xl font-extrabold text-[#1D1D1F]" x-text="stepTitles[currentStep - 1]"></h1>
            </div>
            <div class="text-right shrink-0">
                <span class="text-xs sm:text-sm text-[#6E6E73] font-semibold">Step</span>
                <div class="text-xl sm:text-2xl font-extrabold text-[#780000]">
                    <span x-text="currentStep"></span> <span class="text-xs sm:text-sm text-[#8E8E93] font-normal">/ 5</span>
                </div>
            </div>
        </div>

        <!-- Stepper Progress Bar (5 Steps) -->
        <div class="w-full bg-[#E5E5EA] h-2.5 rounded-full overflow-hidden">
            <div class="bg-gradient-to-r from-[#780000] to-[#00C3D0] h-full transition-all duration-300 rounded-full"
                 :style="'width: ' + ((currentStep / 5) * 100) + '%'"></div>
        </div>
    </div>

    <!-- Error Alert Banner -->
    <div x-show="errorMessage" x-cloak class="mb-6 p-3.5 sm:p-4 rounded-xl bg-[#FEF2F2] text-[#991B1B] text-sm flex items-start justify-between gap-3 shadow-2xs">
        <div class="flex items-center gap-2">
            <span x-text="errorMessage"></span>
        </div>
        <button @click="errorMessage = ''" class="text-[#991B1B] font-bold text-sm">✕</button>
    </div>

    <!-- Draft Restored Notification Banner -->
    <div x-show="draftRestored" x-cloak class="mb-6 p-3.5 sm:p-4 rounded-xl bg-[#F0FDF4] text-[#166534] text-xs sm:text-sm flex items-center justify-between gap-3 shadow-2xs">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-[#16A34A] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
            <span>Your saved booking progress has been automatically restored.</span>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <button type="button" @click="resetForm()" class="font-bold underline text-[#15803D] hover:text-[#166534] text-xs">
                Clear & Start Over
            </button>
            <button type="button" @click="draftRestored = false" class="text-[#166534] font-bold text-sm">✕</button>
        </div>
    </div>

    <!-- MAIN FORM CONTAINER -->
    <div class="bg-white rounded-2xl border border-[#E5E5EA] shadow-sm p-4 sm:p-8 lg:p-10 relative text-sm">

        <!-- ========================================================================= -->
        <!-- STEP 1: SELECT CLASS -->
        <!-- ========================================================================= -->
        <div x-show="currentStep === 1" x-cloak class="space-y-6">
            <div class="border-b border-[#E5E5EA] pb-4">
                <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F]">Choose Your Freediving Class</h2>
                <p class="text-xs sm:text-sm text-[#6E6E73] mt-1">Select the course or dive experience you want to join. Beginners and non-swimmers are welcome!</p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:gap-6">
                <!-- Discovery Card -->
                <div @click="form.class_type = 'discovery'" 
                     class="p-4 sm:p-6 rounded-xl border-1 transition-all cursor-pointer flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                     :class="form.class_type === 'discovery' ? 'border-[#780000] bg-[#F8EAEA]/30 ring-1 ring-[#780000]' : 'border-[#E5E5EA] hover:border-[#D1D1D6]'">
                    <div class="flex items-start gap-3 sm:gap-4">
                        <div class="w-5 h-5 sm:w-6 sm:h-6 rounded-full border-1 flex items-center justify-center shrink-0 mt-0.5"
                             :class="form.class_type === 'discovery' ? 'border-[#780000] bg-[#780000]' : 'border-[#D1D1D6]'">
                            <span x-show="form.class_type === 'discovery'" class="w-2 h-2 rounded-full bg-white"></span>
                        </div>
                        <div>
                            <div class="inline-block px-2 py-0.5 rounded text-xs font-bold uppercase bg-[#780000] text-white mb-1">BEGINNER CLASS</div>
                            <h3 class="text-lg sm:text-xl font-bold text-[#1D1D1F]">Discovery</h3>
                            <p class="text-sm text-[#6E6E73] mt-1">Open to solo joiners and non-swimmers. Includes theory, pool session, and 2 open water dive sessions.</p>
                        </div>
                    </div>
                    <div class="text-left sm:text-right shrink-0 pl-8 sm:pl-0">
                        <span class="text-xl sm:text-2xl font-extrabold text-[#780000]">4,250 php</span>
                        <span class="text-xs sm:text-sm text-[#6E6E73] block">/ person</span>
                    </div>
                </div>

                <!-- Fundive Card -->
                <div @click="form.class_type = 'fundive'" 
                     class="p-4 sm:p-6 rounded-xl border-1 transition-all cursor-pointer flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                     :class="form.class_type === 'fundive' ? 'border-[#780000] bg-[#F8EAEA]/30 ring-1 ring-[#780000]' : 'border-[#E5E5EA] hover:border-[#D1D1D6]'">
                    <div class="flex items-start gap-3 sm:gap-4">
                        <div class="w-5 h-5 sm:w-6 sm:h-6 rounded-full border-1 flex items-center justify-center shrink-0 mt-0.5"
                             :class="form.class_type === 'fundive' ? 'border-[#780000] bg-[#780000]' : 'border-[#D1D1D6]'">
                            <span x-show="form.class_type === 'fundive'" class="w-2 h-2 rounded-full bg-white"></span>
                        </div>
                        <div>
                            <div class="inline-block px-2 py-0.5 rounded text-xs font-bold uppercase bg-[#00C3D0] text-[#004D54] mb-1">PREREQUISITE: DISCOVERY CLASS</div>
                            <h3 class="text-lg sm:text-xl font-bold text-[#1D1D1F]">Fundive</h3>
                            <p class="text-sm text-[#6E6E73] mt-1">2 open water dives, pool session, 2D1N accommodation, safety coach fee, photos and videos.</p>
                        </div>
                    </div>
                    <div class="text-left sm:text-right shrink-0 pl-8 sm:pl-0">
                        <span class="text-xl sm:text-2xl font-extrabold text-[#780000]" x-text="form.is_certified_diver ? '2,500 php' : '3,300 php'"></span>
                        <span class="text-xs sm:text-sm text-[#6E6E73] block" x-text="form.is_certified_diver ? 'Certified Diver' : 'Non-Certified Diver'"></span>
                    </div>
                </div>

                <!-- Refinement Card -->
                <div @click="form.class_type = 'refinement'" 
                     class="p-4 sm:p-6 rounded-xl border-1 transition-all cursor-pointer flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                     :class="form.class_type === 'refinement' ? 'border-[#780000] bg-[#F8EAEA]/30 ring-1 ring-[#780000]' : 'border-[#E5E5EA] hover:border-[#D1D1D6]'">
                    <div class="flex items-start gap-3 sm:gap-4">
                        <div class="w-5 h-5 sm:w-6 sm:h-6 rounded-full border-1 flex items-center justify-center shrink-0 mt-0.5"
                             :class="form.class_type === 'refinement' ? 'border-[#780000] bg-[#780000]' : 'border-[#D1D1D6]'">
                            <span x-show="form.class_type === 'refinement'" class="w-2 h-2 rounded-full bg-white"></span>
                        </div>
                        <div>
                            <div class="inline-block px-2 py-0.5 rounded text-xs font-bold uppercase bg-[#2C2C2E] text-white mb-1">PRACTICE DIVE</div>
                            <h3 class="text-lg sm:text-xl font-bold text-[#1D1D1F]">Refinement</h3>
                            <p class="text-sm text-[#6E6E73] mt-1">Practice dive with 2 open water sessions, pool access, coach fee, 3 meals, and photo/video coverage.</p>
                        </div>
                    </div>
                    <div class="text-left sm:text-right shrink-0 pl-8 sm:pl-0">
                        <span class="text-xl sm:text-2xl font-extrabold text-[#780000]">4,100 php</span>
                        <span class="text-xs sm:text-sm text-[#6E6E73] block">/ person</span>
                    </div>
                </div>
            </div>

            <!-- Fundive Inline Prerequisite & Certification Logic -->
            <div x-show="form.class_type === 'fundive'" x-cloak class="p-4 sm:p-5 bg-[#FFFBEB] border border-[#FDE68A] rounded-xl space-y-4 text-sm">
                <div>
                    <h4 class="font-bold text-[#92400E]">Fundive Prerequisite: Discovery Class</h4>
                    <p class="text-sm text-[#78350F] mt-0.5">Please indicate if you hold a certified freediver license or finished Discovery Class.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                    <label class="flex items-center gap-3 p-3 bg-white rounded-xl border border-[#FDE68A] cursor-pointer">
                        <input type="radio" name="is_certified" :value="false" x-model="form.is_certified_diver" class="text-[#780000] focus:ring-[#780000]">
                        <span class="font-semibold text-[#1D1D1F]">Non-Certified (3,300 php)</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 bg-white rounded-xl border border-[#FDE68A] cursor-pointer">
                        <input type="radio" name="is_certified" :value="true" x-model="form.is_certified_diver" class="text-[#780000] focus:ring-[#780000]">
                        <span class="font-semibold text-[#1D1D1F]">Certified Diver (2,500 php)</span>
                    </label>
                </div>

                <div class="pt-2">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" x-model="form.discovery_completed_ack" class="w-4 h-4 rounded text-[#780000] focus:ring-[#780000] mt-0.5">
                        <span class="font-semibold text-[#78350F]">
                            I confirm that I have completed Discovery Class or hold a certified freediving license.
                        </span>
                    </label>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- STEP 2: SELECT DATE (WITH APPLE HIG LOADING & FORECAST) -->
        <!-- ========================================================================= -->
        <div x-show="currentStep === 2" x-cloak class="space-y-6">
            <div class="border-b border-[#E5E5EA] pb-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F]">Choose Your Dive Dates</h2>
                        <p class="text-xs sm:text-sm text-[#6E6E73] mt-1">Select your preferred weekend or trip dates for your Batangas freediving experience.</p>
                    </div>

                    <!-- Information Icon with Hover Notice -->
                    <div class="relative group inline-flex items-center self-start sm:self-center">
                        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#F2F2F7] hover:bg-[#E5E5EA] text-xs font-semibold text-[#1D1D1F] cursor-pointer transition-colors">
                            <svg class="w-4 h-4 text-[#008E98] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                            <span>About  Forecast</span>
                        </div>

                        <!-- Hover Popover Notice -->
                        <div class="absolute right-0 sm:right-auto sm:left-0 top-full mt-2 w-80 p-3.5 bg-[#1D1D1F] text-white text-xs rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 pointer-events-none space-y-1.5 leading-relaxed">
                            <div class="font-bold flex items-center gap-1 text-[#00C3D0]">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                <span>Weather & Sea Conditions Note</span>
                            </div>
                            <p class="text-[11px] text-gray-200">
                                Safety ratings shown are automated predictions based on coastal forecast models. Actual water conditions can change naturally, and our safety team continuously checks the water before every dive.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Date Picker Inputs -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-1">
                        Trip Start Date (Day 1) <span class="text-[#780000]">*</span>
                    </label>
                    <input type="date" 
                           x-model="form.start_date" 
                           @change="onStartDateChange()"
                           min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                           class="w-full px-4 py-3 rounded-xl border border-[#D1D1D6] focus:border-[#780000] focus:ring-2 focus:ring-[#780000]/20 text-sm font-medium text-[#1D1D1F] bg-white">
                </div>

                <div>
                    <label class="block font-bold text-[#6E6E73] mb-1">
                        Trip End Date (Day 2)
                    </label>
                    <input type="date" 
                           x-model="form.end_date" 
                           disabled 
                           class="w-full px-4 py-3 rounded-xl border border-[#E5E5EA] bg-[#F2F2F7] text-sm font-medium text-[#6E6E73] cursor-not-allowed">
                </div>
            </div>

            <!-- Weather Safety Forecast Section -->
            <div x-show="form.start_date && (weatherLoading || (forecast && !forecast.is_benchmark))" x-cloak class="pt-2 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-[#1D1D1F] text-sm">
                        Dive Safety Evaluation:
                    </span>
                    <span x-show="weatherLoading" class="text-xs text-[#008E98] font-bold flex items-center gap-1.5">
                        <svg class="animate-spin h-3.5 w-3.5 text-[#008E98]" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        Evaluating Radar...
                    </span>
                </div>

                <!-- 1. APPLE HIG DETERMINATE LOADING STATE & PLACEHOLDER SKELETON -->
                <div x-show="weatherLoading" x-cloak class="rounded-2xl p-5 border border-[#E5E5EA] bg-[#FAFAFC] space-y-4 transition-all">
                    <!-- Progress Bar & Status Text -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <span class="relative flex h-2.5 w-2.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#008E98] opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-[#008E98]"></span>
                                </span>
                                <span class="font-semibold text-[#1D1D1F]">Connecting to telemetry...</span>
                            </div>
                            <span class="font-mono font-bold text-[#008E98]" x-text="weatherProgress + '%'"></span>
                        </div>
                        <div class="w-full bg-[#E5E5EA] h-1.5 rounded-full overflow-hidden">
                            <div class="bg-gradient-to-r from-[#008E98] to-[#00C3D0] h-full transition-all duration-200 rounded-full"
                                 :style="'width: ' + weatherProgress + '%'"></div>
                        </div>
                    </div>

                    <!-- Placeholder Skeleton Cards (HIG: Show layout structure immediately) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <div class="p-4 rounded-xl bg-white border border-[#E5E5EA] space-y-2.5 animate-pulse">
                            <div class="flex justify-between items-center">
                                <div class="h-3.5 w-14 bg-[#E5E5EA] rounded-full"></div>
                                <div class="h-4 w-16 bg-[#E5E5EA] rounded-full"></div>
                            </div>
                            <div class="h-3 w-28 bg-[#F2F2F7] rounded"></div>
                            <div class="h-3 w-36 bg-[#F2F2F7] rounded"></div>
                            <div class="h-2.5 w-full bg-[#F2F2F7] rounded"></div>
                        </div>
                        <div class="p-4 rounded-xl bg-white border border-[#E5E5EA] space-y-2.5 animate-pulse">
                            <div class="flex justify-between items-center">
                                <div class="h-3.5 w-14 bg-[#E5E5EA] rounded-full"></div>
                                <div class="h-4 w-16 bg-[#E5E5EA] rounded-full"></div>
                            </div>
                            <div class="h-3 w-28 bg-[#F2F2F7] rounded"></div>
                            <div class="h-3 w-36 bg-[#F2F2F7] rounded"></div>
                            <div class="h-2.5 w-full bg-[#F2F2F7] rounded"></div>
                        </div>
                    </div>

                    <!-- Rotating Marine Insight Tip (HIG: Informative wait-time engagement) -->
                    <div class="p-3 rounded-xl bg-white border border-[#E5E5EA] flex items-center gap-2.5 text-xs text-[#6E6E73]">
                        <span class="text-base shrink-0" x-text="currentTip.icon"></span>
                        <div class="flex-1 min-w-0">
                            <span class="font-bold text-[#1D1D1F]" x-text="currentTip.title + ': '"></span>
                            <span x-text="currentTip.text"></span>
                        </div>
                    </div>
                </div>

                <!-- 2. FORECAST RESULT CONTAINER (LIVE TELEMETRY ONLY, NOT BENCHMARK) -->
                <template x-if="forecast && !weatherLoading && !forecast.is_benchmark">
                    <div class="rounded-2xl p-5 sm:p-6 border transition-all space-y-4 shadow-2xs"
                         :style="'background-color: ' + forecast.bg_color + '; border-color: ' + forecast.border_color + '; color: ' + forecast.text_color">
                        
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <h4 class="text-base sm:text-lg font-extrabold" x-text="forecast.title"></h4>
                                <p class="text-xs sm:text-sm opacity-90" x-text="forecast.location"></p>
                            </div>
                        </div>

                        <p class="text-xs sm:text-sm leading-relaxed" x-text="forecast.description"></p>

                        <!-- Day 1 & Day 2 Cards -->
                        <template x-if="forecast.day1 && forecast.day2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                <!-- Day 1 Card -->
                                <div class="p-3.5 rounded-xl bg-white/85 border border-black/10 space-y-1.5 text-xs text-[#1D1D1F]">
                                    <div class="flex items-center justify-between">
                                        <span class="font-extrabold text-[#780000] text-xs uppercase tracking-wider">Day 1</span>
                                        <span class="font-bold px-2 py-0.5 rounded-full text-[11px]"
                                              :class="{
                                                  'bg-emerald-100 text-emerald-800': forecast.day1.classification === 'Very Safe' || forecast.day1.classification === 'Safe',
                                                  'bg-amber-100 text-amber-800': forecast.day1.classification === 'Moderate',
                                                  'bg-rose-100 text-rose-800': forecast.day1.classification === 'High Risk',
                                                  'bg-red-100 text-red-800': forecast.day1.classification === 'Critical Risk'
                                              }"
                                              x-text="forecast.day1.classification"></span>
                                    </div>
                                    <div class="text-xs text-[#6E6E73] space-y-0.5">
                                        <div class="font-bold text-[#1D1D1F]" x-text="forecast.day1.date"></div>
                                        <div>Worst Hour: <span class="font-medium text-[#1D1D1F]" x-text="forecast.day1.worst_hour"></span></div>
                                    </div>
                                    <p class="text-xs opacity-90 leading-tight pt-0.5" x-text="forecast.day1.recommended_action"></p>
                                </div>

                                <!-- Day 2 Card -->
                                <div class="p-3.5 rounded-xl bg-white/85 border border-black/10 space-y-1.5 text-xs text-[#1D1D1F]">
                                    <div class="flex items-center justify-between">
                                        <span class="font-extrabold text-[#780000] text-xs uppercase tracking-wider">Day 2</span>
                                        <span class="font-bold px-2 py-0.5 rounded-full text-[11px]"
                                              :class="{
                                                  'bg-emerald-100 text-emerald-800': forecast.day2.classification === 'Very Safe' || forecast.day2.classification === 'Safe',
                                                  'bg-amber-100 text-amber-800': forecast.day2.classification === 'Moderate',
                                                  'bg-rose-100 text-rose-800': forecast.day2.classification === 'High Risk',
                                                  'bg-red-100 text-red-800': forecast.day2.classification === 'Critical Risk'
                                              }"
                                              x-text="forecast.day2.classification"></span>
                                    </div>
                                    <div class="text-xs text-[#6E6E73] space-y-0.5">
                                        <div class="font-bold text-[#1D1D1F]" x-text="forecast.day2.date"></div>
                                        <div>Worst Hour: <span class="font-medium text-[#1D1D1F]" x-text="forecast.day2.worst_hour"></span></div>
                                    </div>
                                    <p class="text-xs opacity-90 leading-tight pt-0.5" x-text="forecast.day2.recommended_action"></p>
                                </div>
                            </div>
                        </template>

                    </div>
                </template>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- STEP 3: BOOKING DETAILS (LEFT) + LIVE SUMMARY (RIGHT) [5-STEP COMBINED] -->
        <!-- ========================================================================= -->
        <div x-show="currentStep === 3" x-cloak class="space-y-6">
            <div class="border-b border-[#E5E5EA] pb-4">
                <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F]">3. Guest Details, Add-ons & Summary</h2>
                <p class="text-xs sm:text-sm text-[#6E6E73] mt-1">Fill in the contact info for all divers joining and review your live pricing breakdown.</p>
            </div>

            <!-- 2-COLUMN RESPONSIVE LAYOUT -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">

                <!-- LEFT COLUMN: Form Inputs (7 Columns) -->
                <div class="lg:col-span-7 space-y-6">

                    <!-- SECTION 1: PARTICIPANTS -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-2">
                            <h3 class="text-base font-bold text-[#1D1D1F]">1. Participants</h3>
                            <button type="button" 
                                    @click="addParticipant()" 
                                    class="px-3.5 py-1.5 rounded-xl border border-[#780000] text-[#780000] font-bold text-xs bg-[#F8EAEA]/30 hover:bg-[#F8EAEA] transition-colors flex items-center gap-1.5 shadow-2xs">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                <span>Add Participant</span>
                            </button>
                        </div>
                        
                        <div class="space-y-4">
                            <template x-for="(participant, index) in form.participants" :key="index">
                                <div class="p-4 sm:p-5 rounded-xl bg-[#FAFAFC] relative space-y-4 shadow-2xs">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-[#780000] text-sm" x-text="'Participant #' + (index + 1)"></span>
                                        <button type="button" 
                                                x-show="form.participants.length > 1" 
                                                @click="removeParticipant(index)"
                                                class="text-xs font-semibold text-[#FF3B3C] hover:underline">
                                            Remove
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Full Name <span class="text-[#780000]">*</span></label>
                                            <input type="text" 
                                                   x-model="participant.name" 
                                                   @input="participant.name = participant.name.replace(/[^a-zA-Z\s\.\'\-]/g, '')"
                                                   placeholder="e.g. Maria Santos" 
                                                   class="w-full px-3.5 py-2.5 rounded-xl border text-sm text-[#1D1D1F] bg-white transition-colors"
                                                   :class="touchedStep3 && !validateName(participant.name) ? 'border-[#FF3B3C] bg-red-50/20' : 'border-[#D1D1D6] focus:border-[#780000]'">
                                            <span x-show="touchedStep3 && !validateName(participant.name)" class="text-[11px] text-[#FF3B3C] font-semibold mt-1 block">
                                                Please enter a valid full name (letters only, min 2 characters).
                                            </span>
                                        </div>

                                        <div>
                                            <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Age (8–85 yrs) <span class="text-[#780000]">*</span></label>
                                            <input type="number" 
                                                   x-model="participant.age" 
                                                   min="8" 
                                                   max="85" 
                                                   maxlength="2"
                                                   @input="if(participant.age && participant.age.toString().length > 2) participant.age = parseInt(participant.age.toString().slice(0, 2), 10)"
                                                   placeholder="e.g. 24" 
                                                   class="w-full px-3.5 py-2.5 rounded-xl border text-sm text-[#1D1D1F] bg-white transition-colors"
                                                   :class="touchedStep3 && !validateAge(participant.age) ? 'border-[#FF3B3C] bg-red-50/20' : 'border-[#D1D1D6] focus:border-[#780000]'">
                                            <span x-show="touchedStep3 && !validateAge(participant.age)" class="text-[11px] text-[#FF3B3C] font-semibold mt-1 block">
                                                Age must be between 8 and 85 years old.
                                            </span>
                                        </div>
                                    </div>

                                    <div x-show="form.class_type === 'discovery'" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Swimming Ability</label>
                                            <select x-model="participant.swimmer_status" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white">
                                                <option value="non_swimmer">Non-Swimmer</option>
                                                <option value="casual_swimmer">Casual / Beginner Swimmer</option>
                                                <option value="confident_swimmer">Confident Swimmer</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block font-bold text-[#1D1D1F] text-xs mb-2">
                                            Health Condition / Medical Notes
                                        </label>
                                        <input type="text" 
                                               x-model="participant.health_condition" 
                                               placeholder="e.g. Asthma, ear pressure issues, or None" 
                                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] focus:border-[#780000] text-sm text-[#1D1D1F] bg-white">
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- SECTION 2: CONTACT DETAILS -->
                    <div class="space-y-4 pt-2">
                        <h3 class="text-base font-bold text-[#1D1D1F] border-b border-[#E5E5EA] pb-2">2. Contact Information</h3>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Lead Contact Name <span class="text-[#780000]">*</span></label>
                                <input type="text" 
                                       x-model="form.contact_name" 
                                       @input="form.contact_name = form.contact_name.replace(/[^a-zA-Z\s\.\'\-]/g, '')"
                                       placeholder="Juan Dela Cruz" 
                                       class="w-full px-3.5 py-2.5 rounded-xl border text-sm text-[#1D1D1F] bg-white transition-colors"
                                       :class="touchedStep3 && !validateName(form.contact_name) ? 'border-[#FF3B3C] bg-red-50/20' : 'border-[#D1D1D6] focus:border-[#780000]'">
                                <span x-show="touchedStep3 && !validateName(form.contact_name)" class="text-[11px] text-[#FF3B3C] font-semibold mt-1 block">
                                    Please enter a valid lead name (min 2 characters).
                                </span>
                            </div>

                            <div>
                                <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Email Address <span class="text-[#780000]">*</span></label>
                                <input type="email" 
                                       x-model="form.contact_email" 
                                       placeholder="juan@example.com" 
                                       class="w-full px-3.5 py-2.5 rounded-xl border text-sm text-[#1D1D1F] bg-white transition-colors"
                                       :class="touchedStep3 && !validateEmail(form.contact_email) ? 'border-[#FF3B3C] bg-red-50/20' : 'border-[#D1D1D6] focus:border-[#780000]'">
                                <span x-show="touchedStep3 && !validateEmail(form.contact_email)" class="text-[11px] text-[#FF3B3C] font-semibold mt-1 block">
                                    Please enter a valid email address with @ (e.g. name@example.com).
                                </span>
                            </div>

                            <div>
                                <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Mobile Number (PH) <span class="text-[#780000]">*</span></label>
                                <input type="tel" 
                                       x-model="form.contact_phone" 
                                       @input="form.contact_phone = form.contact_phone.replace(/[^0-9+\s-]/g, '')"
                                       placeholder="0917 123 4567" 
                                       maxlength="16"
                                       class="w-full px-3.5 py-2.5 rounded-xl border text-sm text-[#1D1D1F] bg-white transition-colors"
                                       :class="touchedStep3 && !validatePhone(form.contact_phone) ? 'border-[#FF3B3C] bg-red-50/20' : 'border-[#D1D1D6] focus:border-[#780000]'">
                                <span x-show="touchedStep3 && !validatePhone(form.contact_phone)" class="text-[11px] text-[#FF3B3C] font-semibold mt-1 block">
                                    Valid 11-digit PH mobile number required (e.g. 09171234567 or +639171234567).
                                </span>
                            </div>
                            <div>
                                <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Facebook Profile Link (Optional)</label>
                                <input type="text" x-model="form.contact_facebook" placeholder="facebook.com/juandelacruz" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] focus:border-[#780000] text-sm text-[#1D1D1F] bg-white">
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: ADD-ONS & TRANSPORTATION -->
                    <div class="space-y-4 pt-2">
                        <h3 class="text-base font-bold text-[#1D1D1F] border-b border-[#E5E5EA] pb-2">3. Transportation & Add-ons</h3>
                        
                        <div class="space-y-3">
                            <label class="block font-bold text-[#1D1D1F] text-xs">Transportation Option:</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="p-3.5 rounded-xl border-2 transition-all cursor-pointer flex flex-col justify-between select-none"
                                       :class="form.pickup_option === 'carpool' ? 'border-[#780000] bg-[#F8EAEA]/40 shadow-xs' : 'border-[#E5E5EA] bg-white hover:border-[#D1D1D6]'">
                                    <input type="radio" name="pickup_opt" value="carpool" x-model="form.pickup_option" class="hidden">
                                    <div class="space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-extrabold text-xs sm:text-sm text-[#1D1D1F]">Manila Carpool Van</span>
                                            <span x-show="form.pickup_option === 'carpool'" class="w-2.5 h-2.5 rounded-full bg-[#780000]"></span>
                                        </div>
                                        <span class="font-bold text-[#780000] text-xs block">₱1,200 / person</span>
                                        <span class="text-[11px] text-[#780000] font-semibold block">(DP: ₱3,000 / head)</span>
                                    </div>
                                </label>

                                <label class="p-3.5 rounded-xl border-2 transition-all cursor-pointer flex flex-col justify-between select-none"
                                       :class="form.pickup_option === 'own' ? 'border-[#780000] bg-[#F8EAEA]/40 shadow-xs' : 'border-[#E5E5EA] bg-white hover:border-[#D1D1D6]'">
                                    <input type="radio" name="pickup_opt" value="own" x-model="form.pickup_option" class="hidden">
                                    <div class="space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-extrabold text-xs sm:text-sm text-[#1D1D1F]">Own Vehicle / Commute</span>
                                            <span x-show="form.pickup_option === 'own'" class="w-2.5 h-2.5 rounded-full bg-[#780000]"></span>
                                        </div>
                                        <span class="font-bold text-[#1D1D1F] text-xs block">₱0 (Self-arranged)</span>
                                        <span class="text-[11px] text-[#6E6E73] font-semibold block">(DP: ₱2,000 / head)</span>
                                    </div>
                                </label>
                            </div>

                            <!-- Pickup Hub Selection with Times -->
                            <div x-show="form.pickup_option === 'carpool'" x-cloak class="pt-1">
                                <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Carpool Pickup Hub & Schedule:</label>
                                <select x-model="form.pickup_location" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white font-medium">
                                    <template x-for="p in pickupPoints" :key="p.id">
                                        <option :value="p.name" x-text="p.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <!-- Optional Boat Dive -->
                        <div class="pt-1">
                            <label class="p-3.5 rounded-xl border-1 transition-all cursor-pointer flex items-start justify-between gap-3"
                                   :class="form.boat_dive ? 'border-[#00C3D0] bg-[#E0F9FB]/30' : 'border-[#E5E5EA] bg-white'">
                                <div class="flex items-start gap-2.5">
                                    <input type="checkbox" x-model="form.boat_dive" class="w-4 h-4 rounded text-[#008E98] focus:ring-[#00C3D0] mt-0.5">
                                    <div>
                                        <span class="font-bold text-xs sm:text-sm text-[#1D1D1F] block">Boat Dive (Optional)</span>
                                        <span class="text-xs text-[#6E6E73] block">Boat ride to deeper marine sanctuaries.</span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="font-extrabold text-[#008E98] text-xs sm:text-sm">+₱600</span>
                                    <span class="text-[10px] text-[#6E6E73] block">/ person</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- SECTION 4: ACCURACY VERIFICATION -->
                    <div class="pt-2">
                        <div class="p-3.5 rounded-xl bg-[#F8EAEA] border border-[#780000]/30">
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="checkbox" x-model="form.confirmation_ack" class="w-4 h-4 rounded text-[#780000] focus:ring-[#780000] mt-0.5 shrink-0">
                                <span class="font-bold text-[#780000] text-xs leading-relaxed">
                                    I confirm that all information provided is accurate. <span class="text-red-500">*</span>
                                </span>
                            </label>
                        </div>
                    </div>

                </div>

                <!-- RIGHT COLUMN: Live Booking Summary & Policy (5 Columns, Sticky) -->
                <div class="lg:col-span-5 space-y-4 lg:sticky lg:top-6">
                    
                    <!-- Itemized Invoice Card -->
                    <div class="border border-[#E5E5EA] rounded-2xl bg-white overflow-hidden">
                        <div class="bg-[#FAFAFC] px-4 py-3 border-b border-[#E5E5EA] flex items-center justify-between">
                            <span class="font-bold text-[#1D1D1F] text-sm">Booking Summary</span>
                            <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-[#EBF5FF] text-[#007DFE] capitalize" x-text="form.class_type"></span>
                        </div>

                        <div class="p-4 space-y-3 text-xs">
                            <div class="flex justify-between items-center text-[#6E6E73]">
                                <span>Base Class Rate (<span class="capitalize" x-text="form.class_type"></span> × <span x-text="form.participants.length"></span>)</span>
                                <span class="font-bold text-[#1D1D1F]" x-text="'₱' + formatNumber((pricingQuote ? pricingQuote.base_price_per_pax : calculateBasePriceUnit()) * form.participants.length)"></span>
                            </div>

                            <!-- Dynamic Pricing Adjustments (Itemized Breakdown) -->
                            <template x-if="pricingQuote && pricingQuote.adjustments && pricingQuote.adjustments.length > 0">
                                <div class="space-y-1.5 py-2 border-y border-dashed border-[#E5E5EA]">
                                    <div class="text-[10px] uppercase font-bold tracking-wider text-[#6E6E73]">Seasonal & Demand Adjustments:</div>
                                    <template x-for="adj in pricingQuote.adjustments" :key="adj.rule_id">
                                        <div class="flex justify-between items-center text-xs">
                                            <div class="flex items-center gap-1.5">
                                                <span class="w-1.5 h-1.5 rounded-full" :class="adj.delta_per_pax >= 0 ? 'bg-rose-500' : 'bg-emerald-500'"></span>
                                                <span class="text-[#1D1D1F]" x-text="adj.rule_name"></span>
                                                <span class="text-[10px] px-1.5 py-0.5 rounded font-bold" :class="adj.delta_per_pax >= 0 ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800'" x-text="adj.formatted_adjustment"></span>
                                            </div>
                                            <span class="font-bold" :class="adj.delta_per_pax >= 0 ? 'text-rose-700' : 'text-emerald-700'" x-text="(adj.delta_per_pax >= 0 ? '+' : '−') + '₱' + formatNumber(Math.abs(adj.delta_per_pax) * form.participants.length)"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <div class="flex justify-between items-center text-[#1D1D1F] font-semibold">
                                <span>Adjusted Class Subtotal</span>
                                <span class="font-extrabold text-[#1D1D1F]" x-text="'₱' + formatNumber(calculateSubtotal())"></span>
                            </div>

                            <div x-show="form.pickup_option === 'carpool'" class="flex justify-between items-center text-[#6E6E73]">
                                <span>Transportation (Carpool × <span x-text="form.participants.length"></span>)</span>
                                <span class="font-semibold text-[#1D1D1F]" x-text="'₱' + formatNumber(calculateCarpoolFee())"></span>
                            </div>

                            <div x-show="form.boat_dive" class="flex justify-between items-center text-[#6E6E73]">
                                <span>Boat Dive (₱600 × <span x-text="form.participants.length"></span>)</span>
                                <span class="font-semibold text-[#1D1D1F]" x-text="'₱' + formatNumber(600 * form.participants.length)"></span>
                            </div>

                            <div class="flex justify-between items-center text-[#6E6E73]">
                                <span>Mabini LGU Pass & Env. Fee</span>
                                <span class="font-semibold text-[#1D1D1F]" x-text="'₱' + formatNumber(350 * form.participants.length)"></span>
                            </div>

                            <div class="pt-2 border-t border-[#E5E5EA] flex justify-between items-center font-extrabold text-sm text-[#1D1D1F]">
                                <span>Total Amount</span>
                                <span class="text-base font-black text-[#1D1D1F]" x-text="'₱' + formatNumber(calculateTotal())"></span>
                            </div>

                            <!-- Downpayment Box -->
                            <div class="p-3 rounded-xl bg-[#ECFDF5] flex justify-between items-center">
                                <div>
                                    <span class="font-extrabold text-[#065F46] block text-xs">Downpayment Due Now</span>
                                    <span class="text-[10px] text-[#065F46]" x-text="'(' + (form.pickup_option === 'carpool' ? '3,000' : '2,000') + ' php / head)'"></span>
                                </div>
                                <span class="text-base sm:text-lg font-black text-[#065F46]" x-text="'₱' + formatNumber(calculateDownpayment())"></span>
                            </div>

                            <!-- Balance Box -->
                            <div class="p-2.5 rounded-xl bg-[#FFFBEB] flex justify-between items-center text-[#92400E]">
                                <span class="font-semibold text-[11px]">Remaining Balance at Camp</span>
                                <span class="font-bold text-xs" x-text="'₱' + formatNumber(calculateTotal() - calculateDownpayment())"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Cancellation Policy Card -->
                    <div class="p-4 rounded-2xl bg-[#FAFAFC] border border-[#E5E5EA] space-y-2.5">
                        <h4 class="font-bold text-[#780000] text-xs">
                            Cancellation & Reschedule Policy
                        </h4>
                        <div class="space-y-2 text-[11px] text-[#6E6E73]">
                            <div class="p-2.5 rounded-xl bg-white space-y-0.5 border border-[#E5E5EA]">
                                <div class="flex items-center gap-1.5 font-bold text-[#1D1D1F] text-xs">
                                    <span>Notice Given More than 2 Weeks (> 14 Days)</span>
                                </div>
                                <p class="text-[11px] text-[#4A4A4F] leading-normal pl-4">
                                    Eligible for 100% full downpayment refund or 1 free date reschedule to any future open schedule.
                                </p>
                            </div>
                            <div class="p-2.5 rounded-xl bg-white space-y-0.5 border border-[#E5E5EA]">
                                <div class="flex items-center gap-1.5 font-bold text-[#1D1D1F] text-xs">
                                    <span>Notice Given 7 to 14 Days Before Trip</span>
                                </div>
                                <p class="text-[11px] text-[#4A4A4F] leading-normal pl-4">
                                    Free date reschedule allowed to another available schedule. Downpayment is non-refundable.
                                </p>
                            </div>
                            <div class="p-2.5 rounded-xl bg-white space-y-0.5 border border-[#E5E5EA]">
                                <div class="flex items-center gap-1.5 font-bold text-[#1D1D1F] text-xs">
                                    <span>Notice Given Less than 7 Days (Locked Window)</span>
                                </div>
                                <p class="text-[11px] text-[#4A4A4F] leading-normal pl-4">
                                    Slot is strictly locked with resort/boat allocations. Non-refundable and cannot be rescheduled.
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- STEP 4: DOWNPAYMENT (PAYMONGO INTEGRATED WITH DIRECT IN-PAGE DETAIL ENTRY) -->
        <!-- ========================================================================= -->
        <div x-show="currentStep === 4" x-cloak class="space-y-6">

            <!-- SUB-STEP 1: PAYMENT METHOD SELECTOR (WITH BACK BUTTON) -->
            <div x-show="paymentSubStep === 'select'" class="max-w-lg mx-auto bg-white rounded-2xl border border-[#E5E5EA] p-6 sm:p-8 shadow-sm space-y-6">
                <!-- Top Navigation & Header -->
                <div class="flex items-center justify-between border-b border-[#F2F2F7] pb-4">
                    <button type="button" 
                            @click="prevStep()" 
                            class="text-xs font-bold text-[#6E6E73] hover:text-[#1D1D1F] flex items-center gap-1.5 transition-colors">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                        <span>Back to Booking Details</span>
                    </button>
                    <div class="text-right">
                        <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#FF3B3C] block">Reservation Lock:</span>
                        <span class="text-sm sm:text-base font-mono font-black text-[#FF3B3C]" x-text="timerDisplay"></span>
                    </div>
                </div>

                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F]">4. Secure Downpayment</h2>
                    <p class="text-xs sm:text-sm text-[#6E6E73] mt-1">Select your preferred payment option to lock in your slots. The rest will be paid when you arrive at camp.</p>
                </div>

                <!-- Payment Options List: GCash and BPI only -->
                <div class="space-y-3.5">

                    <!-- Option 1: GCash -->
                    <div @click="form.payment_method = 'gcash'"
                         class="rounded-xl border-2 transition-all p-4.5 cursor-pointer flex items-center justify-between select-none"
                         :class="form.payment_method === 'gcash' ? 'border-[#007DFE] bg-[#007DFE]/5 ring-1 ring-[#007DFE]' : 'border-[#E5E5EA] bg-white hover:border-[#D1D1D6]'">
                        <div class="flex items-center gap-3.5">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-[#007DFE] text-white font-black text-sm shadow-2xs">G</span>
                            <div>
                                <span class="text-sm font-bold text-[#1D1D1F] block">GCash (via PayMongo)</span>
                                <span class="text-xs text-[#6E6E73] block mt-0.5">Pay via GCash e-wallet / PayMongo gateway</span>
                            </div>
                        </div>
                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0"
                             :class="form.payment_method === 'gcash' ? 'border-[#007DFE] bg-[#007DFE]' : 'border-[#D1D1D6]'">
                            <div x-show="form.payment_method === 'gcash'" class="w-2 h-2 rounded-full bg-white"></div>
                        </div>
                    </div>

                    <!-- Option 2: BPI -->
                    <div @click="form.payment_method = 'bpi'"
                         class="rounded-xl border-2 transition-all p-4.5 cursor-pointer flex items-center justify-between select-none"
                         :class="['bpi', 'dob', 'bpi_bank_transfer'].includes(form.payment_method) ? 'border-[#B30916] bg-[#B30916]/5 ring-1 ring-[#B30916]' : 'border-[#E5E5EA] bg-white hover:border-[#D1D1D6]'">
                        <div class="flex items-center gap-3.5">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-[#B30916] text-white font-black text-xs shadow-2xs">BPI</span>
                            <div>
                                <span class="text-sm font-bold text-[#1D1D1F] block">BPI Bank Transfer / Online</span>
                                <span class="text-xs text-[#6E6E73] block mt-0.5">Direct transfer to Camp FreedivePH account</span>
                            </div>
                        </div>
                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0"
                             :class="['bpi', 'dob', 'bpi_bank_transfer'].includes(form.payment_method) ? 'border-[#B30916] bg-[#B30916]' : 'border-[#D1D1D6]'">
                            <div x-show="['bpi', 'dob', 'bpi_bank_transfer'].includes(form.payment_method)" class="w-2 h-2 rounded-full bg-white"></div>
                        </div>
                    </div>

                </div>

                <!-- Downpayment Amount Due -->
                <div class="p-4 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] flex items-center justify-between text-sm">
                    <div>
                        <span class="text-xs font-bold text-[#1D1D1F] block">Required Downpayment</span>
                        <span class="text-[11px] text-[#6E6E73]" x-text="'(' + (form.pickup_option === 'carpool' ? '₱3,000' : '₱2,000') + ' / head × ' + form.participants.length + ' pax)'"></span>
                    </div>
                    <strong class="text-lg sm:text-xl font-black text-[#065F46]" x-text="'₱' + formatNumber(calculateDownpayment())"></strong>
                </div>

                <!-- Primary Continue Button -->
                <div class="space-y-2 pt-1">
                    <button type="button" 
                            @click="proceedToPaymentDetails()" 
                            class="w-full py-3.5 px-6 rounded-xl font-bold text-base text-white bg-[#82C39B] hover:bg-[#68B285] active:scale-[0.99] transition-all shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                        <span>Continue to Payment</span>
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </button>
                </div>

                <!-- Footer -->
                <div class="pt-3 border-t border-[#F2F2F7] text-center space-y-1">
                    <p class="text-xs text-[#6E6E73] leading-relaxed max-w-xs mx-auto">
                        Official payment gateway powered by <strong>PayMongo Philippines</strong>.
                    </p>
                </div>
            </div>

            <!-- SUB-STEP 2: INTERACTIVE PAYMENT DETAILS ENTRY SCREEN -->
            <div x-show="paymentSubStep === 'details'" class="max-w-lg mx-auto bg-white rounded-2xl border border-[#E5E5EA] p-6 sm:p-8 shadow-sm space-y-6">
                <!-- Top Navigation & Title -->
                <div class="flex items-center justify-between border-b border-[#F2F2F7] pb-4">
                    <button type="button" 
                            @click="paymentSubStep = 'select'" 
                            class="text-xs font-bold text-[#6E6E73] hover:text-[#1D1D1F] flex items-center gap-1.5 transition-colors">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                        <span>Change Method</span>
                    </button>
                    <div class="text-right">
                        <span class="text-xs font-semibold text-[#6E6E73] block">Amount to Pay:</span>
                        <span class="text-base sm:text-lg font-black text-[#1D1D1F]" x-text="'₱' + formatNumber(calculateDownpayment())"></span>
                    </div>
                </div>

                <!-- CASE 1: GCASH IN-PAGE EXPRESS CHECKOUT -->
                <template x-if="form.payment_method === 'gcash'">
                    <div class="space-y-5">
                        <div class="flex items-center justify-between border-b border-[#F2F2F7] pb-3">
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-[#1D1D1F]">GCash Express Checkout</h3>
                                <p class="text-xs text-[#6E6E73] mt-0.5">Direct in-page authorization powered by PayMongo.</p>
                            </div>
                            <span class="px-3 py-1 rounded-lg bg-[#007DFE] text-white font-black text-xs shadow-2xs">GCash</span>
                        </div>

                        <!-- Mobile Number & OTP Inputs -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-[#1D1D1F] mb-1.5">Registered GCash Mobile Number <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-2.5 text-sm font-semibold text-[#6E6E73]">+63</span>
                                    <input type="tel" 
                                           x-model="ewalletForm.phone" 
                                           placeholder="917 123 4567" 
                                           class="w-full pl-12 pr-3.5 py-3 rounded-xl border border-[#D1D1D6] text-sm font-mono focus:ring-2 focus:ring-[#82C39B] focus:border-transparent outline-hidden bg-white">
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="text-xs font-bold text-[#1D1D1F]">6-Digit Security OTP <span class="text-red-500">*</span></label>
                                    <span class="text-[11px] text-[#6E6E73]">Test OTP: <strong class="font-mono text-[#007DFE]">123456</strong></span>
                                </div>
                                <input type="text" 
                                       x-model="ewalletForm.otp" 
                                       maxlength="6" 
                                       placeholder="123456" 
                                       class="w-full px-3.5 py-3 rounded-xl border border-[#D1D1D6] text-sm font-mono text-center tracking-widest focus:ring-2 focus:ring-[#82C39B] focus:border-transparent outline-hidden bg-white">
                            </div>
                        </div>

                        <!-- Authorize Button (Direct In-Page) -->
                        <button type="button" 
                                @click="validateAndProcessPayment()" 
                                :disabled="submittingPayment"
                                class="w-full py-3.5 px-6 rounded-xl font-bold text-base text-white bg-[#007DFE] hover:bg-[#0066D6] active:scale-[0.99] transition-all shadow-sm flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                            <span x-show="!submittingPayment">Authorize & Pay ₱<span x-text="formatNumber(calculateDownpayment())"></span></span>
                            <span x-show="submittingPayment" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                Authorizing GCash Payment...
                            </span>
                        </button>
                    </div>
                </template>

                <!-- CASE 2: BPI ONLINE / BANK TRANSFER FORM -->
                <template x-if="['bpi', 'dob', 'bpi_bank_transfer'].includes(form.payment_method)">
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-lg font-bold text-[#1D1D1F]">BPI Bank Transfer Details</h3>
                            <p class="text-xs text-[#6E6E73] mt-0.5">Transfer downpayment to Camp FreedivePH official bank account.</p>
                        </div>

                        <!-- Bank Account Box -->
                        <div class="p-4 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] text-xs space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-[#6E6E73]">Bank:</span>
                                <strong class="text-[#1D1D1F]">Bank of the Philippine Islands (BPI)</strong>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-[#6E6E73]">Account Name:</span>
                                <strong class="text-[#1D1D1F]">Camp FreedivePH Mabini</strong>
                            </div>
                            <div class="flex justify-between items-center pt-1 border-t border-[#E5E5EA]">
                                <span class="text-[#6E6E73]">Account Number:</span>
                                <strong class="font-mono text-sm text-[#B30916]">1234-5678-90</strong>
                            </div>
                        </div>

                        <!-- Reference Input -->
                        <div>
                            <label class="block text-xs font-bold text-[#1D1D1F] mb-1">Bank Reference Number / Transaction ID <span class="text-red-500">*</span></label>
                            <input type="text" 
                                   x-model="bankForm.reference_number" 
                                   placeholder="e.g. BPI-982341" 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-sm font-mono focus:ring-2 focus:ring-[#82C39B] focus:border-transparent outline-hidden bg-white">
                        </div>

                        <!-- Confirm Button -->
                        <button type="button" 
                                @click="validateAndProcessPayment()" 
                                :disabled="submittingPayment"
                                class="w-full py-3.5 px-6 rounded-xl font-bold text-base text-white bg-[#82C39B] hover:bg-[#68B285] active:scale-[0.99] transition-all shadow-sm flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                            <span x-show="!submittingPayment">Confirm Payment & Finish Booking</span>
                            <span x-show="submittingPayment" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                Confirming BPI Payment...
                            </span>
                        </button>
                    </div>
                </template>

                <!-- Security Assurance footer -->
                <div class="pt-3 border-t border-[#F2F2F7] flex items-center justify-center gap-1.5 text-xs text-[#8E8E93]">
                    <svg class="w-3.5 h-3.5 text-[#34C759]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <span>256-Bit SSL Encrypted & Secured by PayMongo</span>
                </div>
            </div>

        </div>

        <!-- ========================================================================= -->
        <!-- STEP 5: CONFIRMATION & PIN RETRIEVAL (FINAL STEP) -->
        <!-- ========================================================================= -->
        <div x-show="currentStep === 5" x-cloak class="space-y-6 text-center">
            <div class="w-16 h-16 bg-[#ECFDF5] text-[#34C759] rounded-full flex items-center justify-center mx-auto text-3xl font-extrabold shadow-sm border border-[#A7F3D0]">
                ✓
            </div>

            <div class="space-y-2">
                <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F]">
                    Booking Confirmed!
                </h2>
                <p class="text-xs sm:text-sm text-[#6E6E73] max-w-md mx-auto">
                    We've emailed your booking confirmation to <strong class="text-[#1D1D1F]" x-text="form.contact_email"></strong>. Please save your reference number and PIN below.
                </p>
            </div>

            <!-- Booking Credentials Voucher -->
            <div class="max-w-md mx-auto p-5 rounded-2xl bg-[#F8EAEA]/40 border border-[#780000]/20 space-y-4 shadow-xs">
                <div>
                    <span class="text-xs uppercase tracking-wider text-[#6E6E73] font-bold">Booking Reference Number</span>
                    <div class="text-2xl sm:text-3xl font-mono font-extrabold text-[#780000] tracking-wider" x-text="confirmedBooking.booking_number"></div>
                </div>

                <div class="pt-2 border-t border-[#780000]/20">
                    <span class="text-xs uppercase tracking-wider text-[#6E6E73] font-bold">4-Digit Security PIN</span>
                    <div class="text-2xl font-mono font-bold text-[#1D1D1F] tracking-widest" x-text="confirmedBooking.pin"></div>
                    <span class="text-xs text-[#6E6E73] block mt-1">Keep this PIN safe to manage or update your booking anytime.</span>
                </div>

                <div class="pt-2">
                    <button type="button" 
                            @click="copyCredentials()" 
                            class="px-4 py-2 rounded-lg bg-white text-[#780000] border border-[#780000]/30 hover:bg-[#F8EAEA] text-xs font-bold transition-colors shadow-2xs">
                        <span x-text="copied ? 'Copied to Clipboard!' : 'Copy Booking # and PIN'"></span>
                    </button>
                </div>
            </div>

            <!-- Recap Summary -->
            <div class="max-w-lg mx-auto p-4 sm:p-5 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] text-left text-xs sm:text-sm space-y-2.5">
                <div class="flex justify-between border-b border-[#E5E5EA] pb-2">
                    <span class="text-[#6E6E73]">Package:</span>
                    <span class="font-bold text-[#1D1D1F] capitalize" x-text="form.class_type"></span>
                </div>
                <div class="flex justify-between border-b border-[#E5E5EA] pb-2">
                    <span class="text-[#6E6E73]">Trip Dates:</span>
                    <span class="font-bold text-[#1D1D1F]" x-text="form.start_date + ' to ' + form.end_date"></span>
                </div>
                <div class="flex justify-between border-b border-[#E5E5EA] pb-2">
                    <span class="text-[#6E6E73]">Participants:</span>
                    <span class="font-bold text-[#1D1D1F]" x-text="form.participants.length + ' participant(s)'"></span>
                </div>

                <!-- Step 5: Applied Pricing Rules Recap -->
                <template x-if="pricingQuote && pricingQuote.adjustments && pricingQuote.adjustments.length > 0">
                    <div class="py-2 border-b border-[#E5E5EA] space-y-1.5">
                        <div class="text-[10px] uppercase font-bold tracking-wider text-[#6E6E73]">Applied Dynamic Pricing Rules:</div>
                        <template x-for="adj in pricingQuote.adjustments" :key="adj.rule_id">
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-[#1D1D1F]" x-text="adj.rule_name + ' (' + adj.formatted_adjustment + ')'"></span>
                                <span class="font-bold" :class="adj.delta_per_pax >= 0 ? 'text-rose-700' : 'text-emerald-700'" x-text="(adj.delta_per_pax >= 0 ? '+' : '−') + '₱' + formatNumber(Math.abs(adj.delta_per_pax) * form.participants.length)"></span>
                            </div>
                        </template>
                    </div>
                </template>

                <div class="flex justify-between border-b border-[#E5E5EA] pb-2">
                    <span class="text-[#6E6E73]">Transportation:</span>
                    <span class="font-bold text-[#1D1D1F]" x-text="form.pickup_option === 'carpool' ? form.pickup_location : 'Own Transportation'"></span>
                </div>
                <div class="flex justify-between border-b border-[#E5E5EA] pb-2">
                    <span class="text-[#6E6E73]">Downpayment Paid:</span>
                    <span class="font-bold text-[#34C759]" x-text="'₱' + formatNumber(confirmedBooking.downpayment_paid)"></span>
                </div>
                <div class="flex justify-between text-[#780000] font-bold">
                    <span>Balance Due at Camp:</span>
                    <span x-text="'₱' + formatNumber(confirmedBooking.balance_due)"></span>
                </div>
            </div>

            <!-- Things to Bring Checklist -->
            <div class="max-w-lg mx-auto p-5 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] text-left text-xs sm:text-sm space-y-2">
                <h4 class="font-bold text-[#1D1D1F]">Things to Bring:</h4>
                <ul class="space-y-1 text-xs sm:text-sm text-[#6E6E73] list-disc list-inside">
                    <li>Swimming clothes (anything you’re comfortable wearing)</li>
                    <li>Toiletries</li>
                    <li>Personal things</li>
                    <li>A pair of socks (in any kind) for fin fitting</li>
                </ul>
                <p class="text-[11px] text-[#065F46] font-semibold pt-1">
                    (Towels, shampoo and soap are all provided)
                </p>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-4">
                <a :href="confirmedBooking.manage_url" class="btn-primary w-full sm:w-auto px-6 py-3 text-sm">
                    Manage This Booking
                </a>
                <a href="{{ route('landing') }}" class="btn-secondary w-full sm:w-auto px-6 py-3 text-sm">
                    Done / Back to Home
                </a>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- BOTTOM WIZARD CONTROLS (Steps 1, 2, 3) -->
        <!-- ========================================================================= -->
        <div x-show="currentStep < 4" class="mt-8 pt-6 flex items-center justify-between">
            <button type="button" 
                    @click="prevStep()" 
                    x-show="currentStep > 1"
                    class="btn-secondary px-4 sm:px-5 py-2.5 text-sm">
                ← Back
            </button>
            <div x-show="currentStep === 1"></div>

            <button type="button" 
                    @click="nextStep()" 
                    class="btn-primary px-5 sm:px-8 py-2.5 text-sm shadow-sm cursor-pointer active:scale-[0.99] transition-all">
                <span x-text="currentStep === 3 ? 'Proceed to Downpayment (₱' + formatNumber(calculateDownpayment()) + ') →' : 'Continue →'"></span>
            </button>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function bookingWizard(config) {
    return {
        currentStep: 1,
        stepTitles: [
            "Select Class",
            "Choose Dive Dates",
            "Contact, Add-ons & Summary",
            "Secure Downpayment",
            "Booking Confirmed"
        ],
        pickupPoints: config.pickupPoints,
        form: {
            class_type: config.initialClass || 'discovery',
            is_certified_diver: false,
            discovery_completed_ack: false,
            start_date: '',
            end_date: '',
            participants: [
                { name: '', age: '', health_condition: '', swimmer_status: 'non_swimmer' }
            ],
            contact_name: '',
            contact_email: '',
            contact_phone: '',
            contact_facebook: '',
            pickup_option: 'carpool',
            pickup_location: config.pickupPoints[0] ? config.pickupPoints[0].name : '',
            boat_dive: false,
            confirmation_ack: false,
            payment_method: 'gcash'
        },
        paymentSubStep: 'select',
        ewalletsOpen: false,
        bankingOpen: false,
        cardForm: {
            name: '',
            number: '',
            expiry: '',
            cvc: ''
        },
        ewalletForm: {
            phone: '',
            otp: '123456'
        },
        bankForm: {
            reference_number: ''
        },
        forecast: null,
        pricingQuote: null,
        weatherLoading: false,
        weatherProgress: 0,
        weatherProgressTimer: null,
        weatherTipTimer: null,
        weatherTipIndex: 0,
        weatherTips: [
            {title: 'Marine Safety', text: 'Mabini coastal waters feature sheltered coves ideal for beginner and advanced freediving.' },
            {title: 'Freediving Tip', text: 'Equalization is key: always equalize early and frequently before feeling ear pressure.' },
            {title: 'Weather Telemetry', text: 'Open-Meteo evaluates wave height (<1.0m is ideal), surface currents, and gust velocity.' },
            {title: 'Sanctuary Dives', text: 'Camp FreedivePH operates in Mabini Marine Protected Areas with clear year-round visibility.' },
            {title: 'Carpool Hubs', text: 'Weekend carpool vans depart Manila hubs at 2:30 AM – 3:30 AM directly to camp resort.' }
        ],
        get currentTip() {
            return this.weatherTips[this.weatherTipIndex] || this.weatherTips[0];
        },
        errorMessage: '',
        submittingPayment: false,
        timerSeconds: 15 * 60,
        timerDisplay: '15:00',
        timerInterval: null,
        confirmedBooking: {
            booking_number: '',
            pin: '',
            downpayment_paid: 0,
            balance_due: 0,
            manage_url: '#'
        },
        copied: false,
        draftRestored: false,

        initWizard() {
            this.loadDraft();

            // Auto-save form inputs whenever they change
            this.$watch('form', () => {
                this.saveDraft();
            });

            // Watch for changes that affect live dynamic pricing quote
            this.$watch('form.class_type', () => this.fetchPricingQuote());
            this.$watch('form.is_certified_diver', () => this.fetchPricingQuote());
            this.$watch('form.participants.length', () => this.fetchPricingQuote());

            // Auto-save step position (for steps 1 to 4)
            this.$watch('currentStep', (val) => {
                if (val < 5) {
                    this.saveDraft();
                }
            });
        },

        saveDraft() {
            try {
                if (this.currentStep >= 5) return;
                const draft = {
                    form: this.form,
                    currentStep: this.currentStep,
                    savedAt: new Date().toISOString()
                };
                localStorage.setItem('camp_freedive_booking_draft', JSON.stringify(draft));
            } catch (e) {
                console.warn('Booking draft save error:', e);
            }
        },

        loadDraft() {
            try {
                const saved = localStorage.getItem('camp_freedive_booking_draft');
                if (saved) {
                    const data = JSON.parse(saved);
                    if (data && data.form) {
                        const hasDetails = data.form.contact_name || 
                                           data.form.contact_email || 
                                           data.form.contact_phone || 
                                           data.form.start_date ||
                                           (data.form.participants && data.form.participants[0] && data.form.participants[0].name);

                        this.form = {
                            ...this.form,
                            ...data.form
                        };

                        if (!Array.isArray(this.form.participants) || this.form.participants.length === 0) {
                            this.form.participants = [
                                { name: '', age: '', health_condition: '', swimmer_status: 'non_swimmer' }
                            ];
                        }

                        if (this.form.start_date) {
                            this.onStartDateChange();
                        }

                        if (data.currentStep && data.currentStep >= 1 && data.currentStep < 5) {
                            this.currentStep = data.currentStep;
                        }

                        if (hasDetails) {
                            this.draftRestored = true;
                        }
                    }
                }
            } catch (e) {
                console.warn('Booking draft load error:', e);
            }
        },

        clearDraft() {
            try {
                localStorage.removeItem('camp_freedive_booking_draft');
            } catch (e) {}
        },

        resetForm() {
            this.clearDraft();
            this.draftRestored = false;
            this.currentStep = 1;
            this.form = {
                class_type: config.initialClass || 'discovery',
                is_certified_diver: false,
                discovery_completed_ack: false,
                start_date: '',
                end_date: '',
                participants: [
                    { name: '', age: '', health_condition: '', swimmer_status: 'non_swimmer' }
                ],
                contact_name: '',
                contact_email: '',
                contact_phone: '',
                contact_facebook: '',
                pickup_option: 'carpool',
                pickup_location: config.pickupPoints[0] ? config.pickupPoints[0].name : '',
                boat_dive: false,
                confirmation_ack: false,
                payment_method: 'gcash'
            };
            this.forecast = null;
            this.errorMessage = '';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        onStartDateChange() {
            if (!this.form.start_date) {
                this.form.end_date = '';
                this.forecast = null;
                this.pricingQuote = null;
                return;
            }
            const start = new Date(this.form.start_date);
            const end = new Date(start);
            end.setDate(start.getDate() + 1);
            this.form.end_date = end.toISOString().split('T')[0];
            this.fetchWeather();
            this.fetchPricingQuote();
        },

        calculateBasePriceUnit() {
            let price = 4250;
            if (this.form.class_type === 'fundive') {
                price = this.form.is_certified_diver ? 2500 : 3300;
            } else if (this.form.class_type === 'refinement') {
                price = 4100;
            }
            return price;
        },

        async fetchPricingQuote() {
            if (!this.form.start_date) return;
            try {
                const response = await fetch(config.pricingQuoteUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken
                    },
                    body: JSON.stringify({
                        class_type: this.form.class_type,
                        start_date: this.form.start_date,
                        is_certified_diver: this.form.is_certified_diver,
                        participants_count: this.form.participants.length
                    })
                });
                const data = await response.json();
                if (data && data.adjusted_price_per_pax) {
                    this.pricingQuote = data;
                }
            } catch (e) {
                console.warn('Pricing quote fetch error:', e);
            }
        },

        async fetchWeather() {
            this.weatherLoading = true;
            this.weatherProgress = 15;
            
            if (this.weatherProgressTimer) clearInterval(this.weatherProgressTimer);
            if (this.weatherTipTimer) clearInterval(this.weatherTipTimer);

            this.weatherProgressTimer = setInterval(() => {
                if (this.weatherProgress < 90) {
                    this.weatherProgress += Math.floor(Math.random() * 15) + 5;
                    if (this.weatherProgress > 90) this.weatherProgress = 90;
                }
            }, 200);

            this.weatherTipTimer = setInterval(() => {
                this.weatherTipIndex = (this.weatherTipIndex + 1) % this.weatherTips.length;
            }, 2500);

            try {
                const response = await fetch(config.checkWeatherUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken
                    },
                    body: JSON.stringify({
                        start_date: this.form.start_date,
                        end_date: this.form.end_date
                    })
                });
                const data = await response.json();
                this.weatherProgress = 100;
                this.forecast = data;
            } catch (e) {
                console.error(e);
            } finally {
                clearInterval(this.weatherProgressTimer);
                clearInterval(this.weatherTipTimer);
                setTimeout(() => {
                    this.weatherLoading = false;
                }, 300);
            }
        },

        addParticipant() {
            this.form.participants.push({
                name: '',
                age: '',
                health_condition: '',
                swimmer_status: 'non_swimmer'
            });
        },

        removeParticipant(index) {
            if (this.form.participants.length > 1) {
                this.form.participants.splice(index, 1);
            }
        },

        calculateSubtotal() {
            const count = this.form.participants.length;
            if (this.pricingQuote && this.pricingQuote.adjusted_price_per_pax) {
                return this.pricingQuote.adjusted_price_per_pax * count;
            }
            return this.calculateBasePriceUnit() * count;
        },

        calculateCarpoolFee() {
            if (this.form.pickup_option === 'carpool') {
                return 1200 * this.form.participants.length;
            }
            return 0;
        },

        calculateTotal() {
            const count = this.form.participants.length;
            const subtotal = this.calculateSubtotal();
            const carpool = this.calculateCarpoolFee();
            const boat = this.form.boat_dive ? (600 * count) : 0;
            const lgu = 300 * count;
            const env = 50 * count;
            return subtotal + carpool + boat + lgu + env;
        },

        calculateDownpayment() {
            const total = this.calculateTotal();
            const count = this.form.participants.length;
            const dpPerHead = (this.form.pickup_option === 'carpool') ? 3000 : 2000;
            const totalDp = dpPerHead * count;
            return Math.min(totalDp, total);
        },

        formatNumber(num) {
            return (num || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        touchedStep3: false,

        validateName(name) {
            if (!name) return false;
            const trimmed = name.toString().trim();
            return trimmed.length >= 2 && /^[a-zA-Z\s\.\'\-]+$/.test(trimmed);
        },

        validateAge(age) {
            if (age === '' || age === null || age === undefined) return false;
            const a = parseInt(age, 10);
            return !isNaN(a) && a >= 8 && a <= 85;
        },

        validateEmail(email) {
            if (!email) return false;
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.toString().trim());
        },

        validatePhone(phone) {
            if (!phone) return false;
            const clean = phone.toString().replace(/[\s\-]/g, '');
            return /^(\+?63|0)9\d{9}$/.test(clean);
        },

        nextStep() {
            this.errorMessage = '';

            if (this.currentStep === 1) {
                if (!this.form.class_type) {
                    this.errorMessage = "Please select a freediving class to proceed.";
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }
                if (this.form.class_type === 'fundive' && !this.form.discovery_completed_ack && !this.form.is_certified_diver) {
                    this.errorMessage = "Fundive requires self-declaration of prior Discovery Class completion or diver certification.";
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }
            } else if (this.currentStep === 2) {
                if (!this.form.start_date) {
                    this.errorMessage = "Please select your preferred 2D1N trip start date to continue.";
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }
                if (this.weatherLoading) {
                    this.errorMessage = "Please wait while we evaluate the marine telemetry & weather safety for your dates.";
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }
                if (this.forecast && !this.forecast.is_bookable) {
                    this.errorMessage = "The selected dive date has a Critical Risk. Please choose an alternate safe date.";
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }
            } else if (this.currentStep === 3) {
                this.touchedStep3 = true;
                for (let i = 0; i < this.form.participants.length; i++) {
                    const p = this.form.participants[i];
                    if (!p.name || !p.name.trim()) {
                        this.errorMessage = `Please enter the Full Name for Participant #${i + 1}.`;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }
                    if (!this.validateName(p.name)) {
                        this.errorMessage = `Participant #${i + 1} name must contain letters only (minimum 2 characters).`;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }
                    if (!p.age) {
                        this.errorMessage = `Please enter the Age for Participant #${i + 1}.`;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }
                    if (!this.validateAge(p.age)) {
                        this.errorMessage = `Participant #${i + 1} age must be between 8 and 85 years old.`;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        return;
                    }
                }
                if (!this.form.contact_name || !this.form.contact_name.trim()) {
                    this.errorMessage = "Please enter the Primary Contact Name.";
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }
                if (!this.validateName(this.form.contact_name)) {
                    this.errorMessage = "Primary Contact Name must contain letters only.";
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }
                if (!this.form.contact_email || !this.form.contact_email.trim()) {
                    this.errorMessage = "Please enter the Primary Contact Email Address.";
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }
                if (!this.validateEmail(this.form.contact_email)) {
                    this.errorMessage = "Please provide a valid email address format (e.g. name@example.com).";
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }
                if (!this.form.contact_phone || !this.form.contact_phone.trim()) {
                    this.errorMessage = "Please enter the Primary Mobile Phone Number.";
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }
                if (!this.validatePhone(this.form.contact_phone)) {
                    this.errorMessage = "Please enter a valid 11-digit Philippine Mobile Number (e.g. 09171234567 or +639171234567).";
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }
                if (!this.form.confirmation_ack) {
                    this.errorMessage = "Please check the confirmation box acknowledging that all provided details are accurate.";
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }
                this.startPaymentTimer();
            }

            this.currentStep++;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        prevStep() {
            this.errorMessage = '';
            if (this.currentStep > 1) {
                this.currentStep--;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        startPaymentTimer() {
            if (this.timerInterval) clearInterval(this.timerInterval);
            this.timerSeconds = 15 * 60;
            this.timerInterval = setInterval(() => {
                this.timerSeconds--;
                const m = Math.floor(this.timerSeconds / 60);
                const s = this.timerSeconds % 60;
                this.timerDisplay = `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;

                if (this.timerSeconds <= 0) {
                    clearInterval(this.timerInterval);
                    alert("Your 15-minute payment session has expired. To ensure slot fairness, your session will now restart.");
                    window.location.reload();
                }
            }, 1000);
        },

        proceedToPaymentDetails() {
            this.errorMessage = '';
            if (!this.cardForm.name && this.form.contact_name) {
                this.cardForm.name = this.form.contact_name;
            }
            if (!this.ewalletForm.phone && this.form.contact_phone) {
                this.ewalletForm.phone = this.form.contact_phone.replace(/^0/, '');
            }
            this.paymentSubStep = 'details';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        validateAndProcessPayment() {
            this.errorMessage = '';

            if (this.form.payment_method === 'gcash') {
                if (!this.ewalletForm.phone) {
                    this.errorMessage = "Please enter your registered GCash mobile number.";
                    return;
                }
            } else if (['bpi', 'dob', 'bpi_bank_transfer'].includes(this.form.payment_method)) {
                if (!this.bankForm.reference_number) {
                    this.errorMessage = "Please enter the Bank Reference Number / Transaction ID for verification.";
                    return;
                }
            }

            this.processPayment(false);
        },

        async processPayment(instantSimulation = false, hostedCheckout = false) {
            this.submittingPayment = true;
            this.errorMessage = '';

            try {
                const payload = {
                    ...this.form,
                    instant_simulation: instantSimulation,
                    hosted_checkout: hostedCheckout,
                    payment_details: {
                        phone: this.ewalletForm.phone,
                        otp: this.ewalletForm.otp,
                        reference_number: this.bankForm.reference_number
                    }
                };

                const response = await fetch(config.storeBookingUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken
                    },
                    body: JSON.stringify(payload)
                });

                let data;
                try {
                    data = await response.json();
                } catch (jsonErr) {
                    data = null;
                }

                if (response.ok && data && data.success) {
                    if (this.timerInterval) clearInterval(this.timerInterval);

                    // Clear draft on successful booking completion
                    this.clearDraft();
                    this.draftRestored = false;

                    // If live PayMongo checkout session was created, redirect directly to PayMongo
                    if (data.is_paymongo_redirect && data.checkout_url && !instantSimulation) {
                        window.location.href = data.checkout_url;
                        return;
                    }

                    // Otherwise show confirmation step (Step 5)
                    this.confirmedBooking = data;
                    this.currentStep = 5;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } else {
                    let errMsg = "Payment processing error. Please check your details and try again.";
                    if (data) {
                        if (data.message) {
                            errMsg = data.message;
                        } else if (data.errors) {
                            errMsg = Object.values(data.errors).flat().join(" ");
                        }
                    }
                    this.errorMessage = errMsg;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            } catch (e) {
                console.error("Booking error:", e);
                this.errorMessage = "An unexpected error occurred while communicating with the booking server. Please check your connection and try again.";
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } finally {
                this.submittingPayment = false;
            }
        },

        copyCredentials() {
            const text = `Camp FreedivePH Booking\nBooking #: ${this.confirmedBooking.booking_number}\nPIN: ${this.confirmedBooking.pin}\nManage: ${this.confirmedBooking.manage_url}`;
            navigator.clipboard.writeText(text).then(() => {
                this.copied = true;
                setTimeout(() => this.copied = false, 3000);
            });
        }
    };
}
</script>
@endpush
