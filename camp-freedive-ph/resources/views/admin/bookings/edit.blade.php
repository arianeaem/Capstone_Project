@extends('layouts.admin')

@section('title', 'Edit Booking #' . $booking->booking_number . ' | Camp FreedivePH')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 text-sm"
     x-data="{
         participants: {{ json_encode($booking->participants->map(fn($p) => [
             'id' => $p->id,
             'name' => $p->name,
             'age' => $p->age,
             'swimmer_status' => $p->swimmer_status,
             'health_condition' => $p->health_condition,
         ])) }},
         pickupPoints: {{ json_encode($pickupPoints) }}
     }">
    
    <!-- Top Breadcrumb -->
    <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.bookings.show', $booking) }}" class="text-xs text-[#6E6E73] hover:text-[#1D1D1F]">
                    ← Back to Booking #{{ $booking->booking_number }}
                </a>
            </div>
            <h1 class="text-2xl font-extrabold text-[#1D1D1F] mt-1">Edit Booking & Participant Details</h1>
        </div>
    </div>

    <form action="{{ route('admin.bookings.update', $booking) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- SECTION 1: TRIP DATES & LOGISTICS -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 shadow-sm space-y-4">
            <h3 class="text-base font-bold text-[#1D1D1F] border-b border-[#E5E5EA] pb-2">1. Dive Dates & Transportation Hub</h3>

            <!-- Hidden locked fields -->
            <input type="hidden" name="pickup_option" value="{{ $booking->pickup_option }}">
            <input type="hidden" name="boat_dive" value="{{ $booking->boat_dive ? 1 : 0 }}">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-2">Start Date (Day 1) <span class="text-[#780000]">*</span></label>
                    <input type="date" 
                           name="start_date" 
                           value="{{ old('start_date', $booking->start_date->format('Y-m-d')) }}" 
                           required 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] focus:border-[#780000] text-sm text-[#1D1D1F] bg-white">
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-2">End Date (Day 2) <span class="text-[#780000]">*</span></label>
                    <input type="date" 
                           name="end_date" 
                           value="{{ old('end_date', $booking->end_date->format('Y-m-d')) }}" 
                           required 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] focus:border-[#780000] text-sm text-[#1D1D1F] bg-white">
                </div>
            </div>

            <!-- Transportation Hub Selection (Editable only if carpool availed) -->
            <div class="pt-2">
                @if($booking->pickup_option === 'carpool')
                    <div>
                        <label class="block font-bold text-[#1D1D1F] mb-2">Carpool Pickup Location & Schedule</label>
                        <select name="pickup_location" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white font-medium">
                            @foreach($pickupPoints as $pt)
                                <option value="{{ $pt['name'] }}" {{ $booking->pickup_location === $pt['name'] ? 'selected' : '' }}>
                                    {{ $pt['name'] }}
                                </option>
                            @endforeach
                        </select>
                        <span class="text-xs text-[#6E6E73] block mt-1">Manila Carpool Service (₱1,200/person roundtrip)</span>
                    </div>
                @else
                    <div class="p-3.5 bg-[#FAFAFC] rounded-xl border border-[#E5E5EA] text-xs space-y-1">
                        <span class="text-[#6E6E73] block">Transportation Mode:</span>
                        <strong class="text-[#1D1D1F] text-sm block">Own Transportation (Direct to Anilao Resort)</strong>
                        <span class="text-[11px] text-[#8E8E93] block">Transportation mode is fixed to preserve original downpayment breakdown.</span>
                    </div>
                @endif
            </div>

            <!-- Boat Dive Status (Read-Only) -->
            <div class="pt-1 text-xs text-[#6E6E73] flex items-center gap-2">
                <span>Sanctuary Boat Dive Add-on:</span>
                <strong class="text-[#1D1D1F]">{{ $booking->boat_dive ? 'Yes (+₱600/person included)' : 'No (Shore Sanctuary Dives Only)' }}</strong>
            </div>
        </div>

        <!-- SECTION 2: PARTICIPANTS & HEALTH NOTES -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-2">
                <div>
                    <h3 class="text-base font-bold text-[#1D1D1F]">2. Divers & Participants ({{ $booking->participants->count() }} pax)</h3>
                    <p class="text-xs text-[#6E6E73]">Update participant medical and roster information for this reservation.</p>
                </div>
                <span class="text-xs text-[#6E6E73] font-semibold bg-[#F2F2F7] px-2 py-0.5 rounded">Fixed Participant Count</span>
            </div>

            <div class="space-y-4">
                <template x-for="(p, index) in participants" :key="index">
                    <div class="p-4 rounded-xl border border-[#E5E5EA] bg-[#FAFAFC] space-y-3">
                        <input type="hidden" :name="'participants[' + index + '][id]'" :value="p.id">

                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[#780000]" x-text="'Participant #' + (index + 1)"></span>
                            <span class="text-xs font-mono text-[#8E8E93]" x-text="'ID: ' + (p.id || 'Existing')"></span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-[#1D1D1F] text-xs mb-2">First & Last Name <span class="text-[#780000]">*</span></label>
                                <input type="text" :name="'participants[' + index + '][name]'" x-model="p.name" required placeholder="First & Last Name" class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                            </div>
                            <div>
                                <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Age <span class="text-[#780000]">*</span></label>
                                <input type="number" :name="'participants[' + index + '][age]'" x-model="p.age" required min="8" max="85" class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
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
                                <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Health Condition Notes</label>
                                <input type="text" :name="'participants[' + index + '][health_condition]'" x-model="p.health_condition" class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- SECTION 3: PRIMARY CONTACT -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 shadow-sm space-y-4">
            <h3 class="text-base font-bold text-[#1D1D1F] border-b border-[#E5E5EA] pb-2">3. Primary Contact</h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Contact Name <span class="text-[#780000]">*</span></label>
                    <input type="text" name="contact_name" value="{{ old('contact_name', $booking->contact_name) }}" required class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                </div>
                <div>
                    <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Email <span class="text-[#780000]">*</span></label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $booking->contact_email) }}" required class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                </div>
                <div>
                    <label class="block font-bold text-[#1D1D1F] text-xs mb-2">Mobile Phone <span class="text-[#780000]">*</span></label>
                    <input type="tel" name="contact_phone" value="{{ old('contact_phone', $booking->contact_phone) }}" required class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                </div>
            </div>
        </div>

        <!-- SECTION 4: MANDATORY REASON FOR EDIT (RA 10173 AUDIT) -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 shadow-sm space-y-3">
            <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-2">
                <h3 class="text-base font-bold text-[#1D1D1F]">4. System Audit Log Note</h3>
                <span class="text-xs text-[#780000] font-bold">Mandatory</span>
            </div>

            <div>
                <label class="block font-bold text-[#1D1D1F] text-xs mb-2">
                    Reason for Modifying Booking & Participant Details <span class="text-[#780000]">*</span>
                </label>
                <textarea name="edit_reason" rows="2" required placeholder="e.g. Corrected spelling of participant name per customer WhatsApp request" class="w-full px-3.5 py-2 rounded-xl border border-[#D1D1D6] focus:border-[#780000] text-sm text-[#1D1D1F] bg-white"></textarea>
                <span class="text-xs text-[#6E6E73] block mt-1">
                    This note and the exact changes will be recorded immutably in the system audit logs alongside your account username and IP address.
                </span>
            </div>
        </div>

        <!-- Controls -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.bookings.show', $booking) }}" class="btn-secondary px-5 py-2.5 text-sm">Cancel</a>
            <button type="submit" class="btn-primary px-7 py-2.5 text-sm font-bold shadow-md">
                Save Changes & Update Audit Trail
            </button>
        </div>
    </form>
</div>
@endsection
