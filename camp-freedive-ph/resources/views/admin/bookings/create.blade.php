@extends('layouts.admin')

@section('title', 'Manual Reservation Entry | Camp FreedivePH')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 text-sm" 
     x-data="adminBookingCreate({
         pickupPoints: {{ json_encode($pickupPoints) }},
         csrfToken: '{{ csrf_token() }}',
         checkWeatherUrl: '{{ route('api.weather.check') }}'
     })"
     x-init="initForm()">
    
    <!-- Top Header -->
    <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight">Add Manual Reservation</h1>
            <p class="text-xs sm:text-sm text-[#6E6E73] mt-1">
                Create a reservation for walk-in guests or direct inquiries.
            </p>
        </div>
        <a href="{{ route('admin.bookings.index') }}" class="btn-secondary px-4 py-2 text-xs sm:text-sm">
            ← Back to List
        </a>
    </div>

    <!-- Create Booking Form -->
    <form action="{{ route('admin.bookings.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Class Package & Dive Dates -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 space-y-4">
            <h3 class="text-base font-bold text-[#1D1D1F] pb-2">1. Class Package & Dive Dates</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Class Package -->
                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-2">Class Type <span class="text-[#780000]">*</span></label>
                    <select name="class_type" x-model="classType" required class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] focus:border-[#780000] text-sm text-[#1D1D1F] bg-white font-medium">
                        <option value="discovery">Discovery (Beginner Class - ₱4,250)</option>
                        <option value="fundive">Fundive (₱2,500 Certified / ₱3,300 Non-Certified)</option>
                        <option value="refinement">Refinement (Practice Dive - ₱4,100)</option>
                    </select>
                </div>

                <!-- Certified Diver Flag for Fundive -->
                <div x-show="classType === 'fundive'" x-cloak>
                    <label class="block font-bold text-[#1D1D1F] mb-2">Fundive Diver Certification</label>
                    <select name="is_certified_diver" x-model="isCertified" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white font-medium">
                        <option :value="false">Non-Certified Diver (₱3,300)</option>
                        <option :value="true">Certified Freediver (₱2,500)</option>
                    </select>
                </div>
            </div>

            <!-- 2D1N Dates -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-2">Trip Start Date (Day 1) <span class="text-[#780000]">*</span></label>
                    <input type="date" 
                           name="start_date" 
                           x-model="startDate" 
                           @change="onStartDateChange()"
                           required 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] focus:border-[#780000] text-sm text-[#1D1D1F] bg-white">
                </div>

                <div>
                    <label class="block font-bold text-[#6E6E73] mb-1">Trip End Date (Day 2 - Auto)</label>
                    <input type="date" 
                           name="end_date" 
                           x-model="endDate" 
                           readonly 
                           required 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#E5E5EA] bg-[#F2F2F7] text-sm text-[#6E6E73]">
                </div>
            </div>

            <!-- Weather Forecast Preview -->
            <div x-show="forecast" x-cloak class="p-3.5 rounded-xl text-xs space-y-1 mt-2"
                 :style="'background-color: ' + (forecast?.bg_color || '#F2F2F7') + '; color: ' + (forecast?.text_color || '#1D1D1F')">
                <div class="flex items-center justify-between font-bold">
                    <span x-text="'Forecast: ' + (forecast?.title || '')"></span>
                    <span x-text="forecast?.is_bookable ? 'Safe Conditions' : 'Critical Storm Warning'"></span>
                </div>
                <p class="text-xs" x-text="forecast?.description"></p>
            </div>
        </div>

        <!-- Participants Information -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 space-y-4">
            <div class="flex items-center justify-between pb-2">
                <h3 class="text-base font-bold text-[#1D1D1F]">2. Divers & Participants</h3>
                <span class="text-xs text-[#6E6E73]">Data Privacy Act (RA 10173) Protected</span>
            </div>

            <div class="space-y-4">
                <template x-for="(p, index) in participants" :key="index">
                    <div class="p-4 rounded-xl border border-[#E5E5EA] bg-[#FAFAFC] space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[#780000]" x-text="'Participant #' + (index + 1)"></span>
                            <button type="button" 
                                    x-show="participants.length > 1" 
                                    @click="removeParticipant(index)"
                                    class="text-xs text-[#FF3B3C] font-bold hover:underline">
                                Remove
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-[#1D1D1F] text-xs mb-2">First & Last Name <span class="text-[#780000]">*</span></label>
                                <input type="text" :name="'participants[' + index + '][name]'" x-model="p.name" required placeholder="First & Last Name" class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                            </div>
                            <div>
                                <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Age <span class="text-[#780000]">*</span></label>
                                <input type="number" :name="'participants[' + index + '][age]'" x-model="p.age" required min="8" max="80" placeholder="Age" class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Swimming Status</label>
                                <select :name="'participants[' + index + '][swimmer_status]'" x-model="p.swimmer_status" class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                                    <option value="non_swimmer">Non-Swimmer</option>
                                    <option value="casual_swimmer">Casual / Beginner Swimmer</option>
                                    <option value="swimmer">Confident Swimmer</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Health Condition Notes (Free-text)</label>
                                <input type="text" :name="'participants[' + index + '][health_condition]'" x-model="p.health_condition" placeholder="e.g. Asthma, allergies, ear issues, or None" class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <button type="button" 
                    @click="addParticipant()"
                    class="w-full py-2.5 rounded-xl border border-dashed border-[#780000]/30 hover:border-[#780000] text-[#780000] font-bold text-xs bg-[#F8EAEA]/20 hover:bg-[#F8EAEA]/50 transition-colors flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Add Another Participant</span>
            </button>
        </div>

        <!-- Primary Contact & Transportation -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 space-y-4">
            <h3 class="text-base font-bold text-[#1D1D1F] pb-2">3. Primary Contact & Transportation</h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Contact Name <span class="text-[#780000]">*</span></label>
                    <input type="text" name="contact_name" required placeholder="Lead Contact" class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                </div>
                <div>
                    <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Contact Email <span class="text-[#780000]">*</span></label>
                    <input type="email" name="contact_email" required placeholder="email@example.com" class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                </div>
                <div>
                    <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Mobile Number <span class="text-[#780000]">*</span></label>
                    <input type="tel" name="contact_phone" required placeholder="0917 123 4567" class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                </div>
            </div>

            <!-- Transportation Choice -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Transportation Option</label>
                    <select name="pickup_option" x-model="pickupOption" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white font-medium">
                        <option value="carpool">Manila Carpool Van (₱1,200 / head roundtrip)</option>
                        <option value="own">Own Transportation (₱0)</option>
                    </select>
                </div>

                <div x-show="pickupOption === 'carpool'" x-cloak>
                    <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Carpool Pickup Location & Schedule</label>
                    <select name="pickup_location" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white">
                        <template x-for="p in pickupPoints" :key="p.id">
                            <option :value="p.name" x-text="p.name"></option>
                        </template>
                    </select>
                </div>
            </div>

            <!-- Boat Dive Toggle -->
            <div class="pt-2">
                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" name="boat_dive" value="1" x-model="boatDive" class="w-4 h-4 rounded text-[#780000] focus:ring-[#780000]">
                    <span class="font-bold text-sm text-[#1D1D1F]">Include Boat Dive Add-on (+₱600 / person)</span>
                </label>
            </div>
        </div>

        <!-- Payment Recording -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 space-y-4">
            <h3 class="text-base font-bold text-[#1D1D1F] pb-2">4. Payment Recording (Offline Reception)</h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Payment Channel <span class="text-[#780000]">*</span></label>
                    <select name="payment_method" required class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white font-medium">
                        <option value="gcash">GCash (Direct Transfer)</option>
                        <option value="bpi_bank_transfer">BPI Bank Transfer</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Payment Stage <span class="text-[#780000]">*</span></label>
                    <select name="payment_stage" x-model="paymentStage" required class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white font-medium">
                        <option value="downpayment">Required Downpayment Only</option>
                        <option value="full">Full Settlement (100%)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Payment Status <span class="text-[#780000]">*</span></label>
                    <select name="payment_status" required class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white font-medium">
                        <option value="completed">Payment Verified (Completed)</option>
                        <option value="pending">Pending Verification</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Admin / Coordinator Note</label>
                <textarea name="admin_notes" rows="2" placeholder="e.g. Phone reservation confirmed via WhatsApp" class="w-full px-3.5 py-2 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white"></textarea>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-between pt-4 border-t border-[#E5E5EA]">
            <a href="{{ route('admin.bookings.index') }}" class="btn-secondary px-6 py-2.5 text-sm font-bold">
                Cancel
            </a>

            <button type="submit" class="btn-primary px-8 py-2.5 text-sm font-bold shadow-md">
                Create & Confirm Reservation
            </button>
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
        boatDive: false,
        paymentStage: 'downpayment',
        pickupPoints: config.pickupPoints || [],
        startDate: '',
        endDate: '',
        forecast: null,
        participants: [
            { name: '', age: '', swimmer_status: 'non_swimmer', health_condition: '' }
        ],

        initForm() {
            const d = new Date();
            d.setDate(d.getDate() + 7);
            this.startDate = d.toISOString().split('T')[0];
            this.onStartDateChange();
        },

        onStartDateChange() {
            if (!this.startDate) return;
            const start = new Date(this.startDate);
            const end = new Date(start);
            end.setDate(start.getDate() + 1);
            this.endDate = end.toISOString().split('T')[0];

            fetch(config.checkWeatherUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken
                },
                body: JSON.stringify({
                    start_date: this.startDate,
                    end_date: this.endDate
                })
            })
            .then(res => res.json())
            .then(data => {
                this.forecast = data;
            });
        },

        addParticipant() {
            this.participants.push({
                name: '',
                age: '',
                swimmer_status: 'non_swimmer',
                health_condition: ''
            });
        },

        removeParticipant(index) {
            if (this.participants.length > 1) {
                this.participants.splice(index, 1);
            }
        },

        calculateSubtotal() {
            let price = 4250;
            if (this.classType === 'fundive') {
                price = this.isCertified ? 2500 : 3300;
            } else if (this.classType === 'refinement') {
                price = 4100;
            }
            return price * this.participants.length;
        },

        calculateTotal() {
            const count = this.participants.length;
            const subtotal = this.calculateSubtotal();
            const carpool = (this.pickupOption === 'carpool') ? (1200 * count) : 0;
            const boat = this.boatDive ? (600 * count) : 0;
            const lgu = 350 * count;
            return subtotal + carpool + boat + lgu;
        },

        calculateDownpayment() {
            const total = this.calculateTotal();
            const count = this.participants.length;
            const dpPerHead = (this.pickupOption === 'carpool') ? 3000 : 2000;
            return Math.min(dpPerHead * count, total);
        },

        formatNumber(num) {
            return (num || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    };
}
</script>
@endpush
