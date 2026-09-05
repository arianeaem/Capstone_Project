@extends('layouts.admin')

@section('title', 'Edit Coach - ' . $coach->full_name . ' | Camp FreedivePH')

@section('content')
<div class="max-w-3xl mx-auto space-y-6 text-sm">
    
    <!-- Top Breadcrumb & Header -->
    <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-4">
        <div>
            <a href="{{ route('admin.coaches.show', $coach) }}" class="text-xs text-[#6E6E73] hover:text-[#1D1D1F]">
                ← Back to {{ $coach->full_name }} Profile
            </a>
            <h1 class="text-2xl font-extrabold text-[#1D1D1F] mt-1">Edit Coach Profile</h1>
        </div>
    </div>

    <!-- Edit Coach Form -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 sm:p-8 shadow-sm">
        <form action="{{ route('admin.coaches.update', $coach) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Basic Information -->
            <div class="space-y-4">
                <h3 class="text-base font-bold text-[#1D1D1F] pb-2">1. Personal & Contact Details</h3>

                <div>
                    <label for="full_name" class="block font-bold text-[#1D1D1F] text-xs mb-2">
                        First & Last Name <span class="text-[#780000]">*</span>
                    </label>
                    <input type="text" 
                           name="full_name" 
                           id="full_name" 
                           value="{{ old('full_name', $coach->full_name) }}" 
                           required 
                           placeholder="First & Last Name"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] focus:border-[#780000] text-sm text-[#1D1D1F] bg-white">
                    @error('full_name')
                        <span class="text-xs text-[#FF3B3C] font-semibold mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="email" class="block font-bold text-[#1D1D1F] text-xs mb-2">
                            Email Address <span class="text-[#780000]">*</span>
                        </label>
                        <input type="email" 
                               name="email" 
                               id="email" 
                               value="{{ old('email', $coach->email) }}" 
                               required 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white">
                        @error('email')
                            <span class="text-xs text-[#FF3B3C] font-semibold mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block font-bold text-[#1D1D1F] text-xs mb-2">
                            Phone Number <span class="text-[#780000]">*</span>
                        </label>
                        <input type="text" 
                               name="phone" 
                               id="phone" 
                               value="{{ old('phone', $coach->phone) }}" 
                               required 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white">
                        @error('phone')
                            <span class="text-xs text-[#FF3B3C] font-semibold mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Certification & Credentials -->
            <div class="space-y-4 pt-2">
                <h3 class="text-base font-bold text-[#1D1D1F] pb-2">2. Freediving Credentials & Expiry</h3>

                <div>
                    <label for="certification_level" class="block font-bold text-[#1D1D1F] text-xs mb-2">
                        Certification Level / Credential <span class="text-[#780000]">*</span>
                    </label>
                    <input type="text" 
                           name="certification_level" 
                           id="certification_level" 
                           value="{{ old('certification_level', $coach->certification_level) }}" 
                           required 
                           placeholder="e.g. AIDA 4 Master Freediver / PADI Freediver Instructor"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white">
                    <span class="text-xs text-[#6E6E73] mt-1 block">Free-text credential title or agency certification.</span>
                    @error('certification_level')
                        <span class="text-xs text-[#FF3B3C] font-semibold mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="certification_number" class="block font-bold text-[#1D1D1F] text-xs mb-2">
                            Certification Number <span class="text-[#780000]">*</span>
                        </label>
                        <input type="text" 
                               name="certification_number" 
                               id="certification_number" 
                               value="{{ old('certification_number', $coach->certification_number) }}" 
                               required 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white">
                        @error('certification_number')
                            <span class="text-xs text-[#FF3B3C] font-semibold mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="certification_expiry" class="block font-bold text-[#1D1D1F] text-xs mb-2">
                            Certification Expiry Date <span class="text-[#780000]">*</span>
                        </label>
                        <input type="date" 
                               name="certification_expiry" 
                               id="certification_expiry" 
                               value="{{ old('certification_expiry', $coach->certification_expiry->format('Y-m-d')) }}" 
                               required 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white">
                        @error('certification_expiry')
                            <span class="text-xs text-[#FF3B3C] font-semibold mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Status & Specialties -->
            <div class="space-y-4 pt-2">
                <h3 class="text-base font-bold text-[#1D1D1F] pb-2">3. Status & Profile Notes</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="status" class="block font-bold text-[#1D1D1F] text-xs mb-2">
                            Status <span class="text-[#780000]">*</span>
                        </label>
                        <select name="status" id="status" required class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white font-medium">
                            <option value="active" {{ old('status', $coach->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="on_leave" {{ old('status', $coach->status) === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                            <option value="pending_deactivation" {{ old('status', $coach->status) === 'pending_deactivation' ? 'selected' : '' }}>Pending Deactivation</option>
                            <option value="inactive" {{ old('status', $coach->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div>
                        <label for="joined_at" class="block font-bold text-[#1D1D1F] text-xs mb-2">
                            Date Joined Camp
                        </label>
                        <input type="date" 
                               name="joined_at" 
                               id="joined_at" 
                               value="{{ old('joined_at', $coach->joined_at ? $coach->joined_at->format('Y-m-d') : '') }}" 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white">
                    </div>
                </div>

                <div>
                    <label for="specialties_notes" class="block font-bold text-[#1D1D1F] text-xs mb-2">
                        Specialties & Notes
                    </label>
                    <textarea name="specialties_notes" 
                              id="specialties_notes" 
                              rows="3" 
                              class="w-full px-3.5 py-2 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white">{{ old('specialties_notes', $coach->specialties_notes) }}</textarea>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E5E5EA]">
                <a href="{{ route('admin.coaches.show', $coach) }}" class="btn-secondary px-5 py-2.5 text-sm">Cancel</a>
                <button type="submit" class="btn-primary px-7 py-2.5 text-sm font-bold shadow-md">
                    Update Profile
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
