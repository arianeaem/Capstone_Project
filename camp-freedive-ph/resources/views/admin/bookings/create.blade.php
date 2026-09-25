@extends('layouts.admin')

@section('title', 'Create Booking | Camp FreedivePH')

@section('breadcrumb')
    <a href="{{ portal_route('bookings.index') }}" class="text-[#6E6E73] hover:text-[#780000] font-medium transition-colors">Bookings</a>
    <svg class="w-3.5 h-3.5 text-[#8E8E93] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
    <span class="font-bold text-[#1D1D1F]">Create Booking</span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6 text-sm" 
     x-data="adminBookingCreate({
         pickupPoints: {{ json_encode($pickupPoints) }},
         csrfToken: '{{ csrf_token() }}',
         checkWeatherUrl: '{{ route('api.weather.check') }}'
     })"
     x-init="initForm()">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight">Create Booking</h1>
        </div>
    </div>

    <!-- Create Booking Form (Single Unified Card) -->
    <form action="{{ route('admin.bookings.store') }}" method="POST">
        @csrf

        <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 sm:p-8 shadow-2xs space-y-8">
            
            <!-- 1. Class Package & Dive Dates -->
            <div class="space-y-4">
                <h3 class="text-base font-extrabold text-[#1D1D1F]">1. Class Package & Dive Dates</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Class Package -->
                    <div>
                        <label class="block font-bold text-[#1D1D1F] text-xs sm:text-sm mb-1.5">Class Type <span class="text-[#780000]">*</span></label>
                        <select name="class_type" x-model="classType" required class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] focus:border-[#780000] text-sm text-[#1D1D1F] bg-white font-medium">
                            <option value="discovery">Discovery (Beginner Class - ₱4,250)</option>
                            <option value="fundive">Fundive (₱2,500 Certified / ₱3,300 Non-Certified)</option>
                            <option value="refinement">Refinement (Practice Dive - ₱4,100)</option>
                        </select>
                    </div>

                    <!-- Certified Diver Flag for Fundive -->
                    <div x-show="classType === 'fundive'" x-cloak>
                        <label class="block font-bold text-[#1D1D1F] text-xs sm:text-sm mb-1.5">Fundive Diver Certification</label>
                        <select name="is_certified_diver" x-model="isCertified" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white font-medium">
                            <option :value="false">Non-Certified Diver (₱3,300)</option>
                            <option :value="true">Certified Freediver (₱2,500)</option>
                        </select>
                    </div>
                </div>

                <!-- 2D1N Dates -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                    <div>
                        <label class="block font-bold text-[#1D1D1F] text-xs sm:text-sm mb-1.5">Trip Start Date <span class="text-[#780000]">*</span></label>
                        <input type="date" 
                               name="start_date" 
                               x-model="startDate" 
                               @change="onStartDateChange()"
                               required 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] focus:border-[#780000] text-sm text-[#1D1D1F] bg-white">
                    </div>

                    <div>
                        <label class="block font-bold text-[#6E6E73] text-xs sm:text-sm mb-1.5">Trip End Date</label>
                        <input type="date" 
                               name="end_date" 
                               x-model="endDate" 
                               readonly 
                               required 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#E5E5EA] bg-[#F2F2F7] text-sm text-[#6E6E73]">
                    </div>
                </div>

                <!-- Weather Forecast (Exact Design Matching Reference Picture) -->
                <div class="pt-2">
                    <!-- Loading State -->
                    <div x-show="weatherLoading" x-cloak class="p-4 rounded-xl bg-[#F2F2F7] border border-[#E5E5EA] space-y-2">
                        <div class="flex items-center justify-between text-xs sm:text-sm">
                            <span class="font-semibold text-[#1D1D1F]">Evaluating weather & marine conditions...</span>
                            <span class="font-bold text-[#00C3D0]">Checking</span>
                        </div>
                        <div class="w-full bg-[#E5E5EA] h-1.5 rounded-full overflow-hidden">
                            <div class="bg-[#00C3D0] h-full w-2/3 animate-pulse"></div>
                        </div>
                    </div>

                    <!-- Weather Assessment Results (Image Style: Overall Top, Day 1 Left, Day 2 Right) -->
                    <template x-if="forecast && !weatherLoading && !forecast.is_benchmark">
                        <div class="space-y-3 pt-2">
                            <!-- Overall Assessment Title & 5 Lines Indicator -->
                            <div class="space-y-1.5">
                                <div class="flex items-center gap-3">
                                    <span class="text-base sm:text-lg font-black uppercase tracking-wide"
                                          :class="getSafetyTextClass(forecast.overall_classification)"
                                          x-text="forecast.overall_classification"></span>

                                    <!-- 5 Lines Indicator -->
                                    <div class="flex items-center gap-1.5">
                                        <template x-for="i in 5" :key="i">
                                            <div class="h-1.5 w-5 sm:w-6 rounded-full transition-all duration-300"
                                                 :class="i <= getSafetyScore(forecast.overall_classification) ? getSafetyBarClass(forecast.overall_classification) : 'bg-[#E5E5EA]'"></div>
                                        </template>
                                    </div>
                                </div>

                                <p class="text-xs sm:text-sm text-[#6E6E73] leading-relaxed" x-text="forecast.description"></p>
                            </div>

                            <!-- Day 1 (Left) & Day 2 (Right) Side-by-Side Grid -->
                            <template x-if="forecast.day1 && forecast.day2">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-3">
                                    <!-- Day 1 (Left) -->
                                    <div class="relative pl-3.5 space-y-1 text-sm">
                                        <div class="absolute left-0 top-0 bottom-0 w-1 rounded-full transition-colors duration-200"
                                             :class="getSafetyBarClass(forecast.day1.classification)"></div>
                                        <div class="font-extrabold text-xs uppercase tracking-wider text-[#1D1D1F]">DAY 1</div>
                                        <div class="flex items-center justify-between text-xs sm:text-sm">
                                            <span class="font-bold text-[#1D1D1F]" x-text="forecast.day1.date"></span>
                                            <span class="text-[#6E6E73] text-xs">Worst Hour: <strong class="text-[#1D1D1F]" x-text="forecast.day1.worst_hour"></strong></span>
                                        </div>
                                        <p class="text-xs text-[#6E6E73] leading-relaxed pt-0.5" x-text="forecast.day1.recommended_action"></p>
                                    </div>

                                    <!-- Day 2 (Right) -->
                                    <div class="relative pl-3.5 space-y-1 text-sm">
                                        <div class="absolute left-0 top-0 bottom-0 w-1 rounded-full transition-colors duration-200"
                                             :class="getSafetyBarClass(forecast.day2.classification)"></div>
                                        <div class="font-extrabold text-xs uppercase tracking-wider text-[#1D1D1F]">DAY 2</div>
                                        <div class="flex items-center justify-between text-xs sm:text-sm">
                                            <span class="font-bold text-[#1D1D1F]" x-text="forecast.day2.date"></span>
                                            <span class="text-[#6E6E73] text-xs">Worst Hour: <strong class="text-[#1D1D1F]" x-text="forecast.day2.worst_hour"></strong></span>
                                        </div>
                                        <p class="text-xs text-[#6E6E73] leading-relaxed pt-0.5" x-text="forecast.day2.recommended_action"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Empty / Beyond 16 days state -->
                    <template x-if="startDate && !weatherLoading && (!forecast || forecast.is_benchmark)">
                        <div class="p-3.5 rounded-xl border border-[#E5E5EA] bg-[#FAFAFA] text-xs text-[#6E6E73]">
                            Marine condition evaluations are unavailable for dates beyond 16 days. Reservation may proceed; conditions will be verified prior to camp.
                        </div>
                    </template>
                </div>
            </div>

            <!-- 2. Divers & Participants -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-extrabold text-[#1D1D1F]">2. Divers & Participants</h3>
                    <button type="button" 
                            @click="addParticipant()"
                            class="btn-primary text-xs sm:text-sm px-3.5 py-1.5 font-bold flex items-center gap-1.5 shadow-2xs">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                        <span>Add Participant</span>
                    </button>
                </div>

                <div class="space-y-4">
                    <template x-for="(p, index) in participants" :key="index">
                        <div class="p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-[#780000] text-xs uppercase tracking-wider" x-text="'Participant #' + (index + 1)"></span>
                                <button type="button" 
                                        x-show="participants.length > 1" 
                                        @click="removeParticipant(index)"
                                        class="text-xs text-[#FF3B3C] font-bold hover:underline">
                                    Remove
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block font-bold text-[#1D1D1F] text-xs mb-1.5">First Name <span class="text-[#780000]">*</span></label>
                                    <input type="text" :name="'participants[' + index + '][first_name]'" x-model="p.first_name" required placeholder="First Name" class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white">
                                </div>
                                <div>
                                    <label class="block font-bold text-[#1D1D1F] text-xs mb-1.5">Last Name <span class="text-[#780000]">*</span></label>
                                    <input type="text" :name="'participants[' + index + '][last_name]'" x-model="p.last_name" required placeholder="Last Name" class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white">
                                </div>
                                <div>
                                    <label class="block font-bold text-[#1D1D1F] text-xs mb-1.5">Age <span class="text-[#780000]">*</span></label>
                                    <input type="number" :name="'participants[' + index + '][age]'" x-model="p.age" required min="8" max="80" placeholder="Age" class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-bold text-[#1D1D1F] text-xs mb-1.5">Swimming Status</label>
                                    <select :name="'participants[' + index + '][swimmer_status]'" x-model="p.swimmer_status" class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white font-medium">
                                        <option value="non_swimmer">Non-Swimmer</option>
                                        <option value="casual_swimmer">Casual / Beginner Swimmer</option>
                                        <option value="swimmer">Confident Swimmer</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-bold text-[#1D1D1F] text-xs mb-1.5">Health Condition Notes (Free-text)</label>
                                    <input type="text" :name="'participants[' + index + '][health_condition]'" x-model="p.health_condition" placeholder="e.g. Asthma, allergies, ear issues, or None" class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- 3. Primary Contact & Transportation -->
            <div class="space-y-4">
                <h3 class="text-base font-extrabold text-[#1D1D1F]">3. Primary Contact & Transportation</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-[#1D1D1F] text-xs sm:text-sm mb-1.5">First Name <span class="text-[#780000]">*</span></label>
                        <input type="text" name="first_name" required placeholder="First Name" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white">
                    </div>
                    <div>
                        <label class="block font-bold text-[#1D1D1F] text-xs sm:text-sm mb-1.5">Last Name <span class="text-[#780000]">*</span></label>
                        <input type="text" name="last_name" required placeholder="Last Name" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-[#1D1D1F] text-xs sm:text-sm mb-1.5">Contact Email <span class="text-[#780000]">*</span></label>
                        <input type="email" name="contact_email" required placeholder="email@example.com" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white">
                    </div>
                    <div>
                        <label class="block font-bold text-[#1D1D1F] text-xs sm:text-sm mb-1.5">Mobile Number <span class="text-[#780000]">*</span></label>
                        <input type="tel" name="contact_phone" required placeholder="0917 123 4567" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white">
                    </div>
                    <div>
                        <label class="block font-bold text-[#1D1D1F] text-xs sm:text-sm mb-1.5">Facebook / Messenger Profile</label>
                        <input type="text" name="contact_facebook" placeholder="fb.com/username (Optional)" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white">
                    </div>
                </div>

                <!-- Transportation Choice -->
                <div class="space-y-3 pt-1">
                    <label class="block font-bold text-[#1D1D1F] text-xs sm:text-sm">Transportation Option <span class="text-[#780000]">*</span></label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" role="radiogroup" aria-label="Transportation Option">
                        <label tabindex="0"
                               role="radio"
                               :aria-checked="pickupOption === 'carpool'"
                               @keydown.enter.prevent="pickupOption = 'carpool'"
                               @keydown.space.prevent="pickupOption = 'carpool'"
                               class="p-3.5 rounded-xl border border-transparent transition-all cursor-pointer flex flex-col justify-between select-none bg-transparent hover:bg-transparent hover:ring-1 hover:ring-[#780000] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]"
                               :class="pickupOption === 'carpool' ? 'ring-2 ring-[#780000]' : ''">
                            <input type="radio" name="pickup_option" value="carpool" x-model="pickupOption" class="hidden">
                            <div class="space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-extrabold text-sm text-[#1D1D1F]">Manila Carpool Van</span>
                                    <span x-show="pickupOption === 'carpool'" class="w-2.5 h-2.5 rounded-full bg-[#780000]"></span>
                                </div>
                                <span class="font-bold text-[#780000] text-xs sm:text-sm block">₱1,200 / person</span>
                                <span class="text-xs sm:text-sm text-[#780000] font-semibold block">(DP: ₱3,000 / head)</span>
                            </div>
                        </label>

                        <label tabindex="0"
                               role="radio"
                               :aria-checked="pickupOption === 'own'"
                               @keydown.enter.prevent="pickupOption = 'own'"
                               @keydown.space.prevent="pickupOption = 'own'"
                               class="p-3.5 rounded-xl border border-transparent transition-all cursor-pointer flex flex-col justify-between select-none bg-transparent hover:bg-transparent hover:ring-1 hover:ring-[#780000] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]"
                               :class="pickupOption === 'own' ? 'ring-2 ring-[#780000]' : ''">
                            <input type="radio" name="pickup_option" value="own" x-model="pickupOption" class="hidden">
                            <div class="space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-extrabold text-sm text-[#1D1D1F]">Own Vehicle / Commute</span>
                                    <span x-show="pickupOption === 'own'" class="w-2.5 h-2.5 rounded-full bg-[#780000]"></span>
                                </div>
                                <span class="font-bold text-[#1D1D1F] text-xs sm:text-sm block">₱0 (Self-arranged)</span>
                                <span class="text-xs sm:text-sm text-[#6E6E73] font-semibold block">(DP: ₱2,000 / head)</span>
                            </div>
                        </label>
                    </div>

                    <!-- Carpool Pickup Location & Schedule -->
                    <div x-show="pickupOption === 'carpool'" x-cloak class="pt-1">
                        <label class="block font-bold text-[#1D1D1F] text-xs sm:text-sm mb-1.5">Carpool Pickup Location & Schedule <span class="text-[#780000]">*</span></label>
                        <select name="pickup_location" 
                                x-model="pickupLocation"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-transparent text-sm text-[#1D1D1F] bg-transparent font-medium transition-all cursor-pointer hover:bg-transparent hover:ring-1 hover:ring-[#780000] focus:outline-none focus:ring-2 focus:ring-[#780000]"
                                :class="pickupLocation ? 'ring-2 ring-[#780000]' : 'ring-1 ring-[#D1D1D6]'">
                            <option value="" disabled selected class="bg-white text-[#1D1D1F]">-- Select Carpool Pickup Hub & Schedule --</option>
                            <template x-for="p in pickupPoints" :key="p.id">
                                <option :value="p.name" x-text="p.name" class="bg-white text-[#1D1D1F]"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <!-- Optional Boat Dive -->
                <div class="pt-1">
                    <label tabindex="0"
                           role="checkbox"
                           :aria-checked="boatDive"
                           @keydown.enter.prevent="boatDive = !boatDive"
                           @keydown.space.prevent="boatDive = !boatDive"
                           @click="boatDive = !boatDive"
                           class="p-3.5 rounded-xl border border-transparent transition-all cursor-pointer flex items-start justify-between gap-3 select-none bg-transparent hover:bg-transparent hover:ring-1 hover:ring-[#780000] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]"
                           :class="boatDive ? 'ring-2 ring-[#780000]' : ''">
                        <input type="checkbox" name="boat_dive" value="1" x-model="boatDive" class="hidden">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-sm text-[#1D1D1F] block">Boat Dive (Optional)</span>
                                <span x-show="boatDive" class="w-2.5 h-2.5 rounded-full bg-[#780000]"></span>
                            </div>
                            <span class="text-xs sm:text-sm text-[#6E6E73] block leading-snug">Boat ride to deeper marine sanctuaries.</span>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="font-extrabold text-[#780000] text-xs sm:text-sm">+₱600</span>
                            <span class="text-[11px] sm:text-xs text-[#6E6E73] block">/ person</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- 4. Payment Recording (Offline Reception) -->
            <div class="space-y-4">
                <h3 class="text-base font-extrabold text-[#1D1D1F]">4. Payment Recording</h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-[#1D1D1F] text-xs sm:text-sm mb-1.5">Payment Channel <span class="text-[#780000]">*</span></label>
                        <select name="payment_method" required class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white font-medium">
                            <option value="gcash">GCash (Direct Transfer)</option>
                            <option value="bpi_bank_transfer">BPI Bank Transfer</option>
                            <option value="maya">Maya / PayMaya</option>
                            <option value="bdo">BDO Unibank</option>
                            <option value="unionbank">UnionBank</option>
                            <option value="cash">Cash (On-Site / Reception)</option>
                            <option value="other">Other Bank / E-Wallet</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-[#1D1D1F] text-xs sm:text-sm mb-1.5">Payment Stage <span class="text-[#780000]">*</span></label>
                        <select name="payment_stage" x-model="paymentStage" required class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white font-medium">
                            <option value="downpayment">Required Downpayment Only</option>
                            <option value="full">Full Settlement (100%)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-[#1D1D1F] text-xs sm:text-sm mb-1.5">Payment Status <span class="text-[#780000]">*</span></label>
                        <select name="payment_status" required class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white font-medium">
                            <option value="completed">Payment Verified (Completed)</option>
                            <option value="pending">Pending Verification</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] text-xs sm:text-sm mb-1.5">Admin / Coordinator Note</label>
                    <textarea name="admin_notes" rows="2" placeholder="e.g. Phone reservation confirmed via WhatsApp" class="w-full px-3.5 py-2 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white"></textarea>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-[#E5E5EA]">
                <a href="{{ route('admin.bookings.index') }}" class="btn-secondary min-h-[44px] px-6 py-2.5 text-sm font-bold inline-flex items-center justify-center active:scale-[0.99] transition-all focus:outline-none focus:ring-2 focus:ring-[#780000]">
                    Cancel
                </a>

                <button type="submit" class="btn-primary min-h-[44px] px-8 py-2.5 text-sm font-bold shadow-md inline-flex items-center justify-center active:scale-[0.99] transition-all focus:outline-none focus:ring-2 focus:ring-[#780000]">
                    Create Booking
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function adminBookingCreate(config) {
    return {
        classType: 'discovery',
        isCertified: false,
        pickupOption: 'carpool',
        pickupLocation: config.pickupPoints && config.pickupPoints.length > 0 ? config.pickupPoints[0].name : '',
        boatDive: false,
        paymentStage: 'downpayment',
        pickupPoints: config.pickupPoints || [],
        startDate: '',
        endDate: '',
        forecast: null,
        weatherLoading: false,
        participants: [
            { first_name: '', last_name: '', age: '', swimmer_status: 'non_swimmer', health_condition: '' }
        ],

        initForm() {
            // Default trip start date to none
        },

        async onStartDateChange() {
            if (!this.startDate) {
                this.endDate = '';
                this.forecast = null;
                return;
            }
            const start = new Date(this.startDate);
            const end = new Date(start);
            end.setDate(start.getDate() + 1);
            this.endDate = end.toISOString().split('T')[0];

            this.weatherLoading = true;
            try {
                const response = await fetch(config.checkWeatherUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken
                    },
                    body: JSON.stringify({
                        start_date: this.startDate,
                        end_date: this.endDate
                    })
                });
                const data = await response.json();
                this.forecast = data;
            } catch (e) {
                console.error('Weather check error:', e);
            } finally {
                this.weatherLoading = false;
            }
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

        addParticipant() {
            this.participants.push({
                first_name: '',
                last_name: '',
                age: '',
                swimmer_status: 'non_swimmer',
                health_condition: ''
            });
        },

        removeParticipant(index) {
            if (this.participants.length > 1) {
                this.participants.splice(index, 1);
            }
        }
    };
}
</script>
@endpush
