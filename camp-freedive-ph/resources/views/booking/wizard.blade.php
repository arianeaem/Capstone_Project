@extends('layouts.app')

@section('title', 'Book Camp | Camp FreedivePH')
@section('meta_description', 'Book a 2D1N freediving camp in Mabini, Batangas.')
@section('hide_header', true)
@section('hide_footer', true)

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-6 sm:py-10 text-sm" 
     x-data="bookingWizard({
         initialClass: '{{ $selectedClass }}',
         pickupPoints: {{ json_encode($pickupPoints) }},
         csrfToken: '{{ csrf_token() }}',
         checkWeatherUrl: '{{ route('api.weather.check') }}',
         pricingQuoteUrl: '{{ route('api.pricing.quote') }}',
         storeBookingUrl: '{{ route('booking.store') }}'
     })"
     x-init="initWizard()">

    <!-- Booking Header -->
    <div class="flex items-center justify-between pb-5 mb-6 sm:mb-8 border-b border-[#E5E5EA]">
        <a href="{{ route('landing') }}" class="flex items-center gap-2.5 group">
            <img src="{{ asset('images/logo.png') }}" alt="Camp FreedivePH Logo" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full object-contain bg-white">
            <div>
                <span class="font-extrabold text-base sm:text-lg tracking-tight text-[#1D1D1F] block leading-none">Camp Freedive<span class="text-[#780000]">PH</span></span>
                <span class="text-[11px] sm:text-xs text-[#6E6E73] font-medium tracking-wider block mt-0.5">Mabini, Batangas</span>
            </div>
        </a>
        <a href="{{ route('landing') }}" class="text-xs font-semibold text-[#6E6E73] hover:text-[#780000] flex items-center gap-1.5 transition-colors">
            <span>← Exit to Home</span>
        </a>
    </div>

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
        <button @click="errorMessage = ''" class="text-[#991B1B] font-bold text-sm">✕</button>
    </div>

    <!-- Draft Restored Notification Banner -->
    <div x-show="draftRestored" x-cloak class="mb-6 p-3.5 sm:p-4 rounded-xl bg-[#F0FDF4] text-[#166534] text-xs sm:text-sm flex items-center justify-between gap-3 shadow-2xs">
        <div class="flex items-center gap-2">
            <span>Your saved booking progress has been automatically restored.</span>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <button type="button" @click="resetForm()" class="font-bold underline text-[#15803D] hover:text-[#166534] text-xs">
                Clear
            </button>
            <button type="button" @click="draftRestored = false" class="text-[#166534] font-bold text-sm">✕</button>
        </div>
    </div>

    <!-- Wizard Form Container -->
    <div class="bg-white rounded-2xl border border-[#E5E5EA] p-4 sm:p-8 lg:p-10 relative text-sm">

        <!-- Step 1: Select Class -->
        <div x-show="currentStep === 1" x-cloak class="space-y-6">
            <div class="border-b border-[#E5E5EA] pb-4">
                <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F]">Choose Your Freediving Class</h2>
                <p class="text-xs sm:text-sm text-[#6E6E73] mt-1">Select the course or dive experience you want to join. Beginners and non-swimmers are welcome!</p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:gap-6">
                <!-- Discovery Package Option -->
                <div @click="form.class_type = 'discovery'" 
                     class="p-4 sm:p-6 rounded-xl border transition-all cursor-pointer flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                     :class="form.class_type === 'discovery' ? 'border-[#780000] bg-[#F8EAEA]/30' : 'border-[#E5E5EA] hover:border-[#D1D1D6]'">
                    <div class="flex items-start gap-3 sm:gap-4">
                        <div class="w-5 h-5 sm:w-6 sm:h-6 rounded-full border flex items-center justify-center shrink-0 mt-0.5"
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

                <!-- Fun Dive Package Option -->
                <div @click="form.class_type = 'fundive'" 
                     class="p-4 sm:p-6 rounded-xl border transition-all cursor-pointer flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                     :class="form.class_type === 'fundive' ? 'border-[#780000] bg-[#F8EAEA]/30' : 'border-[#E5E5EA] hover:border-[#D1D1D6]'">
                    <div class="flex items-start gap-3 sm:gap-4">
                        <div class="w-5 h-5 sm:w-6 sm:h-6 rounded-full border flex items-center justify-center shrink-0 mt-0.5"
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

                <!-- Skill Refinement Package Option -->
                <div @click="form.class_type = 'refinement'" 
                     class="p-4 sm:p-6 rounded-xl border transition-all cursor-pointer flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                     :class="form.class_type === 'refinement' ? 'border-[#780000] bg-[#F8EAEA]/30' : 'border-[#E5E5EA] hover:border-[#D1D1D6]'">
                    <div class="flex items-start gap-3 sm:gap-4">
                        <div class="w-5 h-5 sm:w-6 sm:h-6 rounded-full border flex items-center justify-center shrink-0 mt-0.5"
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

        <!-- Step 2: Select Dates -->
        <div x-show="currentStep === 2" x-cloak class="space-y-6">
            
            <!-- Step Header -->
            <div class="border-b border-[#E5E5EA] pb-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F]">Choose Your Dive Dates</h2>
                        <p class="text-xs sm:text-sm text-[#6E6E73] mt-1">Select your preferred 2D1N trip start date on the calendar. All freediving camps run for 2 consecutive days.</p>
                    </div>

                    <!-- Information Icon with Hover Notice -->
                    <div class="relative group inline-flex items-center self-start sm:self-center">
                        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#F2F2F7] hover:bg-[#E5E5EA] text-xs font-semibold text-[#1D1D1F] cursor-pointer transition-colors">
                            <img src="{{ asset('icons/icons8-exclamation-mark-60.png') }}" class="w-4 h-4 shrink-0 object-contain" alt="About Forecast">
                            <span>About Forecast</span>
                        </div>

                        <!-- Interactive Date Help Tooltip -->
                        <div class="absolute right-0 sm:right-auto sm:left-0 top-full mt-2 w-80 p-3.5 bg-[#1D1D1F] text-white text-xs rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 pointer-events-none space-y-1.5 leading-relaxed">
                            <div class="font-bold flex items-center gap-1.5 text-[#00C3D0]">
                                <img src="{{ asset('icons/icons8-exclamation-mark-60.png') }}" class="w-3.5 h-3.5 shrink-0 object-contain brightness-0 invert" alt="Weather Note">
                                <span>Weather & Sea Conditions Note</span>
                            </div>
                            <p class="text-[11px] text-gray-200">
                                Safety ratings shown are automated predictions based on coastal forecast models. Actual water conditions can change naturally, and our safety team continuously checks the water before every dive.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dates and Safety Evaluation Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">

                <!-- Calendar and Date Selection -->
                <div class="lg:col-span-7 space-y-5">

                    <!-- Selected Dates Overview -->
                    <div class="rounded-2xl border border-[#E5E5EA] bg-white p-4 sm:p-5 transition-all shadow-2xs">
                        <div class="grid grid-cols-2 divide-x divide-[#E5E5EA]">
                            
                            <!-- Depart (Day 1) -->
                            <div class="pr-3 sm:pr-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-[#6E6E73]">Depart</span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-[#F8EAEA] text-[#780000]">Day 1</span>
                                </div>
                                <div class="mt-1.5">
                                    <template x-if="form.start_date">
                                        <div>
                                            <div class="text-base sm:text-xl font-black text-[#1D1D1F] tracking-tight" x-text="formatDateDisplay(form.start_date)"></div>
                                            <div class="text-xs font-semibold text-[#780000] mt-0.5" x-text="formatDateDayOfWeek(form.start_date)"></div>
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
                            <div class="pl-3 sm:pl-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-[#6E6E73]">Return</span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-[#EBF7F8] text-[#00C3D0]">Day 2</span>
                                </div>
                                <div class="mt-1.5">
                                    <template x-if="form.end_date">
                                        <div>
                                            <div class="text-base sm:text-xl font-black text-[#1D1D1F] tracking-tight" x-text="formatDateDisplay(form.end_date)"></div>
                                            <div class="text-xs font-semibold text-[#00C3D0] mt-0.5" x-text="formatDateDayOfWeek(form.end_date)"></div>
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
                    <div class="bg-white rounded-2xl p-5 sm:p-7 space-y-6">
                        
                        <!-- Months Container -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 items-start">
                            
                            <!-- First Month -->
                            <div class="space-y-4">
                                <!-- Header for Month 1 with Prev Arrow -->
                                <div class="flex items-center justify-between h-9">
                                    <button type="button" 
                                            @click="prevMonth()" 
                                            :disabled="!canGoPrev()"
                                            class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-[#F2F2F7] disabled:opacity-20 disabled:cursor-not-allowed transition-all text-[#1D1D1F] shrink-0"
                                            title="Previous Month">
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
                                            title="Next Month">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
                                    </button>
                                </div>
                                
                                <!-- Weekday Headers -->
                                <div class="grid grid-cols-7 text-center text-xs font-semibold text-[#6E6E73] py-1">
                                    <span class="text-[#780000] font-bold">Sun</span>
                                    <span>Mon</span>
                                    <span>Tue</span>
                                    <span>Wed</span>
                                    <span>Thu</span>
                                    <span>Fri</span>
                                    <span>Sat</span>
                                </div>

                                <!-- Month Calendar Days -->
                                <div class="grid grid-cols-7 gap-y-2 text-center text-xs sm:text-sm">
                                    <template x-for="(dObj, idx) in getMonthDays(month1Year, month1Month)" :key="'m1-' + idx">
                                        <div class="h-9 flex items-center justify-center relative">
                                            <template x-if="dObj.isBlank">
                                                <span class="w-full h-full"></span>
                                            </template>
                                            <template x-if="!dObj.isBlank">
                                                <button type="button"
                                                        @click="!dObj.isDisabled && selectDate(dObj.dateStr)"
                                                        :disabled="dObj.isDisabled"
                                                        class="w-8 h-8 sm:w-9 sm:h-9 rounded-full flex items-center justify-center font-medium text-xs sm:text-sm transition-all relative z-10"
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

                            <!-- Second Month -->
                            <div class="space-y-4">
                                <!-- Header for Month 2 with Next Arrow -->
                                <div class="flex items-center justify-between h-9">
                                    <div class="w-8 hidden md:block shrink-0"></div>

                                    <div class="font-extrabold text-sm sm:text-base text-[#1D1D1F] text-center flex-1">
                                        <span x-text="getMonthName(month2Month) + ' ' + month2Year"></span>
                                    </div>

                                    <button type="button" 
                                            @click="nextMonth()" 
                                            class="w-8 h-8 rounded-full flex items-center justify-center hover:bg-[#F2F2F7] transition-all text-[#1D1D1F] hidden md:flex shrink-0"
                                            title="Next Month">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
                                    </button>
                                </div>
                                
                                <!-- Weekday Headers -->
                                <div class="grid grid-cols-7 text-center text-xs font-semibold text-[#6E6E73] py-1">
                                    <span class="text-[#780000] font-bold">Sun</span>
                                    <span>Mon</span>
                                    <span>Tue</span>
                                    <span>Wed</span>
                                    <span>Thu</span>
                                    <span>Fri</span>
                                    <span>Sat</span>
                                </div>

                                <!-- Month Calendar Days -->
                                <div class="grid grid-cols-7 gap-y-2 text-center text-xs sm:text-sm">
                                    <template x-for="(dObj, idx) in getMonthDays(month2Year, month2Month)" :key="'m2-' + idx">
                                        <div class="h-9 flex items-center justify-center relative">
                                            <template x-if="dObj.isBlank">
                                                <span class="w-full h-full"></span>
                                            </template>
                                            <template x-if="!dObj.isBlank">
                                                <button type="button"
                                                        @click="!dObj.isDisabled && selectDate(dObj.dateStr)"
                                                        :disabled="dObj.isDisabled"
                                                        class="w-8 h-8 sm:w-9 sm:h-9 rounded-full flex items-center justify-center font-medium text-xs sm:text-sm transition-all relative z-10"
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
                    <div class="bg-white rounded-2xl border border-[#E5E5EA] p-5 sm:p-6 shadow-2xs space-y-4">
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#00C3D0] animate-pulse"></span>
                                <h3 class="font-black text-base text-[#1D1D1F]">Dive Safety Evaluation</h3>
                            </div>
                            
                            <template x-if="weatherLoading">
                                <span class="text-xs text-[#00C3D0] font-bold flex items-center gap-1">
                                    <svg class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    Checking...
                                </span>
                            </template>
                        </div>

                        <!-- Forecast Loading State -->
                        <div x-show="weatherLoading" x-cloak class="space-y-4 py-2">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-xs">
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
                                <div class="p-4 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] space-y-2">
                                    <div class="h-3.5 w-24 bg-[#E5E5EA] rounded"></div>
                                    <div class="h-3 w-40 bg-[#F2F2F7] rounded"></div>
                                </div>
                                <div class="p-4 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] space-y-2">
                                    <div class="h-3.5 w-24 bg-[#E5E5EA] rounded"></div>
                                    <div class="h-3 w-40 bg-[#F2F2F7] rounded"></div>
                                </div>
                            </div>

                            <!-- Rotating Tip -->
                            <div class="p-3 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] flex items-center gap-2.5 text-xs text-[#6E6E73]">
                                <div class="flex-1 min-w-0">
                                    <span class="font-bold text-[#1D1D1F]" x-text="currentTip.title + ': '"></span>
                                    <span x-text="currentTip.text"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Weather Assessment Results -->
                        <template x-if="forecast && !weatherLoading && !forecast.is_benchmark">
                            <div class="space-y-4">
                                <!-- Overall Banner -->
                                <div class="rounded-xl p-4 border transition-all space-y-2 shadow-2xs"
                                     :style="'background-color: ' + forecast.bg_color + '; border-color: ' + forecast.border_color + '; color: ' + forecast.text_color">
                                    <div class="flex items-center justify-between gap-2">
                                        <h4 class="font-extrabold text-sm sm:text-base" x-text="forecast.title"></h4>
                                        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-white/60" x-text="forecast.classification"></span>
                                    </div>
                                    <p class="text-xs leading-relaxed opacity-90" x-text="forecast.description"></p>
                                </div>

                                <!-- Day 1 & Day 2 Breakdown -->
                                <template x-if="forecast.day1 && forecast.day2">
                                    <div class="space-y-2.5">
                                        <!-- Day 1 -->
                                        <div class="p-3 rounded-xl space-y-1 text-xs">
                                            <div class="flex items-center justify-between">
                                                <span class="font-extrabold text-[#780000] text-xs uppercase tracking-wider">Day 1</span>
                                                <span class="font-bold px-2 py-0.5 rounded-full text-[10px]"
                                                      :class="{
                                                          'bg-emerald-100 text-emerald-800': forecast.day1.classification === 'Very Safe' || forecast.day1.classification === 'Safe',
                                                          'bg-amber-100 text-amber-800': forecast.day1.classification === 'Moderate',
                                                          'bg-rose-100 text-rose-800': forecast.day1.classification === 'High Risk',
                                                          'bg-red-100 text-red-800': forecast.day1.classification === 'Critical Risk'
                                                      }"
                                                      x-text="forecast.day1.classification"></span>
                                            </div>
                                            <div class="text-[#6E6E73] text-[11px] flex items-center justify-between">
                                                <span class="font-bold text-[#1D1D1F]" x-text="forecast.day1.date"></span>
                                                <span>Worst Hour: <strong class="text-[#1D1D1F]" x-text="forecast.day1.worst_hour"></strong></span>
                                            </div>
                                            <p class="text-[11px] text-[#6E6E73] pt-0.5" x-text="forecast.day1.recommended_action"></p>
                                        </div>

                                        <!-- Day 2 -->
                                        <div class="p-3 rounded-xl space-y-1 text-xs">
                                            <div class="flex items-center justify-between">
                                                <span class="font-extrabold text-[#00C3D0] text-xs uppercase tracking-wider">Day 2</span>
                                                <span class="font-bold px-2 py-0.5 rounded-full text-[10px]"
                                                      :class="{
                                                          'bg-emerald-100 text-emerald-800': forecast.day2.classification === 'Very Safe' || forecast.day2.classification === 'Safe',
                                                          'bg-amber-100 text-amber-800': forecast.day2.classification === 'Moderate',
                                                          'bg-rose-100 text-rose-800': forecast.day2.classification === 'High Risk',
                                                          'bg-red-100 text-red-800': forecast.day2.classification === 'Critical Risk'
                                                      }"
                                                      x-text="forecast.day2.classification"></span>
                                            </div>
                                            <div class="text-[#6E6E73] text-[11px] flex items-center justify-between">
                                                <span class="font-bold text-[#1D1D1F]" x-text="forecast.day2.date"></span>
                                                <span>Worst Hour: <strong class="text-[#1D1D1F]" x-text="forecast.day2.worst_hour"></strong></span>
                                            </div>
                                            <p class="text-[11px] text-[#6E6E73] pt-0.5" x-text="forecast.day2.recommended_action"></p>
                                        </div>
                                    </div>
                                </template>

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
                        <div class="flex items-center justify-between pb-2">
                            <h4 class="text-lg font-bold text-[#1D1D1F]">1. Participants</h4>
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
                                                class="text-xs font-semibold text-[#FF3B3C] hover:underline cursor-pointer">
                                            Remove
                                        </button>
                                    </div>

                                    <!-- Participant Name -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block font-bold text-[#1D1D1F] text-xs mb-2">First Name <span class="text-[#780000]">*</span></label>
                                            <input type="text" 
                                                   x-model="participant.first_name" 
                                                   @input="participant.first_name = participant.first_name.replace(/[^a-zA-Z\s\.\'\-]/g, ''); participant.name = (participant.first_name + ' ' + (participant.last_name || '')).trim()"
                                                   placeholder="e.g. Maria" 
                                                   class="w-full px-3.5 py-2.5 rounded-xl border text-sm text-[#1D1D1F] bg-white transition-colors"
                                                   :class="touchedStep3 && !validateName(participant.first_name) ? 'border-[#FF3B3C] bg-red-50/20' : 'border-[#D1D1D6] focus:border-[#780000]'">
                                            <span x-show="touchedStep3 && !validateName(participant.first_name)" class="text-[11px] text-[#FF3B3C] font-semibold mt-1 block">
                                                Please enter a valid first name (letters only, min 2 chars).
                                            </span>
                                        </div>

                                        <div>
                                            <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Last Name <span class="text-[#780000]">*</span></label>
                                            <input type="text" 
                                                   x-model="participant.last_name" 
                                                   @input="participant.last_name = participant.last_name.replace(/[^a-zA-Z\s\.\'\-]/g, ''); participant.name = ((participant.first_name || '') + ' ' + participant.last_name).trim()"
                                                   placeholder="e.g. Santos" 
                                                   class="w-full px-3.5 py-2.5 rounded-xl border text-sm text-[#1D1D1F] bg-white transition-colors"
                                                   :class="touchedStep3 && !validateName(participant.last_name) ? 'border-[#FF3B3C] bg-red-50/20' : 'border-[#D1D1D6] focus:border-[#780000]'">
                                            <span x-show="touchedStep3 && !validateName(participant.last_name)" class="text-[11px] text-[#FF3B3C] font-semibold mt-1 block">
                                                Please enter a valid last name (letters only, min 2 chars).
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Age & Swimming Ability -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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

                                        <div x-show="form.class_type === 'discovery'">
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

                    <!-- Contact Details -->
                    <div class="space-y-4 pt-2">
                        <h4  class="text-lg font-bold text-[#1D1D1F] pb-2">2. Contact Information</h4>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Lead First Name <span class="text-[#780000]">*</span></label>
                                <input type="text" 
                                       x-model="form.contact_first_name" 
                                       @input="form.contact_first_name = form.contact_first_name.replace(/[^a-zA-Z\s\.\'\-]/g, ''); form.contact_name = (form.contact_first_name + ' ' + (form.contact_last_name || '')).trim()"
                                       placeholder="Juan" 
                                       class="w-full px-3.5 py-2.5 rounded-xl border text-sm text-[#1D1D1F] bg-white transition-colors"
                                       :class="touchedStep3 && !validateName(form.contact_first_name) ? 'border-[#FF3B3C] bg-red-50/20' : 'border-[#D1D1D6] focus:border-[#780000]'">
                                <span x-show="touchedStep3 && !validateName(form.contact_first_name)" class="text-[11px] text-[#FF3B3C] font-semibold mt-1 block">
                                    Please enter a valid first name (min 2 chars).
                                </span>
                            </div>

                            <div>
                                <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Lead Last Name <span class="text-[#780000]">*</span></label>
                                <input type="text" 
                                       x-model="form.contact_last_name" 
                                       @input="form.contact_last_name = form.contact_last_name.replace(/[^a-zA-Z\s\.\'\-]/g, ''); form.contact_name = ((form.contact_first_name || '') + ' ' + form.contact_last_name).trim()"
                                       placeholder="Dela Cruz" 
                                       class="w-full px-3.5 py-2.5 rounded-xl border text-sm text-[#1D1D1F] bg-white transition-colors"
                                       :class="touchedStep3 && !validateName(form.contact_last_name) ? 'border-[#FF3B3C] bg-red-50/20' : 'border-[#D1D1D6] focus:border-[#780000]'">
                                <span x-show="touchedStep3 && !validateName(form.contact_last_name)" class="text-[11px] text-[#FF3B3C] font-semibold mt-1 block">
                                    Please enter a valid last name (min 2 chars).
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

                            <div class="sm:col-span-2">
                                <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Facebook Profile Link (Optional)</label>
                                <input type="text" x-model="form.contact_facebook" placeholder="facebook.com/juandelacruz" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] focus:border-[#780000] text-sm text-[#1D1D1F] bg-white">
                            </div>
                        </div>
                    </div>

                    <!-- Transportation and Add-ons -->
                    <div class="space-y-4 pt-2">
                        <h4 class="text-lg font-bold text-[#1D1D1F] pb-2">3. Transportation & Add-ons</h4>
                        
                        <div class="space-y-3">
                            <label class="block font-bold text-[#1D1D1F] text-xs">Transportation Option:</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="p-3.5 rounded-xl border transition-all cursor-pointer flex flex-col justify-between select-none"
                                       :class="form.pickup_option === 'carpool' ? 'border-[#780000] bg-[#F8EAEA]/40' : 'border-[#E5E5EA] bg-white hover:border-[#D1D1D6]'">
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

                                <label class="p-3.5 rounded-xl border transition-all cursor-pointer flex flex-col justify-between select-none"
                                       :class="form.pickup_option === 'own' ? 'border-[#780000] bg-[#F8EAEA]/40' : 'border-[#E5E5EA] bg-white hover:border-[#D1D1D6]'">
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
                                <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Carpool Pickup Hub & Schedule: <span class="text-[#780000]">*</span></label>
                                <select x-model="form.pickup_location" 
                                        class="w-full px-3.5 py-2.5 rounded-xl border text-xs text-[#1D1D1F] bg-white font-medium transition-colors cursor-pointer"
                                        :class="touchedStep3 && form.pickup_option === 'carpool' && !form.pickup_location ? 'border-[#FF3B3C] bg-red-50/20' : 'border-[#D1D1D6] focus:border-[#780000]'">
                                    <option value="" disabled selected>-- Select Carpool Pickup Hub & Schedule --</option>
                                    <template x-for="p in pickupPoints" :key="p.id">
                                        <option :value="p.name" x-text="p.name"></option>
                                    </template>
                                </select>
                                <span x-show="touchedStep3 && form.pickup_option === 'carpool' && !form.pickup_location" class="text-[11px] text-[#FF3B3C] font-semibold mt-1 block">
                                    Please select your preferred Carpool Pickup Hub to continue.
                                </span>
                            </div>
                        </div>

                        <!-- Optional Boat Dive -->
                        <div class="pt-1">
                            <label class="p-3.5 rounded-xl border transition-all cursor-pointer flex items-start justify-between gap-3"
                                   :class="form.boat_dive ? 'border-[#00C3D0] bg-[#E0F9FB]/30' : 'border-[#E5E5EA] bg-white'">
                                <div class="flex items-start gap-2.5">
                                    <input type="checkbox" x-model="form.boat_dive" class="w-4 h-4 rounded text-[#00C3D0] focus:ring-[#00C3D0] mt-0.5">
                                    <div>
                                        <span class="font-bold text-xs sm:text-sm text-[#1D1D1F] block">Boat Dive (Optional)</span>
                                        <span class="text-xs text-[#6E6E73] block">Boat ride to deeper marine sanctuaries.</span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="font-extrabold text-[#00C3D0] text-xs sm:text-sm">+₱600</span>
                                    <span class="text-[10px] text-[#6E6E73] block">/ person</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Accuracy Verification -->
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

                <!-- Live Booking Summary -->
                <div class="lg:col-span-5 space-y-4 lg:sticky lg:top-6">
                    
                    <!-- Itemized Price Calculation Summary -->
                    <div class="border border-[#E5E5EA] rounded-2xl bg-white overflow-hidden shadow-2xs">
                        <div class="bg-[#FAFAFC] px-4 py-3 border-b border-[#E5E5EA] flex items-center justify-between">
                            <span class="font-bold text-[#1D1D1F] text-sm">Booking Summary</span>
                            <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-[#EBF5FF] text-[#007DFE] capitalize" x-text="form.class_type"></span>
                        </div>

                        <div class="p-4 space-y-3 text-xs">
                            <div class="flex justify-between items-center text-[#6E6E73]">
                                <span>Base Class Rate (<span class="capitalize" x-text="form.class_type"></span> × <span x-text="form.participants.length"></span>)</span>
                                <span class="font-bold text-[#1D1D1F]" x-text="'₱' + formatNumber((pricingQuote ? pricingQuote.base_price_per_pax : calculateBasePriceUnit()) * form.participants.length)"></span>
                            </div>

                            <!-- Dynamic Pricing Adjustments -->
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

                </div>
            </div>
        </div>

        <!-- Step 4: Downpayment -->
        <div x-show="currentStep === 4" x-cloak class="space-y-6">

            <div class="max-w-xl mx-auto bg-white rounded-2xl border border-[#E5E5EA] p-6 sm:p-8 space-y-6">
                <!-- Top Navigation & Header -->
                <div class="flex items-center justify-between border-b border-[#F2F2F7] pb-4">
                    <button type="button" 
                            @click="prevStep()" 
                            class="text-xs font-bold text-[#6E6E73] hover:text-[#1D1D1F] flex items-center gap-1.5 transition-colors cursor-pointer">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                        <span>Back to Booking Details</span>
                    </button>
                    <div class="text-right">
                        <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#FF3B3C] block">Slot Hold Timer:</span>
                        <span class="text-sm sm:text-base font-mono font-black text-[#FF3B3C]" x-text="timerDisplay"></span>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <p class="text-xs sm:text-sm text-[#6E6E73]">
                        Pay your required reservation downpayment via PayMongo's secure hosted checkout page. The remaining balance will be settled at camp.
                    </p>
                </div>

                <!-- Downpayment Breakdown Box -->
                <div class="p-4 sm:p-5 rounded-2xl bg-[#FAFAFC] border border-[#E5E5EA] space-y-3">
                    <div class="flex justify-between items-center text-xs text-[#6E6E73]">
                        <span>Package: <strong class="text-[#1D1D1F] capitalize" x-text="form.class_type"></strong> (<span x-text="form.participants.length"></span> pax)</span>
                        <span class="font-bold text-[#1D1D1F]" x-text="'Total: ₱' + formatNumber(calculateTotal())"></span>
                    </div>
                    <div class="flex justify-between items-center text-xs text-[#6E6E73]">
                        <span>Remaining Balance (Payable at Camp):</span>
                        <span class="font-semibold text-[#1D1D1F]" x-text="'₱' + formatNumber(calculateTotal() - calculateDownpayment())"></span>
                    </div>
                    <div class="pt-3 border-t border-[#E5E5EA] flex justify-between items-center">
                        <div>
                            <span class="font-bold text-[#065F46] text-xs sm:text-sm block">Downpayment Due Now:</span>
                            <span class="text-[10px] sm:text-xs text-[#065F46]" x-text="'(' + (form.pickup_option === 'carpool' ? '₱3,000' : '₱2,000') + ' / head × ' + form.participants.length + ' pax)'"></span>
                        </div>
                        <strong class="text-xl sm:text-2xl font-black text-[#065F46]" x-text="'₱' + formatNumber(calculateDownpayment())"></strong>
                    </div>
                </div>

                <!-- Supported Hosted Payment Channels -->
                <div class="space-y-3">
                    <label class="block text-xs font-bold text-[#1D1D1F] uppercase tracking-wider">
                        Supported Payment Channels on Hosted Checkout:
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <!-- QR Ph -->
                        <div class="p-3 rounded-xl border border-[#E5E5EA] bg-white flex items-center gap-2.5">
                            <div>
                                <span class="font-bold text-xs text-[#1D1D1F] block">QR Ph</span>
                                <span class="text-[10px] text-[#6E6E73] block">Any PH Bank / App</span>
                            </div>
                        </div>

                        <!-- GCash -->
                        <div class="p-3 rounded-xl border border-[#E5E5EA] bg-white flex items-center gap-2.5">
                            <div>
                                <span class="font-bold text-xs text-[#1D1D1F] block">GCash</span>
                                <span class="text-[10px] text-[#6E6E73] block">Direct E-Wallet</span>
                            </div>
                        </div>

                        <!-- Maya -->
                        <div class="p-3 rounded-xl border border-[#E5E5EA] bg-white flex items-center gap-2.5">
                            <div>
                                <span class="font-bold text-xs text-[#1D1D1F] block">Maya</span>
                                <span class="text-[10px] text-[#6E6E73] block">Direct E-Wallet</span>
                            </div>
                        </div>
                    </div>

                    <p class="text-[11px] text-[#6E6E73] text-center">
                        Cards (Visa/Mastercard), GrabPay, and all Philippine banks (via QR Ph) are supported on the PayMongo checkout page.
                    </p>
                </div>

                <!-- Cancellation and Reschedule Policy -->
                <div class="p-4 sm:p-5 rounded-2xl bg-[#FAFAFC] border border-[#E5E5EA] space-y-2.5">
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

                <!-- Hosted Checkout Action Button -->
                <div class="space-y-3 pt-2">
                    <button type="button" 
                            @click="processPayment(false, true)" 
                            :disabled="submittingPayment"
                            class="w-full py-4 px-6 rounded-xl font-bold text-base text-white bg-[#780000] hover:bg-[#5E0000] active:scale-[0.99] transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                        <span x-show="!submittingPayment" class="flex items-center gap-2">
                            <span>Proceed to PayMongo Hosted Checkout</span>
                        </span>
                        <span x-show="submittingPayment" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span>Redirecting to PayMongo...</span>
                        </span>
                    </button>

                    <div class="flex items-center justify-center gap-1.5 text-xs text-[#6E6E73]">
                        <svg class="w-3.5 h-3.5 text-[#34C759]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <span>256-Bit SSL Encrypted Hosted Checkout by PayMongo</span>
                    </div>
                </div>

            </div>

        </div>

        <!-- Step 5: Confirmation & Credentials -->
        <div x-show="currentStep === 5" x-cloak class="space-y-6 text-center">
            <div class="w-16 h-16 bg-[#ECFDF5] text-[#34C759] rounded-full flex items-center justify-center mx-auto text-3xl font-extrabold border border-[#A7F3D0]">
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
            <div class="max-w-md mx-auto p-5 rounded-2xl bg-[#F8EAEA]/40 border border-[#780000]/20 space-y-4">
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

                <!-- Applied Dynamic Pricing Rules -->
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

        <!-- Step Navigation Controls -->
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
                    class="btn-primary px-5 sm:px-8 py-2.5 text-sm cursor-pointer active:scale-[0.99] transition-all">
                <span x-text="currentStep === 3 ? 'Proceed to Downpayment (₱' + formatNumber(calculateDownpayment()) + ')' : 'Continue'"></span>
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
                payment_method: 'paymongo'
            };
            this.forecast = null;
            this.pricingQuote = null;
            this.errorMessage = '';
            this.touchedStep3 = false;
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

        async processPayment(instantSimulation = false, hostedCheckout = true) {
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
</script>
@endpush
