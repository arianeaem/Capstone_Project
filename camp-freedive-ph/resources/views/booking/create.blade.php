@extends('layouts.app')

@section('title', 'Book Camp | Camp FreedivePH')
@section('meta_description', 'Book a 2D1N freediving camp in Mabini, Batangas.')
@section('hide_header', true)
@section('hide_footer', true)

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-6 sm:py-10 text-sm" 
     x-data="bookingForm({
         initialClass: '{{ $selectedClass }}',
         pickupPoints: {{ json_encode($pickupPoints) }},
         csrfToken: '{{ csrf_token() }}',
         checkWeatherUrl: '{{ route('api.weather.check') }}',
         pricingQuoteUrl: '{{ route('api.pricing.quote') }}',
         storeBookingUrl: '{{ route('booking.store') }}'
     })"
     x-init="initBooking()">

    <!-- Booking Header -->
    <div class="flex items-center justify-between pb-4 sm:pb-5 mb-5 sm:mb-8 border-b border-[#E5E5EA] gap-2">
        <a href="{{ route('landing') }}" class="flex items-center gap-2 sm:gap-2.5 group min-w-0">
            <img src="{{ asset('images/logo.png') }}" alt="Camp FreedivePH Logo" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-contain bg-white shrink-0">
            <div class="min-w-0">
                <span class="font-extrabold text-sm sm:text-lg tracking-tight text-[#1D1D1F] block leading-none truncate">Camp Freedive<span class="text-[#780000]">PH</span></span>
                <span class="text-[11px] sm:text-sm text-[#6E6E73] font-medium tracking-wider block mt-0.5 truncate">Mabini, Batangas</span>
            </div>
        </a>
        <a href="{{ route('landing') }}" class="text-xs sm:text-sm font-semibold text-[#6E6E73] hover:text-[#780000] flex items-center gap-1 transition-colors shrink-0">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            <span>Exit to Home</span>
        </a>
    </div>

    <!-- Top Stepper Header -->
    <div class="mb-6 sm:mb-10">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h1 class="text-xl sm:text-3xl font-extrabold text-[#1D1D1F]" x-text="stepTitles[currentStep - 1]"></h1>
            </div>
            <div class="text-right shrink-0">
                <span class="text-sm text-[#6E6E73] font-semibold">Step</span>
                <div class="text-xl sm:text-2xl font-extrabold text-[#780000]">
                    <span x-text="currentStep"></span> <span class="text-sm text-[#8E8E93] font-normal">/ 5</span>
                </div>
            </div>
        </div>

        <!-- Stepper Progress Bar -->
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
        <button @click="errorMessage = ''" aria-label="Dismiss error message" class="text-[#991B1B] font-bold text-sm">✕</button>
    </div>

    <!-- Draft Restored Notification Banner -->
    <div x-show="draftRestored" x-cloak class="mb-6 p-3.5 sm:p-4 rounded-xl bg-[#F0FDF4] text-[#166534] text-sm flex items-center justify-between gap-3 shadow-2xs">
        <div class="flex items-center gap-2">
            <span>Your saved booking progress has been automatically restored.</span>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <button type="button" @click="resetForm()" class="font-bold underline text-[#15803D] hover:text-[#166534] text-sm">
                Clear
            </button>
            <button type="button" @click="draftRestored = false" aria-label="Dismiss restored draft notification" class="text-[#166534] font-bold text-sm">✕</button>
        </div>
    </div>

    <!-- Booking Form Container -->
    <div class="bg-white rounded-2xl border border-[#E5E5EA] p-4 sm:p-8 lg:p-10 relative text-sm">

        <!-- Step 1: Select Class -->
        <div x-show="currentStep === 1" x-cloak class="space-y-6">

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
                
                <!-- Left Side: Packages & Fundive Experience Level -->
                <div class="lg:col-span-8 space-y-4 sm:space-y-5">
                    
                    <!-- 3 Square Packages Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4" role="radiogroup" aria-label="Freediving Class Packages">
                        
                        <!-- 1. Discovery Square -->
                        <div @click="form.class_type = 'discovery'" 
                             @keydown.enter.prevent="form.class_type = 'discovery'"
                             @keydown.space.prevent="form.class_type = 'discovery'"
                             tabindex="0"
                             role="radio"
                             :aria-checked="form.class_type === 'discovery'"
                             aria-label="Discovery beginner class, 4,250 php per person"
                             class="p-4 sm:p-5 rounded-2xl bg-white cursor-pointer flex flex-col justify-between items-center text-center relative group min-h-[250px] focus:outline-none">
                            
                            <div class="w-full flex flex-col items-center">
                                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-3 transition-colors duration-150"
                                     :class="form.class_type === 'discovery' ? 'bg-[#780000]/20 ring-2 ring-[#780000]' : 'bg-[#780000]/10'">
                                    <img src="{{ asset('icons/icons8-water-60.png') }}" class="w-10 h-10 object-contain" alt="Discovery Icon">
                                </div>
                                <h3 class="text-base sm:text-lg font-extrabold text-[#1D1D1F]">Discovery</h3>
                                <span class="text-xs font-bold uppercase tracking-wider text-[#780000] mt-1 mb-2">
                                    BEGINNER CLASS
                                </span>
                                <p class="text-xs text-[#6E6E73] leading-relaxed">
                                    Solo joiners and non-swimmers welcome. Theory, pool and 2 open water dives.
                                </p>
                            </div>

                            <div class="mt-4 pt-3 border-t border-black/5 w-full flex flex-col items-center">
                                <span class="text-lg sm:text-xl font-black text-[#780000]">4,250 php</span>
                                <span class="text-xs text-[#6E6E73]">/ person</span>
                            </div>
                        </div>

                        <!-- 2. Fundive Square -->
                        <div @click="form.class_type = 'fundive'" 
                             @keydown.enter.prevent="form.class_type = 'fundive'"
                             @keydown.space.prevent="form.class_type = 'fundive'"
                             tabindex="0"
                             role="radio"
                             :aria-checked="form.class_type === 'fundive'"
                             aria-label="Fundive class, prerequisite discovery class"
                             class="p-4 sm:p-5 rounded-2xl bg-white cursor-pointer flex flex-col justify-between items-center text-center relative group min-h-[250px] focus:outline-none">
                            
                            <div class="w-full flex flex-col items-center">
                                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-3 transition-colors duration-150"
                                     :class="form.class_type === 'fundive' ? 'bg-[#780000]/20 ring-2 ring-[#780000]' : 'bg-[#780000]/10'">
                                    <img src="{{ asset('icons/icons8-snorkel-60.png') }}" class="w-10 h-10 object-contain" alt="Fundive Icon">
                                </div>
                                <h3 class="text-base sm:text-lg font-extrabold text-[#1D1D1F]">Fundive</h3>
                                <span class="text-xs font-bold uppercase tracking-wider text-[#780000] mt-1 mb-2">
                                    PREREQUISITE: DISCOVERY CLASS
                                </span>
                                <p class="text-xs text-[#6E6E73] leading-relaxed">
                                    Explore open water sanctuaries with coach guidance, 2D1N stay and photo coverage.
                                </p>
                            </div>

                            <div class="mt-4 pt-3 border-t border-black/5 w-full flex flex-col items-center">
                                <span class="text-lg sm:text-xl font-black text-[#780000]" x-text="form.is_certified_diver ? '2,500 php' : '3,300 php'"></span>
                                <span class="text-xs text-[#6E6E73]" x-text="form.is_certified_diver ? 'Certified Diver / person' : 'Non-Certified Diver / person'"></span>
                            </div>
                        </div>

                        <!-- 3. Refinement Square -->
                        <div @click="form.class_type = 'refinement'" 
                             @keydown.enter.prevent="form.class_type = 'refinement'"
                             @keydown.space.prevent="form.class_type = 'refinement'"
                             tabindex="0"
                             role="radio"
                             :aria-checked="form.class_type === 'refinement'"
                             aria-label="Skill refinement practice dive, 4,100 php per person"
                             class="p-4 sm:p-5 rounded-2xl bg-white cursor-pointer flex flex-col justify-between items-center text-center relative group min-h-[250px] focus:outline-none">
                            
                            <div class="w-full flex flex-col items-center">
                                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-3 transition-colors duration-150"
                                     :class="form.class_type === 'refinement' ? 'bg-[#780000]/20 ring-2 ring-[#780000]' : 'bg-[#780000]/10'">
                                    <img src="{{ asset('icons/icons8-flippers-60.png') }}" class="w-10 h-10 object-contain" alt="Refinement Icon">
                                </div>
                                <h3 class="text-base sm:text-lg font-extrabold text-[#1D1D1F]">Refinement</h3>
                                <span class="text-xs font-bold uppercase tracking-wider text-[#780000] mt-1 mb-2">
                                    PRACTICE DIVE
                                </span>
                                <p class="text-xs text-[#6E6E73] leading-relaxed">
                                    Practice dive with 2 open water sessions, pool access, coach fee and full meals.
                                </p>
                            </div>

                            <div class="mt-4 pt-3 border-t border-black/5 w-full flex flex-col items-center">
                                <span class="text-lg sm:text-xl font-black text-[#780000]">4,100 php</span>
                                <span class="text-xs text-[#6E6E73]">/ person</span>
                            </div>
                        </div>
                    </div>

                    <!-- Fundive Diver Certification Selection (Visible when Fundive is selected) -->
                    <div x-show="form.class_type === 'fundive'" x-transition x-cloak class="p-4 sm:p-5 bg-amber-100 rounded-2xl space-y-3">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div>
                                <h4 class="font-extrabold text-[#92400E] text-sm sm:text-base flex items-center gap-1.5">
                                    <span>Fundive Experience & Certification Level</span>
                                </h4>
                                <p class="text-xs sm:text-sm text-[#78350F] mt-0.5">
                                    Select whether you hold an official freediving certification or require full safety coach guidance.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                            <!-- Non-Certified -->
                            <label @click="form.is_certified_diver = false"
                                   class="p-3.5 rounded-xl border transition-all cursor-pointer flex items-center justify-between gap-3 bg-white"
                                   :class="!form.is_certified_diver ? 'border-[#780000] bg-[#F8EAEA]/50' : 'border-transparent'">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="is_certified" :value="false" x-model="form.is_certified_diver" class="text-[#780000] focus:ring-[#780000]">
                                    <div>
                                        <span class="font-bold text-[#1D1D1F] block text-sm">Non-Certified Diver</span>
                                        <span class="text-xs text-[#6E6E73] block">Includes dedicated safety coach</span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <strong class="text-sm sm:text-base font-extrabold text-[#780000]">3,300 php</strong>
                                    <span class="text-xs text-[#6E6E73] block">/ person</span>
                                </div>
                            </label>

                            <!-- Certified Diver -->
                            <label @click="form.is_certified_diver = true"
                                   class="p-3.5 rounded-xl border transition-all cursor-pointer flex items-center justify-between gap-3 bg-white"
                                   :class="form.is_certified_diver ? 'border-[#780000] bg-[#F8EAEA]/50' : 'border-transparent'">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="is_certified" :value="true" x-model="form.is_certified_diver" class="text-[#780000] focus:ring-[#780000]">
                                    <div>
                                        <span class="font-bold text-[#1D1D1F] block text-sm">Certified Diver</span>
                                        <span class="text-xs text-[#6E6E73] block">Licensed (no coach fee needed)</span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <strong class="text-sm sm:text-base font-extrabold text-[#780000]">2,500 php</strong>
                                    <span class="text-xs text-[#6E6E73] block">/ person</span>
                                </div>
                            </label>
                        </div>
                    </div>

                </div>

                <!-- Right Side: Inclusions & Exclusions -->
                <div class="lg:col-span-4 space-y-6" x-show="form.class_type" x-transition x-cloak>
                    <!-- Inclusions -->
                    <div class="space-y-2.5">
                        <h4 class="font-extrabold text-sm uppercase tracking-wider text-[#1D1D1F]">Inclusions</h4>
                        
                        <!-- Discovery Inclusions -->
                        <ul x-show="form.class_type === 'discovery'" class="space-y-2 text-sm text-[#3A3A3C] list-disc list-outside ml-4">
                            <li>2 open water dives (2-3 hrs per session)</li>
                            <li>1 pool session (10 ft deep pool access)</li>
                            <li>2D1N shared AC room accommodation</li>
                            <li>Lesson fee and coach fee</li>
                            <li>Safety buoy set up</li>
                            <li>3 full board meals</li>
                            <li>Photos and videos</li>
                            <li>Gears (mask, snorkel, fins, weight belt)</li>
                        </ul>

                        <!-- Fundive Inclusions -->
                        <ul x-show="form.class_type === 'fundive'" class="space-y-2 text-sm text-[#3A3A3C] list-disc list-outside ml-4">
                            <li>2 open water dives (2-3 hrs per session)</li>
                            <li>1 pool session (10 ft deep pool access)</li>
                            <li>2D1N shared AC room accommodation</li>
                            <li x-text="form.is_certified_diver ? 'Safety buoy setup and dive buddy briefing' : 'Safety coach fee included'"></li>
                            <li>Safety buoy set up</li>
                            <li>3 full board meals</li>
                            <li>Photos and videos</li>
                            <li>Gears (mask, snorkel, fins, weight belt)</li>
                        </ul>

                        <!-- Refinement Inclusions -->
                        <ul x-show="form.class_type === 'refinement'" class="space-y-2 text-sm text-[#3A3A3C] list-disc list-outside ml-4">
                            <li>2 open water dives (2-3 hrs per session)</li>
                            <li>1 pool session (10 ft deep pool access)</li>
                            <li>2D1N shared AC room accommodation</li>
                            <li>Coach fee (skills drills and form correction)</li>
                            <li>Safety buoy set up</li>
                            <li>3 full board meals</li>
                            <li>Photos and videos</li>
                            <li>Gears (mask, snorkel, fins, weight belt)</li>
                        </ul>
                    </div>

                    <!-- Exclusions -->
                    <div class="space-y-2.5">
                        <h4 class="font-extrabold text-sm uppercase tracking-wider text-[#1D1D1F]">Exclusions</h4>
                        
                        <ul class="space-y-2 text-sm text-[#3A3A3C] list-disc list-outside ml-4">
                            <li>Transportation (We arrange convenient carpool van transfers)</li>
                            <li>Boat dive (Optional sanctuary boat trip +₱600/person)</li>
                            <li>Mabini LGU municipal environmental fee and dive pass</li>
                        </ul>
                    </div>
                </div>

            </div>

        </div>

        <!-- Step 2: Select Dates -->
        <div x-show="currentStep === 2" x-cloak class="space-y-6">

            <!-- Dates and Safety Evaluation Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">

                <!-- Calendar and Date Selection -->
                <div class="lg:col-span-7 space-y-5">

                    <!-- Selected Dates Overview -->
                    <div class="rounded-2xl border border-[#E5E5EA] bg-white p-3.5 sm:p-5 transition-all shadow-2xs">
                        <div class="grid grid-cols-2 divide-x divide-[#E5E5EA]">
                            
                            <!-- Depart (Day 1) -->
                            <div class="pr-2.5 sm:pr-4">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between items-start gap-1 sm:gap-2">
                                    <span class="text-xs sm:text-sm font-extrabold uppercase tracking-wider text-[#6E6E73]">Depart</span>
                                    <span class="text-xs font-bold px-2 py-0.5 rounded bg-[#F8EAEA] text-[#780000] whitespace-nowrap">Day 1</span>
                                </div>
                                <div class="mt-1.5">
                                    <template x-if="form.start_date">
                                        <div>
                                            <div class="text-sm sm:text-lg md:text-xl font-black text-[#1D1D1F] tracking-tight truncate" x-text="formatDateDisplay(form.start_date)"></div>
                                            <div class="text-xs sm:text-sm font-semibold text-[#780000] mt-0.5 truncate" x-text="formatDateDayOfWeek(form.start_date)"></div>
                                        </div>
                                    </template>
                                    <template x-if="!form.start_date">
                                        <div class="text-xs sm:text-sm font-medium text-[#8E8E93] italic py-1">
                                            Select start date below
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Return (Day 2) -->
                            <div class="pl-2.5 sm:pl-4">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between items-start gap-1 sm:gap-2">
                                    <span class="text-xs sm:text-sm font-extrabold uppercase tracking-wider text-[#6E6E73]">Return</span>
                                    <span class="text-xs font-bold px-2 py-0.5 rounded bg-[#EBF7F8] text-[#00C3D0] whitespace-nowrap">Day 2</span>
                                </div>
                                <div class="mt-1.5">
                                    <template x-if="form.end_date">
                                        <div>
                                            <div class="text-sm sm:text-lg md:text-xl font-black text-[#1D1D1F] tracking-tight truncate" x-text="formatDateDisplay(form.end_date)"></div>
                                            <div class="text-xs sm:text-sm font-semibold text-[#00C3D0] mt-0.5 truncate" x-text="formatDateDayOfWeek(form.end_date)"></div>
                                        </div>
                                    </template>
                                    <template x-if="!form.end_date">
                                        <div class="text-xs sm:text-sm font-medium text-[#8E8E93] italic py-1">
                                            Next day return
                                        </div>
                                    </template>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Interactive Dual-Month Calendar -->
                    <div class="bg-white rounded-2xl p-4 sm:p-7 space-y-6 shadow-2xs border border-[#E5E5EA]">
                        
                        <!-- Months Container (Single month on mobile with arrows, dual months on desktop) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 items-start">
                            
                            <!-- First Month -->
                            <div class="space-y-4">
                                <!-- Header for Month 1 with Prev/Next Arrows -->
                                <div class="flex items-center justify-between h-9">
                                    <button type="button" 
                                            @click="prevMonth()" 
                                            :disabled="!canGoPrev()"
                                            class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-[#F2F2F7] disabled:opacity-20 disabled:cursor-not-allowed transition-all text-[#1D1D1F] shrink-0"
                                            title="Previous Month"
                                            aria-label="Previous Month">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                                    </button>

                                    <div class="font-extrabold text-sm sm:text-base text-[#1D1D1F] text-center flex-1">
                                        <span x-text="getMonthName(month1Month) + ' ' + month1Year"></span>
                                    </div>

                                    <!-- Month Navigation Controls -->
                                    <div class="w-8 hidden md:block shrink-0"></div>
                                    <button type="button" 
                                            @click="nextMonth()" 
                                            class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-[#F2F2F7] transition-all text-[#1D1D1F] md:hidden shrink-0"
                                            title="Next Month"
                                            aria-label="Next Month">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
                                    </button>
                                </div>
                                
                                <!-- Weekday Headers -->
                                <div class="grid grid-cols-7 text-center text-xs sm:text-sm font-semibold text-[#6E6E73] py-1">
                                    <span class="text-[#780000] font-bold">Sun</span>
                                    <span>Mon</span>
                                    <span>Tue</span>
                                    <span>Wed</span>
                                    <span>Thu</span>
                                    <span>Fri</span>
                                    <span>Sat</span>
                                </div>

                                <!-- Month Calendar Days -->
                                <div class="grid grid-cols-7 gap-y-1.5 sm:gap-y-2 text-center text-xs sm:text-sm">
                                    <template x-for="(dObj, idx) in getMonthDays(month1Year, month1Month)" :key="'m1-' + idx">
                                        <div class="h-8 sm:h-9 flex items-center justify-center relative">
                                            <template x-if="dObj.isBlank">
                                                <span class="w-full h-full"></span>
                                            </template>
                                            <template x-if="!dObj.isBlank">
                                                <button type="button"
                                                        @click="!dObj.isDisabled && selectDate(dObj.dateStr)"
                                                        :disabled="dObj.isDisabled"
                                                        :aria-label="dObj.dateStr + (dObj.isDisabled ? ' (Unavailable)' : '')"
                                                        :aria-pressed="dObj.dateStr === form.start_date"
                                                        class="w-7 h-7 sm:w-9 sm:h-9 rounded-full flex items-center justify-center font-medium text-xs sm:text-sm transition-all relative z-10 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]"
                                                        :class="{
                                                            'bg-[#780000] text-white font-bold ring-2 ring-[#780000]/20': dObj.dateStr === form.start_date,
                                                            'bg-[#00C3D0] text-white font-bold ring-2 ring-[#00C3D0]/20': dObj.dateStr === form.end_date,
                                                            'text-gray-300 cursor-not-allowed': dObj.isDisabled,
                                                            'hover:bg-[#F2F2F7] hover:text-[#780000] cursor-pointer text-[#1D1D1F]': !dObj.isDisabled && dObj.dateStr !== form.start_date && dObj.dateStr !== form.end_date,
                                                            'text-[#780000] font-semibold': dObj.isSunday && !dObj.isDisabled && dObj.dateStr !== form.start_date && dObj.dateStr !== form.end_date
                                                        }"
                                                        x-text="dObj.day">
                                                </button>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Second Month (Visible on md+ screens for dual-calendar experience) -->
                            <div class="space-y-4 hidden md:block">
                                <!-- Header for Month 2 with Next Arrow -->
                                <div class="flex items-center justify-between h-9">
                                    <div class="w-8 shrink-0"></div>

                                    <div class="font-extrabold text-sm sm:text-base text-[#1D1D1F] text-center flex-1">
                                        <span x-text="getMonthName(month2Month) + ' ' + month2Year"></span>
                                    </div>

                                    <button type="button" 
                                            @click="nextMonth()" 
                                            class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-[#F2F2F7] transition-all text-[#1D1D1F] shrink-0"
                                            title="Next Month"
                                            aria-label="Next Month">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
                                    </button>
                                </div>
                                
                                <!-- Weekday Headers -->
                                <div class="grid grid-cols-7 text-center text-xs sm:text-sm font-semibold text-[#6E6E73] py-1">
                                    <span class="text-[#780000] font-bold">Sun</span>
                                    <span>Mon</span>
                                    <span>Tue</span>
                                    <span>Wed</span>
                                    <span>Thu</span>
                                    <span>Fri</span>
                                    <span>Sat</span>
                                </div>

                                <!-- Month Calendar Days -->
                                <div class="grid grid-cols-7 gap-y-1.5 sm:gap-y-2 text-center text-xs sm:text-sm">
                                    <template x-for="(dObj, idx) in getMonthDays(month2Year, month2Month)" :key="'m2-' + idx">
                                        <div class="h-8 sm:h-9 flex items-center justify-center relative">
                                            <template x-if="dObj.isBlank">
                                                <span class="w-full h-full"></span>
                                            </template>
                                            <template x-if="!dObj.isBlank">
                                                <button type="button"
                                                        @click="!dObj.isDisabled && selectDate(dObj.dateStr)"
                                                        :disabled="dObj.isDisabled"
                                                        :aria-label="dObj.dateStr + (dObj.isDisabled ? ' (Unavailable)' : '')"
                                                        :aria-pressed="dObj.dateStr === form.start_date"
                                                        class="w-7 h-7 sm:w-9 sm:h-9 rounded-full flex items-center justify-center font-medium text-xs sm:text-sm transition-all relative z-10 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]"
                                                        :class="{
                                                            'bg-[#780000] text-white font-bold ring-2 ring-[#780000]/20': dObj.dateStr === form.start_date,
                                                            'bg-[#00C3D0] text-white font-bold ring-2 ring-[#00C3D0]/20': dObj.dateStr === form.end_date,
                                                            'text-gray-300 cursor-not-allowed': dObj.isDisabled,
                                                            'hover:bg-[#F2F2F7] hover:text-[#780000] cursor-pointer text-[#1D1D1F]': !dObj.isDisabled && dObj.dateStr !== form.start_date && dObj.dateStr !== form.end_date,
                                                            'text-[#780000] font-semibold': dObj.isSunday && !dObj.isDisabled && dObj.dateStr !== form.start_date && dObj.dateStr !== form.end_date
                                                        }"
                                                        x-text="dObj.day">
                                                </button>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

                <!-- Dive Safety Evaluation -->
                <div class="lg:col-span-5 space-y-4 lg:sticky lg:top-8">
                    <div class="bg-white rounded-2xl border border-[#E5E5EA] p-4 sm:p-6 shadow-2xs space-y-4">
                        
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5 sm:gap-2">
                                <h3 class="font-black text-sm sm:text-base text-[#1D1D1F]">Dive Safety Evaluation</h3>
                                
                                <!-- About Forecast Icon with Tooltip -->
                                <div class="relative group inline-flex items-center">
                                    <button type="button" aria-label="About Forecast" class="p-1 rounded-lg text-[#6E6E73] hover:text-[#1D1D1F] transition-colors focus:outline-none flex items-center justify-center cursor-pointer">
                                        <img src="{{ asset('icons/icons8-exclamation-mark-60.png') }}" class="w-4 h-4 sm:w-5 sm:h-5 shrink-0 object-contain" alt="About Forecast">
                                    </button>
                                    <div class="absolute left-0 top-full mt-2 w-64 sm:w-72 max-w-[calc(100vw-3rem)] p-3.5 bg-[#1D1D1F] text-white text-xs rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 pointer-events-none space-y-1.5 leading-relaxed">
                                        <div class="font-bold flex items-center gap-1.5 text-[#00C3D0]">
                                            <img src="{{ asset('icons/icons8-exclamation-mark-60.png') }}" class="w-4 h-4 shrink-0 object-contain brightness-0 invert" alt="Weather Note">
                                            <span>Weather & Sea Conditions Note</span>
                                        </div>
                                        <p class="text-xs text-gray-200">
                                            Safety ratings shown are automated predictions based on coastal forecast models. Actual water conditions can change naturally, and our safety team continuously checks the water before every dive.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <template x-if="weatherLoading">
                                <span class="text-xs sm:text-sm text-[#00C3D0] font-bold flex items-center gap-1 shrink-0">
                                    <svg class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    Checking...
                                </span>
                            </template>
                        </div>

                        <!-- Forecast Loading State -->
                        <div x-show="weatherLoading" x-cloak class="space-y-4 py-2">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-xs sm:text-sm">
                                    <span class="font-semibold text-[#1D1D1F]">Checking weather & marine conditions...</span>
                                    <span class="font-mono font-bold text-[#00C3D0]" x-text="weatherProgress + '%'"></span>
                                </div>
                                <div class="w-full bg-[#E5E5EA] h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-gradient-to-r from-[#00C3D0] to-[#00C3D0] h-full transition-all duration-200 rounded-full"
                                         :style="'width: ' + weatherProgress + '%'"></div>
                                </div>
                            </div>

                            <!-- Weather Assessment Loading State -->
                            <div class="space-y-2.5 pt-1 animate-pulse">
                                <div class="p-3.5 sm:p-4 rounded-xl bg-[#F2F2F7] space-y-2">
                                    <div class="h-3.5 w-24 bg-[#E5E5EA] rounded"></div>
                                    <div class="h-3 w-40 bg-[#E5E5EA] rounded"></div>
                                </div>
                                <div class="p-3.5 sm:p-4 rounded-xl bg-[#F2F2F7] space-y-2">
                                    <div class="h-3.5 w-24 bg-[#E5E5EA] rounded"></div>
                                    <div class="h-3 w-40 bg-[#E5E5EA] rounded"></div>
                                </div>
                            </div>

                            <!-- Rotating Tip -->
                            <div class="p-3 rounded-xl bg-[#F2F2F7] flex items-center gap-2.5 text-xs sm:text-sm text-[#6E6E73]">
                                <div class="flex-1 min-w-0">
                                    <span class="font-bold text-[#1D1D1F]" x-text="currentTip.title + ': '"></span>
                                    <span x-text="currentTip.text"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Weather Assessment Results -->
                        <template x-if="forecast && !weatherLoading && !forecast.is_benchmark">
                            <div class="space-y-4">
                                <!-- Overall Assessment (5 Lines Indicator) -->
                                <div class="space-y-2 pb-1">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div class="flex items-center gap-2.5 sm:gap-3 flex-wrap">
                                            <span class="text-base sm:text-xl font-black uppercase tracking-wide"
                                                  :class="getSafetyTextClass(forecast.overall_classification)"
                                                  x-text="forecast.overall_classification"></span>

                                            <!-- 5 Lines Indicator -->
                                            <div class="flex items-center gap-1 sm:gap-1.5">
                                                <template x-for="i in 5" :key="i">
                                                    <div class="h-1.5 w-4 sm:w-7 rounded-full transition-all duration-300"
                                                         :class="i <= getSafetyScore(forecast.overall_classification) ? getSafetyBarClass(forecast.overall_classification) : 'bg-[#E5E5EA]'"></div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-xs sm:text-sm text-[#6E6E73] leading-relaxed" x-text="forecast.description"></p>
                                </div>

                                <!-- Day 1 & Day 2 Breakdown -->
                                <template x-if="forecast.day1 && forecast.day2">
                                    <div class="space-y-3 pt-2">
                                        <!-- Day 1 -->
                                        <div class="relative pl-3.5 sm:pl-4 py-0.5 space-y-1 text-xs sm:text-sm">
                                            <div class="absolute left-0 top-0.5 bottom-0.5 w-1 sm:w-1.5 rounded-full transition-colors duration-200"
                                                 :class="getSafetyBarClass(forecast.day1.classification)"></div>
                                            <div class="flex items-center justify-between">
                                                <span class="font-extrabold text-[#1D1D1F] text-xs sm:text-sm uppercase tracking-wider">Day 1</span>
                                            </div>
                                            <div class="text-[#6E6E73] text-xs sm:text-sm flex flex-wrap items-center justify-between gap-1">
                                                <span class="font-bold text-[#1D1D1F]" x-text="forecast.day1.date"></span>
                                                <span class="shrink-0">Worst Hour: <strong class="text-[#1D1D1F]" x-text="forecast.day1.worst_hour"></strong></span>
                                            </div>
                                            <p class="text-xs sm:text-sm text-[#6E6E73] pt-0.5 leading-relaxed" x-text="forecast.day1.recommended_action"></p>
                                        </div>

                                        <!-- Day 2 -->
                                        <div class="relative pl-3.5 sm:pl-4 py-0.5 space-y-1 text-xs sm:text-sm">
                                            <div class="absolute left-0 top-0.5 bottom-0.5 w-1 sm:w-1.5 rounded-full transition-colors duration-200"
                                                 :class="getSafetyBarClass(forecast.day2.classification)"></div>
                                            <div class="flex items-center justify-between">
                                                <span class="font-extrabold text-[#1D1D1F] text-xs sm:text-sm uppercase tracking-wider">Day 2</span>
                                            </div>
                                            <div class="text-[#6E6E73] text-xs sm:text-sm flex flex-wrap items-center justify-between gap-1">
                                                <span class="font-bold text-[#1D1D1F]" x-text="forecast.day2.date"></span>
                                                <span class="shrink-0">Worst Hour: <strong class="text-[#1D1D1F]" x-text="forecast.day2.worst_hour"></strong></span>
                                            </div>
                                            <p class="text-xs sm:text-sm text-[#6E6E73] pt-0.5 leading-relaxed" x-text="forecast.day2.recommended_action"></p>
                                        </div>
                                    </div>
                                </template>

                            </div>
                        </template>

                        <!-- Empty State: Dates Selected but Evaluation Not Available -->
                        <template x-if="form.start_date && !weatherLoading && (!forecast || forecast.is_benchmark)">
                            <div class="py-1 text-left">
                                <p class="text-xs sm:text-sm text-[#6E6E73] leading-relaxed">
                                    Evaluation for this dates are not available but you can still proceed. The camp will just update you.
                                </p>
                            </div>
                        </template>

                        <!-- Empty State: No Date Selected Yet -->
                        <template x-if="!form.start_date && !weatherLoading">
                            <div class="py-1 text-left">
                                <p class="text-xs sm:text-sm text-[#8E8E93] italic leading-relaxed">
                                    Select dates on the calendar to view safety evaluation.
                                </p>
                            </div>
                        </template>

                    </div>
                </div>

            </div>
        </div>

        <!-- Step 3: Booking Details -->
        <div x-show="currentStep === 3" x-cloak class="space-y-6">

            <!-- Form Inputs and Summary Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">

                <!-- Form Inputs -->
                <div class="lg:col-span-7 space-y-6">

                    <!-- Section 1: Participants -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between pb-1 gap-2 flex-wrap sm:flex-nowrap">
                            <h4 class="text-base sm:text-lg font-bold text-[#1D1D1F]">1. Participants</h4>
                            <button type="button" 
                                    @click="addParticipant()" 
                                    class="px-3 py-1.5 rounded-xl border border-[#780000] text-[#780000] font-bold text-xs sm:text-sm bg-[#F8EAEA]/30 hover:bg-[#F8EAEA] transition-colors flex items-center gap-1.5 shadow-2xs cursor-pointer shrink-0">
                                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                                <span>Add Participant</span>
                            </button>
                        </div>
                        
                        <div class="space-y-4">
                            <template x-for="(participant, index) in form.participants" :key="index">
                                <div class="p-3.5 sm:p-5 rounded-xl bg-[#F2F2F7] relative space-y-3.5 sm:space-y-4 shadow-2xs">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-[#780000] text-sm" x-text="'Participant #' + (index + 1)"></span>
                                        <button type="button" 
                                                x-show="form.participants.length > 1" 
                                                @click="removeParticipant(index)"
                                                class="text-xs sm:text-sm font-semibold text-[#FF3B3C] hover:underline cursor-pointer">
                                            Remove
                                        </button>
                                    </div>

                                    <!-- Participant Name -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block font-bold text-[#1D1D1F] text-xs sm:text-sm mb-1.5">First Name <span class="text-[#780000]">*</span></label>
                                            <input type="text" 
                                                   x-model="participant.first_name" 
                                                   @input="participant.first_name = participant.first_name.replace(/[^a-zA-Z\s\.\'\-]/g, ''); participant.name = (participant.first_name + ' ' + (participant.last_name || '')).trim()"
                                                   placeholder="e.g. Maria" 
                                                   class="w-full px-3.5 py-2.5 rounded-xl border text-sm text-[#1D1D1F] bg-white transition-colors"
                                                   :class="touchedStep3 && !validateName(participant.first_name) ? 'border-[#FF3B3C] bg-red-50/20' : 'border-[#D1D1D6] focus:border-[#780000]'">
                                            <span x-show="touchedStep3 && !validateName(participant.first_name)" class="text-xs text-[#FF3B3C] font-semibold mt-1 block">
                                                Please enter a valid first name (letters only, min 2 chars).
                                            </span>
                                        </div>

                                        <div>
                                            <label class="block font-bold text-[#1D1D1F] text-xs sm:text-sm mb-1.5">Last Name <span class="text-[#780000]">*</span></label>
                                            <input type="text" 
                                                   x-model="participant.last_name" 
                                                   @input="participant.last_name = participant.last_name.replace(/[^a-zA-Z\s\.\'\-]/g, ''); participant.name = ((participant.first_name || '') + ' ' + participant.last_name).trim()"
                                                   placeholder="e.g. Santos" 
                                                   class="w-full px-3.5 py-2.5 rounded-xl border text-sm text-[#1D1D1F] bg-white transition-colors"
                                                   :class="touchedStep3 && !validateName(participant.last_name) ? 'border-[#FF3B3C] bg-red-50/20' : 'border-[#D1D1D6] focus:border-[#780000]'">
                                            <span x-show="touchedStep3 && !validateName(participant.last_name)" class="text-xs text-[#FF3B3C] font-semibold mt-1 block">
                                                Please enter a valid last name (letters only, min 2 chars).
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Age & Swimming Ability -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block font-bold text-[#1D1D1F] text-xs sm:text-sm mb-1.5">Age (8-85 yrs) <span class="text-[#780000]">*</span></label>
                                            <input type="number" 
                                                   x-model="participant.age" 
                                                   min="8" 
                                                   max="85" 
                                                   maxlength="2"
                                                   @input="if(participant.age && participant.age.toString().length > 2) participant.age = parseInt(participant.age.toString().slice(0, 2), 10)"
                                                   placeholder="e.g. 24" 
                                                   class="w-full px-3.5 py-2.5 rounded-xl border text-sm text-[#1D1D1F] bg-white transition-colors"
                                                   :class="touchedStep3 && !validateAge(participant.age) ? 'border-[#FF3B3C] bg-red-50/20' : 'border-[#D1D1D6] focus:border-[#780000]'">
                                            <span x-show="touchedStep3 && !validateAge(participant.age)" class="text-xs text-[#FF3B3C] font-semibold mt-1 block">
                                                Age must be between 8 and 85 years old.
                                            </span>
                                        </div>

                                        <div x-show="form.class_type === 'discovery'">
                                            <label class="block font-bold text-[#1D1D1F] text-xs sm:text-sm mb-1.5">Swimming Ability</label>
                                            <select x-model="participant.swimmer_status" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white">
                                                <option value="non_swimmer">Non-Swimmer</option>
                                                <option value="casual_swimmer">Casual / Beginner Swimmer</option>
                                                <option value="confident_swimmer">Confident Swimmer</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block font-bold text-[#1D1D1F] text-xs sm:text-sm mb-1.5">
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

                    <!-- Contact Details -->
                    <div class="space-y-4 pt-1">
                        <h4 class="text-base sm:text-lg font-bold text-[#1D1D1F] pb-1">2. Contact Information</h4>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-[#1D1D1F] text-xs sm:text-sm mb-1.5">Lead First Name <span class="text-[#780000]">*</span></label>
                                <input type="text" 
                                       x-model="form.contact_first_name" 
                                       @input="form.contact_first_name = form.contact_first_name.replace(/[^a-zA-Z\s\.\'\-]/g, ''); form.contact_name = (form.contact_first_name + ' ' + (form.contact_last_name || '')).trim()"
                                       placeholder="Juan" 
                                       class="w-full px-3.5 py-2.5 rounded-xl border text-sm text-[#1D1D1F] bg-white transition-colors"
                                       :class="touchedStep3 && !validateName(form.contact_first_name) ? 'border-[#FF3B3C] bg-red-50/20' : 'border-[#D1D1D6] focus:border-[#780000]'">
                                <span x-show="touchedStep3 && !validateName(form.contact_first_name)" class="text-xs text-[#FF3B3C] font-semibold mt-1 block">
                                    Please enter a valid first name (min 2 chars).
                                </span>
                            </div>

                            <div>
                                <label class="block font-bold text-[#1D1D1F] text-xs sm:text-sm mb-1.5">Lead Last Name <span class="text-[#780000]">*</span></label>
                                <input type="text" 
                                       x-model="form.contact_last_name" 
                                       @input="form.contact_last_name = form.contact_last_name.replace(/[^a-zA-Z\s\.\'\-]/g, ''); form.contact_name = ((form.contact_first_name || '') + ' ' + form.contact_last_name).trim()"
                                       placeholder="Dela Cruz" 
                                       class="w-full px-3.5 py-2.5 rounded-xl border text-sm text-[#1D1D1F] bg-white transition-colors"
                                       :class="touchedStep3 && !validateName(form.contact_last_name) ? 'border-[#FF3B3C] bg-red-50/20' : 'border-[#D1D1D6] focus:border-[#780000]'">
                                <span x-show="touchedStep3 && !validateName(form.contact_last_name)" class="text-xs text-[#FF3B3C] font-semibold mt-1 block">
                                    Please enter a valid last name (min 2 chars).
                                </span>
                            </div>

                            <div>
                                <label class="block font-bold text-[#1D1D1F] text-xs sm:text-sm mb-1.5">Email Address <span class="text-[#780000]">*</span></label>
                                <input type="email" 
                                       x-model="form.contact_email" 
                                       placeholder="juan@example.com" 
                                       class="w-full px-3.5 py-2.5 rounded-xl border text-sm text-[#1D1D1F] bg-white transition-colors"
                                       :class="touchedStep3 && !validateEmail(form.contact_email) ? 'border-[#FF3B3C] bg-red-50/20' : 'border-[#D1D1D6] focus:border-[#780000]'">
                                <span x-show="touchedStep3 && !validateEmail(form.contact_email)" class="text-xs text-[#FF3B3C] font-semibold mt-1 block">
                                    Please enter a valid email address with @ (e.g. name@example.com).
                                </span>
                            </div>

                            <div>
                                <label class="block font-bold text-[#1D1D1F] text-xs sm:text-sm mb-1.5">Mobile Number (PH) <span class="text-[#780000]">*</span></label>
                                <input type="tel" 
                                       x-model="form.contact_phone" 
                                       @input="form.contact_phone = form.contact_phone.replace(/[^0-9+\s-]/g, '')"
                                       placeholder="0917 123 4567" 
                                       maxlength="16"
                                       class="w-full px-3.5 py-2.5 rounded-xl border text-sm text-[#1D1D1F] bg-white transition-colors"
                                       :class="touchedStep3 && !validatePhone(form.contact_phone) ? 'border-[#FF3B3C] bg-red-50/20' : 'border-[#D1D1D6] focus:border-[#780000]'">
                                <span x-show="touchedStep3 && !validatePhone(form.contact_phone)" class="text-xs text-[#FF3B3C] font-semibold mt-1 block">
                                    Valid 11-digit PH mobile number required (e.g. 09171234567 or +639171234567).
                                </span>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block font-bold text-[#1D1D1F] text-xs sm:text-sm mb-1.5">Facebook Profile Link (Optional)</label>
                                <input type="text" x-model="form.contact_facebook" placeholder="facebook.com/juandelacruz" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] focus:border-[#780000] text-sm text-[#1D1D1F] bg-white">
                            </div>
                        </div>
                    </div>

                    <!-- Transportation and Add-ons -->
                    <div class="space-y-4 pt-1">
                        <h4 class="text-base sm:text-lg font-bold text-[#1D1D1F] pb-1">3. Transportation & Add-ons</h4>
                        
                        <div class="space-y-3">
                            <label class="block font-bold text-[#1D1D1F] text-xs sm:text-sm">Transportation Option:</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" role="radiogroup" aria-label="Transportation Option">
                                <label tabindex="0"
                                       role="radio"
                                       :aria-checked="form.pickup_option === 'carpool'"
                                       @keydown.enter.prevent="form.pickup_option = 'carpool'"
                                       @keydown.space.prevent="form.pickup_option = 'carpool'"
                                       class="p-3.5 rounded-xl border transition-all cursor-pointer flex flex-col justify-between select-none focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]"
                                       :class="form.pickup_option === 'carpool' ? 'border-[#780000] bg-[#F8EAEA]/40' : 'border-[#E5E5EA] bg-white hover:border-[#D1D1D6]'">
                                    <input type="radio" name="pickup_opt" value="carpool" x-model="form.pickup_option" class="hidden">
                                    <div class="space-y-1">
                                        <div class="flex items-center justify-between">
                                             <span class="font-extrabold text-sm text-[#1D1D1F]">Manila Carpool Van</span>
                                            <span x-show="form.pickup_option === 'carpool'" class="w-2.5 h-2.5 rounded-full bg-[#780000]"></span>
                                        </div>
                                        <span class="font-bold text-[#780000] text-xs sm:text-sm block">₱1,200 / person</span>
                                        <span class="text-xs sm:text-sm text-[#780000] font-semibold block">(DP: ₱3,000 / head)</span>
                                    </div>
                                </label>

                                <label tabindex="0"
                                       role="radio"
                                       :aria-checked="form.pickup_option === 'own'"
                                       @keydown.enter.prevent="form.pickup_option = 'own'"
                                       @keydown.space.prevent="form.pickup_option = 'own'"
                                       class="p-3.5 rounded-xl border transition-all cursor-pointer flex flex-col justify-between select-none focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]"
                                       :class="form.pickup_option === 'own' ? 'border-[#780000] bg-[#F8EAEA]/40' : 'border-[#E5E5EA] bg-white hover:border-[#D1D1D6]'">
                                    <input type="radio" name="pickup_opt" value="own" x-model="form.pickup_option" class="hidden">
                                    <div class="space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-extrabold text-sm text-[#1D1D1F]">Own Vehicle / Commute</span>
                                            <span x-show="form.pickup_option === 'own'" class="w-2.5 h-2.5 rounded-full bg-[#780000]"></span>
                                        </div>
                                        <span class="font-bold text-[#1D1D1F] text-xs sm:text-sm block">₱0 (Self-arranged)</span>
                                        <span class="text-xs sm:text-sm text-[#6E6E73] font-semibold block">(DP: ₱2,000 / head)</span>
                                    </div>
                                </label>
                            </div>

                            <!-- Pickup Hub Selection with Times -->
                            <div x-show="form.pickup_option === 'carpool'" x-cloak class="pt-1">
                                <label class="block font-bold text-[#1D1D1F] text-xs sm:text-sm mb-1.5">Carpool Pickup Hub & Schedule: <span class="text-[#780000]">*</span></label>
                                <select x-model="form.pickup_location" 
                                        class="w-full px-3.5 py-2.5 rounded-xl border text-sm text-[#1D1D1F] bg-white font-medium transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#780000]"
                                        :class="touchedStep3 && form.pickup_option === 'carpool' && !form.pickup_location ? 'border-[#FF3B3C] bg-red-50/20' : 'border-[#D1D1D6] focus:border-[#780000]'">
                                    <option value="" disabled selected>-- Select Carpool Pickup Hub & Schedule --</option>
                                    <template x-for="p in pickupPoints" :key="p.id">
                                        <option :value="p.name" x-text="p.name"></option>
                                    </template>
                                </select>
                                <span x-show="touchedStep3 && form.pickup_option === 'carpool' && !form.pickup_location" class="text-xs text-[#FF3B3C] font-semibold mt-1 block">
                                    Please select your preferred Carpool Pickup Hub to continue.
                                </span>
                            </div>
                        </div>

                        <!-- Optional Boat Dive -->
                        <div class="pt-1">
                            <label tabindex="0"
                                   role="checkbox"
                                   :aria-checked="form.boat_dive"
                                   @keydown.enter.prevent="form.boat_dive = !form.boat_dive"
                                   @keydown.space.prevent="form.boat_dive = !form.boat_dive"
                                   class="p-3.5 rounded-xl border transition-all cursor-pointer flex items-start justify-between gap-3 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#00C3D0]"
                                   :class="form.boat_dive ? 'border-[#00C3D0] bg-[#E0F9FB]/30' : 'border-[#E5E5EA] bg-white'">
                                <div class="flex items-start gap-2.5 min-w-0">
                                    <input type="checkbox" x-model="form.boat_dive" class="w-4 h-4 rounded text-[#00C3D0] focus:ring-[#00C3D0] mt-0.5 shrink-0 cursor-pointer">
                                    <div class="min-w-0">
                                        <span class="font-bold text-sm text-[#1D1D1F] block">Boat Dive (Optional)</span>
                                        <span class="text-xs sm:text-sm text-[#6E6E73] block leading-snug">Boat ride to deeper marine sanctuaries.</span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="font-extrabold text-[#00C3D0] text-xs sm:text-sm">+₱600</span>
                                    <span class="text-[11px] sm:text-xs text-[#6E6E73] block">/ person</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Accuracy & Prerequisite Verification -->
                    <div class="pt-1">
                        <div class="p-3.5 sm:p-4 rounded-xl bg-[#F8EAEA] border border-[#F1D5D5]">
                            <label tabindex="0"
                                   role="checkbox"
                                   :aria-checked="form.confirmation_ack"
                                   @keydown.enter.prevent="form.confirmation_ack = !form.confirmation_ack"
                                   @keydown.space.prevent="form.confirmation_ack = !form.confirmation_ack"
                                   class="flex items-start gap-2.5 cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000] rounded-lg">
                                <input type="checkbox" x-model="form.confirmation_ack" class="w-4 h-4 rounded text-[#780000] focus:ring-[#780000] mt-0.5 shrink-0 cursor-pointer">
                                <span class="font-bold text-[#780000] text-xs sm:text-sm leading-relaxed">
                                    <span x-show="form.class_type === 'fundive' || form.class_type === 'refinement'">
                                        I confirm that I have completed Discovery Class and that all information provided is accurate. <span class="text-red-500">*</span>
                                    </span>
                                    <span x-show="form.class_type === 'discovery'">
                                        I confirm that all information provided is accurate. <span class="text-red-500">*</span>
                                    </span>
                                </span>
                            </label>
                        </div>
                    </div>

                </div>

                <!-- Live Booking Summary -->
                <div class="lg:col-span-5 space-y-4 lg:sticky lg:top-6">
                    
                    <!-- Itemized Price Calculation Summary -->
                    <div class="border border-[#E5E5EA] rounded-2xl bg-white overflow-hidden shadow-2xs">
                        <div class="bg-[#F2F2F7] px-4 py-3 border-b border-[#E5E5EA] flex items-center justify-between gap-2">
                            <span class="font-bold text-[#1D1D1F] text-sm sm:text-base">Booking Summary</span>
                            <span class="text-xs sm:text-sm font-bold px-2.5 py-0.5 rounded-full bg-[#EBF5FF] text-[#007DFE] capitalize shrink-0" x-text="form.class_type"></span>
                        </div>

                        <div class="p-3.5 sm:p-5 space-y-3 text-xs sm:text-sm">
                            <div class="flex justify-between items-center gap-2 text-[#6E6E73]">
                                <span class="min-w-0">Base Class Rate (<span class="capitalize" x-text="form.class_type"></span> × <span x-text="form.participants.length"></span>)</span>
                                <span class="font-bold text-[#1D1D1F] shrink-0 text-right" x-text="'₱' + formatNumber((pricingQuote ? pricingQuote.base_price_per_pax : calculateBasePriceUnit()) * form.participants.length)"></span>
                            </div>

                            <!-- Dynamic Pricing Adjustments -->
                            <template x-if="pricingQuote && pricingQuote.adjustments && pricingQuote.adjustments.length > 0">
                                <div class="space-y-2 py-2.5 border-y border-dashed border-[#E5E5EA]">
                                    <div class="text-[11px] sm:text-xs uppercase font-bold tracking-wider text-[#6E6E73]">Seasonal & Demand Adjustments:</div>
                                    <template x-for="adj in pricingQuote.adjustments" :key="adj.rule_id">
                                        <div class="flex justify-between items-start gap-2 text-xs sm:text-sm">
                                            <div class="min-w-0 space-y-0.5">
                                                <div class="font-medium text-[#1D1D1F] leading-snug" x-text="adj.rule_name"></div>
                                                <span class="inline-block text-[11px] px-1.5 py-0.5 rounded font-bold" :class="adj.delta_per_pax >= 0 ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800'" x-text="adj.formatted_adjustment + ' / pax'"></span>
                                            </div>
                                            <span class="font-bold shrink-0 text-right" :class="adj.delta_per_pax >= 0 ? 'text-rose-700' : 'text-emerald-700'" x-text="(adj.delta_per_pax >= 0 ? '+' : '−') + '₱' + formatNumber(Math.abs(adj.delta_per_pax) * form.participants.length)"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <div class="flex justify-between items-center gap-2 text-[#1D1D1F] font-semibold">
                                <span>Adjusted Class Subtotal</span>
                                <span class="font-extrabold text-[#1D1D1F] shrink-0 text-right" x-text="'₱' + formatNumber(calculateSubtotal())"></span>
                            </div>

                            <div x-show="form.pickup_option === 'carpool'" class="flex justify-between items-center gap-2 text-[#6E6E73]">
                                <span>Transportation (Carpool × <span x-text="form.participants.length"></span>)</span>
                                <span class="font-semibold text-[#1D1D1F] shrink-0 text-right" x-text="'₱' + formatNumber(calculateCarpoolFee())"></span>
                            </div>

                            <div x-show="form.boat_dive" class="flex justify-between items-center gap-2 text-[#6E6E73]">
                                <span>Boat Dive (₱600 × <span x-text="form.participants.length"></span>)</span>
                                <span class="font-semibold text-[#1D1D1F] shrink-0 text-right" x-text="'₱' + formatNumber(600 * form.participants.length)"></span>
                            </div>

                            <div class="flex justify-between items-center gap-2 text-[#6E6E73]">
                                <span>Mabini LGU Pass & Env. Fee</span>
                                <span class="font-semibold text-[#1D1D1F] shrink-0 text-right" x-text="'₱' + formatNumber(350 * form.participants.length)"></span>
                            </div>

                            <div class="pt-2.5 border-t border-[#E5E5EA] flex justify-between items-center gap-2 font-extrabold text-[#1D1D1F]">
                                <span class="text-xs sm:text-sm">Total Amount</span>
                                <span class="text-base sm:text-lg font-black text-[#1D1D1F] shrink-0 text-right" x-text="'₱' + formatNumber(calculateTotal())"></span>
                            </div>

                            <!-- Downpayment Box -->
                            <div class="p-3 sm:p-3.5 rounded-xl bg-[#D1FAE5] flex justify-between items-center gap-2">
                                <div class="min-w-0">
                                    <span class="font-extrabold text-[#065F46] block text-xs sm:text-sm leading-tight">Downpayment Due Now</span>
                                    <span class="text-[11px] sm:text-xs text-[#065F46]/80 font-medium block mt-0.5" x-text="'(' + (form.pickup_option === 'carpool' ? '3,000' : '2,000') + ' php / head)'"></span>
                                </div>
                                <span class="text-base sm:text-lg font-black text-[#065F46] shrink-0 text-right" x-text="'₱' + formatNumber(calculateDownpayment())"></span>
                            </div>

                            <!-- Balance Box -->
                            <div class="p-2.5 sm:p-3 rounded-xl bg-[#FDE68A] flex justify-between items-center gap-2 text-[#92400E]">
                                <span class="font-semibold text-xs sm:text-sm">Remaining Balance at Camp</span>
                                <span class="font-bold text-xs sm:text-sm shrink-0 text-right" x-text="'₱' + formatNumber(calculateTotal() - calculateDownpayment())"></span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Step 4: Downpayment -->
        <div x-show="currentStep === 4" x-cloak class="space-y-6">

            <div class="max-w-xl mx-auto bg-white rounded-2xl border border-[#E5E5EA] p-5 sm:p-8 shadow-2xs">
                <!-- Top Navigation & Header -->
                <div class="flex items-center justify-between">
                    <button type="button" 
                            @click="prevStep()" 
                            class="text-sm font-bold text-[#6E6E73] hover:text-[#1D1D1F] flex items-center gap-1.5 transition-colors cursor-pointer">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                        <span>Back to Booking Details</span>
                    </button>
                    <div class="text-right">
                        <span class="text-xs font-bold uppercase tracking-wider text-[#FF3B3C] block">Slot Hold Timer</span>
                        <span class="text-sm sm:text-base font-mono font-black text-[#FF3B3C]" x-text="timerDisplay"></span>
                    </div>
                </div>

                <!-- 1. Cancellation and Reschedule Policy (First on Step 4) -->
                <div class="space-y-3">
                    <div>
                        <h3 class="text-base font-extrabold text-[#1D1D1F]">
                            Cancellation & Reschedule Policy
                        </h3>
                        <p class="text-xs sm:text-sm text-[#6E6E73] mt-0.5">
                            Please review our reservation policies before completing your downpayment.
                        </p>
                    </div>

                    <div class="space-y-2.5 text-sm text-[#6E6E73]">
                        <!-- Tier 1: > 14 Days -->
                        <div class="rounded-xl space-y-1">
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <strong class="text-sm font-bold text-[#1D1D1F]">Notice Given > 14 Days</strong>
                                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md">
                                    100% Refund or 1 Free Reschedule
                                </span>
                            </div>
                            <p class="text-xs sm:text-sm text-[#6E6E73] leading-relaxed">
                                Eligible for full downpayment refund or one free date transfer to any future open schedule.
                            </p>
                        </div>

                        <!-- Tier 2: 7 to 14 Days -->
                        <div class="rounded-xl space-y-1">
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <strong class="text-sm font-bold text-[#1D1D1F]">Notice Given 7 to 14 Days</strong>
                                <span class="text-xs font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md">
                                    1 Free Date Reschedule
                                </span>
                            </div>
                            <p class="text-xs sm:text-sm text-[#6E6E73] leading-relaxed">
                                Free date reschedule to another available schedule. Downpayment is non-refundable.
                            </p>
                        </div>

                        <!-- Tier 3: < 7 Days (Locked) -->
                        <div class="rounded-xl space-y-1">
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <strong class="text-sm font-bold text-[#1D1D1F]">Notice Given < 7 Days</strong>
                                <span class="text-xs font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded-md">
                                    Non-Refundable
                                </span>
                            </div>
                            <p class="text-xs sm:text-sm text-[#6E6E73] leading-relaxed">
                                Slots and resort/boat allocations are finalized. Cannot be refunded or rescheduled.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 2. Downpayment Breakdown & Summary Card -->
                <div class="space-y-3 pt-2 border-t border-[#F2F2F7]">
                    <div>
                        <h3 class="text-base font-extrabold text-[#1D1D1F]">
                            Reservation Breakdown
                        </h3>
                    </div>

                    <div class="rounded-2xl space-y-3">
                        <div class="flex justify-between items-center text-sm text-[#6E6E73]">
                            <span>Package</span>
                            <strong class="text-[#1D1D1F] capitalize" x-text="form.class_type + ' (' + form.participants.length + ' pax)'"></strong>
                        </div>
                        <div class="flex justify-between items-center text-sm text-[#6E6E73]" x-show="form.start_date && form.end_date">
                            <span>Dive Dates</span>
                            <span class="font-medium text-[#1D1D1F]" x-text="formatDateDisplay(form.start_date) + ' to ' + formatDateDisplay(form.end_date)"></span>
                        </div>
                        <div class="flex justify-between items-center text-sm text-[#6E6E73]">
                            <span>Total Trip Cost</span>
                            <strong class="text-[#1D1D1F]" x-text="'₱' + formatNumber(calculateTotal())"></strong>
                        </div>
                        <div class="flex justify-between items-center text-sm text-[#6E6E73]">
                            <span>Remaining Balance (at Camp)</span>
                            <span class="font-semibold text-[#1D1D1F]" x-text="'₱' + formatNumber(calculateTotal() - calculateDownpayment())"></span>
                        </div>

                        <!-- Downpayment Highlight -->
                        <div class="pt-3 border-t border-[#E5E5EA] flex justify-between items-center">
                            <div>
                                <span class="font-bold text-[#065F46] text-sm block">Downpayment Due Now:</span>
                                <span class="text-xs text-[#065F46]" x-text="'(' + (form.pickup_option === 'carpool' ? '₱3,000' : '₱2,000') + ' / head × ' + form.participants.length + ' pax)'"></span>
                            </div>
                            <strong class="text-2xl font-black text-[#065F46]" x-text="'₱' + formatNumber(calculateDownpayment())"></strong>
                        </div>
                    </div>
                </div>

                <!-- 3. Supported Hosted Payment Channels -->
                <div class="space-y-2.5 pt-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#8E8E93] block">
                        Accepted on PayMongo Hosted Checkout:
                    </span>

                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="p-2.5 rounded-xl bg-[#F2F2F7] space-y-0.5">
                            <span class="font-bold text-xs sm:text-sm text-[#1D1D1F] block">QR Ph</span>
                            <span class="text-xs text-[#8E8E93] block">All PH Banks</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-[#F2F2F7] space-y-0.5">
                            <span class="font-bold text-xs sm:text-sm text-[#1D1D1F] block">GCash</span>
                            <span class="text-xs text-[#8E8E93] block">E-Wallet</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-[#F2F2F7] space-y-0.5">
                            <span class="font-bold text-xs sm:text-sm text-[#1D1D1F] block">Maya & Cards</span>
                            <span class="text-xs text-[#8E8E93] block">Visa / Master</span>
                        </div>
                    </div>

                    <p class="text-xs text-[#6E6E73] text-center pt-1">
                        You will be redirected to PayMongo's secure hosted checkout page to complete your payment.
                    </p>
                </div>

                <!-- 4. Explicit Consent & Hosted Checkout Action Button -->
                <div class="space-y-4 pt-2">
                    <div class="p-3.5 rounded-xl bg-[#F2F2F7] transition-all"
                         :class="touchedStep4 && !form.hasAgreedToTerms ? 'border border-[#FF3B3C] bg-red-50/20' : ''">
                        <label class="flex items-start gap-2.5 cursor-pointer select-none">
                            <input type="checkbox" 
                                   x-model="form.hasAgreedToTerms" 
                                   class="w-4 h-4 rounded text-[#780000] focus:ring-[#780000] mt-0.5 shrink-0">
                            <span class="text-xs sm:text-sm text-[#1D1D1F] leading-relaxed">
                                I have read and agree to the 
                                <a href="{{ route('legal.terms') }}" target="_blank" class="text-[#780000] font-bold underline hover:text-[#500000]">Terms &amp; Conditions</a>, 
                                <a href="{{ route('legal.privacy') }}" target="_blank" class="text-[#780000] font-bold underline hover:text-[#500000]">Privacy Policy</a>, 
                                and Cancellation Policy. <span class="text-[#780000]">*</span>
                            </span>
                        </label>
                        <span x-show="touchedStep4 && !form.hasAgreedToTerms" class="text-xs text-[#FF3B3C] font-semibold mt-1.5 block">
                            Please check the box above to accept the terms before proceeding to payment.
                        </span>
                    </div>

                    <button type="button" 
                            @click="if (!form.hasAgreedToTerms) { touchedStep4 = true; errorMessage = 'Please read and agree to the Terms & Conditions and Privacy Policy to proceed.'; window.scrollTo({ top: 0, behavior: 'smooth' }); return; } processPayment(false, true)" 
                            :disabled="submittingPayment || !form.hasAgreedToTerms"
                            class="btn-primary w-full py-3.5 px-6 text-base font-bold shadow-sm flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!submittingPayment" class="flex items-center gap-2">
                            <span>Proceed to PayMongo Hosted Checkout</span>
                        </span>
                        <span x-show="submittingPayment" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span>Redirecting to PayMongo...</span>
                        </span>
                    </button>

                    <div class="text-center text-xs text-[#6E6E73] leading-relaxed">
                        <span>Your payment is safely processed by PayMongo using bank-grade encryption. We never see or store your card or wallet details.</span>
                    </div>
                </div>

            </div>

        </div>

        <!-- Step 5: Confirmation & Credentials -->
        <div x-show="currentStep === 5" x-cloak class="space-y-6 text-center">
            <div class="w-16 h-16 bg-[#ECFDF5] text-[#34C759] rounded-full flex items-center justify-center mx-auto border border-[#A7F3D0]">
                <svg class="w-8 h-8 text-[#166534]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
            </div>

            <div class="space-y-2">
                <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F]">
                    Booking Confirmed!
                </h2>
                <p class="text-sm sm:text-sm text-[#6E6E73] max-w-md mx-auto">
                    We've emailed your booking confirmation to <strong class="text-[#1D1D1F]" x-text="form.contact_email"></strong>. Please save your reference number and PIN below.
                </p>
            </div>

            <!-- Booking Credentials Voucher -->
            <div class="max-w-md mx-auto p-5 rounded-2xl bg-[#F8EAEA]/40 border border-[#780000]/20 space-y-4">
                <div>
                    <span class="text-sm uppercase tracking-wider text-[#6E6E73] font-bold">Booking Reference Number</span>
                    <div class="text-2xl sm:text-3xl font-mono font-extrabold text-[#780000] tracking-wider" x-text="confirmedBooking.booking_number"></div>
                </div>

                <div class="pt-2 border-t border-[#780000]/20">
                    <span class="text-sm uppercase tracking-wider text-[#6E6E73] font-bold">4-Digit Security PIN</span>
                    <div class="text-2xl font-mono font-bold text-[#1D1D1F] tracking-widest" x-text="confirmedBooking.pin"></div>
                    <span class="text-sm text-[#6E6E73] block mt-1">Keep this PIN safe to manage or update your booking anytime.</span>
                </div>

                <div class="pt-2">
                    <button type="button" 
                            @click="copyCredentials()" 
                            class="btn-secondary text-[#780000] border-[#780000]/30 hover:bg-[#F8EAEA] px-4 py-2 text-sm font-bold shadow-2xs">
                        <span x-text="copied ? 'Copied to Clipboard!' : 'Copy Booking # and PIN'"></span>
                    </button>
                </div>
            </div>

            <!-- Recap Summary -->
            <div class="max-w-lg mx-auto p-4 sm:p-5 rounded-xl bg-[#F2F2F7] text-left text-sm sm:text-sm space-y-2.5 shadow-2xs">
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

                <!-- Applied Dynamic Pricing Rules -->
                <template x-if="pricingQuote && pricingQuote.adjustments && pricingQuote.adjustments.length > 0">
                    <div class="py-2 border-b border-[#E5E5EA] space-y-1.5">
                        <div class="text-sm uppercase font-bold tracking-wider text-[#6E6E73]">Applied Dynamic Pricing Rules:</div>
                        <template x-for="adj in pricingQuote.adjustments" :key="adj.rule_id">
                            <div class="flex justify-between items-center text-sm">
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
            <div class="max-w-lg mx-auto p-5 rounded-xl bg-[#F2F2F7] text-left text-sm sm:text-sm space-y-2 shadow-2xs">
                <h4 class="font-bold text-[#1D1D1F]">Things to Bring:</h4>
                <ul class="space-y-1 text-sm sm:text-sm text-[#6E6E73] list-disc list-inside">
                    <li>Swimming clothes (anything you’re comfortable wearing)</li>
                    <li>Toiletries</li>
                    <li>Personal things</li>
                    <li>A pair of socks (in any kind) for fin fitting</li>
                </ul>
                <p class="text-sm text-[#065F46] font-semibold pt-1">
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

        <!-- Step Navigation Controls -->
        <div x-show="currentStep < 4" class="mt-8 pt-6 flex items-center justify-between gap-2.5 sm:gap-4">
            <button type="button" 
                    @click="prevStep()" 
                    x-show="currentStep > 1"
                    class="btn-secondary px-3.5 sm:px-5 py-2.5 sm:py-3 text-xs sm:text-sm font-bold flex items-center gap-1.5 shrink-0 cursor-pointer">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                <span>Back</span>
            </button>
            <div x-show="currentStep === 1"></div>

            <button type="button" 
                    @click="nextStep()" 
                    class="btn-primary px-4 sm:px-8 py-2.5 sm:py-3 text-xs sm:text-sm font-extrabold cursor-pointer active:scale-[0.99] transition-all text-center leading-snug flex-1 sm:flex-initial shadow-sm">
                <span x-text="currentStep === 3 ? 'Proceed to Downpayment (₱' + formatNumber(calculateDownpayment()) + ')' : 'Continue'"></span>
            </button>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
/**
 * Alpine.js Client-Side Booking Flow State Machine.
 *
 * Workflow Architecture:
 * - Step 1 (Package Selection): Class tier configuration and diver certification prerequisite checking.
 * - Step 2 (Date & Weather Selection): Interactive dual-calendar picker with live 16-day Open-Meteo & ML risk preview.
 * - Step 3 (Contact & Add-ons): Dynamic participant repeater, medical questionnaires, carpool hubs, and live pricing quote.
 * - Step 4 (Downpayment Checkout): 15-minute slot-hold countdown timer and PayMongo payment gateway integration.
 * - Step 5 (Voucher & PIN): Booking reference confirmation and credential copy tool.
 *
 * State Persistence:
 * Automatically synchronizes draft state to localStorage to prevent data loss on accidental page refresh.
 *
 * @param {Object} config Initial configuration (initialClass, pickupPoints).
 * @return {Object} Reactive Alpine.js component scope.
 */
// TODO: Implement Web Worker background sync for offline draft storage in IndexedDB.
function bookingForm(config) {
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
                { first_name: '', last_name: '', name: '', age: '', health_condition: '', swimmer_status: 'non_swimmer' }
            ],
            contact_first_name: '',
            contact_last_name: '',
            contact_name: '',
            contact_email: '',
contact_phone: '',
            contact_facebook: '',
            pickup_option: 'carpool',
            pickup_location: '',
            boat_dive: false,
            confirmation_ack: false,
            hasAgreedToTerms: false,
            payment_method: 'paymongo'
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
            {title: 'Marine Conditions', text: 'Our forecast monitors wave height (<1.0m is ideal), ocean currents, and wind speed.' },
            {title: 'Sanctuary Dives', text: 'Camp FreedivePH operates in Mabini Marine Protected Areas with clear year-round visibility.' },
            {title: 'Carpool Hubs', text: 'Weekend carpool vans depart Manila hubs at 2:30 AM - 3:30 AM directly to camp resort.' }
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

        // Dual Calendar State & Helpers
        calendarYear: new Date().getFullYear(),
        calendarMonth: new Date().getMonth(),

        get month1Year() {
            return this.calendarYear;
        },
        get month1Month() {
            return this.calendarMonth;
        },
        get month2Year() {
            return (this.calendarMonth === 11) ? this.calendarYear + 1 : this.calendarYear;
        },
        get month2Month() {
            return (this.calendarMonth + 1) % 12;
        },

        getMonthName(mIndex) {
            const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            return months[mIndex] || '';
        },

        formatDateDisplay(dateStr) {
            if (!dateStr) return '';
            const parts = dateStr.split('-');
            if (parts.length !== 3) return dateStr;
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const d = parts[2].padStart(2, '0');
            const m = months[parseInt(parts[1], 10) - 1] || '';
            const y = parts[0];
            return `${d} ${m} ${y}`;
        },

        formatDateDayOfWeek(dateStr) {
            if (!dateStr) return '';
            const parts = dateStr.split('-');
            if (parts.length !== 3) return '';
            const date = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
            const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            return days[date.getDay()] || '';
        },

        canGoPrev() {
            const now = new Date();
            const curY = now.getFullYear();
            const curM = now.getMonth();
            return (this.calendarYear > curY) || (this.calendarYear === curY && this.calendarMonth > curM);
        },

        prevMonth() {
            if (!this.canGoPrev()) return;
            if (this.calendarMonth === 0) {
                this.calendarMonth = 11;
                this.calendarYear--;
            } else {
                this.calendarMonth--;
            }
        },

        nextMonth() {
            if (this.calendarMonth === 11) {
                this.calendarMonth = 0;
                this.calendarYear++;
            } else {
                this.calendarMonth++;
            }
        },

        getMonthDays(year, month) {
            const firstDayIndex = new Date(year, month, 1).getDay();
            const totalDays = new Date(year, month + 1, 0).getDate();
            const now = new Date();
            const todayStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
            
            const days = [];
            for (let b = 0; b < firstDayIndex; b++) {
                days.push({ isBlank: true });
            }
            for (let d = 1; d <= totalDays; d++) {
                const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                const dayOfWeek = new Date(year, month, d).getDay();
                const isDisabled = dateStr < todayStr;
                const isSunday = (dayOfWeek === 0);
                days.push({
                    day: d,
                    dateStr: dateStr,
                    isBlank: false,
                    isDisabled: isDisabled,
                    isSunday: isSunday
                });
            }
            return days;
        },

        selectDate(dateStr) {
            this.form.start_date = dateStr;
            this.onStartDateChange();
        },

        initBooking() {
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
                                           data.form.contact_first_name ||
                                           data.form.contact_email || 
                                           data.form.contact_phone || 
                                           data.form.start_date ||
                                           (data.form.participants && data.form.participants[0] && (data.form.participants[0].first_name || data.form.participants[0].name));

                        this.form = {
                            ...this.form,
                            ...data.form
                        };

                        if (!Array.isArray(this.form.participants) || this.form.participants.length === 0) {
                            this.form.participants = [
                                { first_name: '', last_name: '', name: '', age: '', health_condition: '', swimmer_status: 'non_swimmer' }
                            ];
                        } else {
                            this.form.participants.forEach(p => {
                                if (!p.first_name && p.name) {
                                    const parts = p.name.trim().split(/\s+/);
                                    p.first_name = parts[0] || '';
                                    p.last_name = parts.slice(1).join(' ') || '';
                                }
                            });
                        }

                        if (!this.form.contact_first_name && this.form.contact_name) {
                            const parts = this.form.contact_name.trim().split(/\s+/);
                            this.form.contact_first_name = parts[0] || '';
                            this.form.contact_last_name = parts.slice(1).join(' ') || '';
                        }

                        if (this.form.start_date) {
                            const p = this.form.start_date.split('-');
                            if (p.length === 3) {
                                this.calendarYear = parseInt(p[0], 10);
                                this.calendarMonth = parseInt(p[1], 10) - 1;
                            }
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
                    { first_name: '', last_name: '', name: '', age: '', health_condition: '', swimmer_status: 'non_swimmer' }
                ],
                contact_first_name: '',
                contact_last_name: '',
                contact_name: '',
                contact_email: '',
                contact_phone: '',
                contact_facebook: '',
                pickup_option: 'carpool',
                pickup_location: '',
                boat_dive: false,
                confirmation_ack: false,
                hasAgreedToTerms: false,
                payment_method: 'paymongo'
            };
            this.forecast = null;
            this.pricingQuote = null;
            this.errorMessage = '';
            this.touchedStep3 = false;
            this.touchedStep4 = false;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        onStartDateChange() {
            if (!this.form.start_date) {
                this.form.end_date = '';
                this.forecast = null;
                this.pricingQuote = null;
                return;
            }
            const parts = this.form.start_date.split('-');
            if (parts.length === 3) {
                const start = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
                const end = new Date(start);
                end.setDate(start.getDate() + 1);
                const ey = end.getFullYear();
                const em = String(end.getMonth() + 1).padStart(2, '0');
                const ed = String(end.getDate()).padStart(2, '0');
                this.form.end_date = `${ey}-${em}-${ed}`;
            }
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
                first_name: '',
                last_name: '',
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

        getSafetyScore(classification) {
            switch (classification) {
                case 'Very Safe': return 5;
                case 'Safe': return 4;
                case 'Moderate': return 3;
                case 'High Risk': return 2;
                case 'Critical Risk': return 1;
                default: return 4;
            }
        },

        getSafetyBarClass(classification) {
            switch (classification) {
                case 'Very Safe':
                case 'Safe':
                    return 'bg-[#10B981]';
                case 'Moderate':
                    return 'bg-[#F59E0B]';
                case 'High Risk':
                    return 'bg-[#F43F5E]';
                case 'Critical Risk':
                    return 'bg-[#EF4444]';
                default:
                    return 'bg-[#10B981]';
            }
        },

        getSafetyBorderClass(classification) {
            switch (classification) {
                case 'Very Safe':
                case 'Safe':
                    return 'border-[#10B981]';
                case 'Moderate':
                    return 'border-[#F59E0B]';
                case 'High Risk':
                    return 'border-[#F43F5E]';
                case 'Critical Risk':
                    return 'border-[#EF4444]';
                default:
                    return 'border-[#10B981]';
            }
        },

        getSafetyTextClass(classification) {
            switch (classification) {
                case 'Very Safe':
                case 'Safe':
                    return 'text-[#10B981]';
                case 'Moderate':
                    return 'text-[#F59E0B]';
                case 'High Risk':
                    return 'text-[#F43F5E]';
                case 'Critical Risk':
                    return 'text-[#EF4444]';
                default:
                    return 'text-[#10B981]';
            }
        },

        touchedStep3: false,
        touchedStep4: false,

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
            } else if (this.currentStep === 2) {
                if (!this.form.start_date) {
                    this.errorMessage = "Please select your preferred trip start date to continue.";
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }
                if (this.weatherLoading) {
                    this.errorMessage = "Please wait while we evaluate the weather & marine safety for your dates.";
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
                        this.errorMessage = `Please enter the First & Last Name for Participant #${i + 1}.`;
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
                if (this.form.pickup_option === 'carpool' && !this.form.pickup_location) {
                    this.errorMessage = "Please select your preferred Carpool Pickup Hub & Schedule to continue.";
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }
                if (!this.form.confirmation_ack) {
                    this.errorMessage = (this.form.class_type === 'fundive' || this.form.class_type === 'refinement')
                        ? "Please check the confirmation box acknowledging that you have completed Discovery Class and that all provided information is accurate."
                        : "Please check the confirmation box acknowledging that all provided details are accurate.";
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }
                if (this.form.class_type === 'fundive' || this.form.class_type === 'refinement') {
                    this.form.discovery_completed_ack = true;
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

        async processPayment(instantSimulation = false, hostedCheckout = true) {
            if (!this.form.hasAgreedToTerms) {
                this.touchedStep4 = true;
                this.errorMessage = "Please read and agree to the Terms & Conditions and Privacy Policy to proceed.";
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }

            this.submittingPayment = true;
            this.errorMessage = '';

            try {
                const payload = {
                    ...this.form,
                    payment_method: 'paymongo'
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
window.bookingWizard = bookingForm;
</script>
@endpush
