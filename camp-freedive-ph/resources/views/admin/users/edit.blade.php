@extends('layouts.admin')

@section('title', 'Edit Profile: ' . $user->name . ' | Camp FreedivePH')

@section('content')
<div class="max-w-2xl mx-auto space-y-6 text-sm">
    
    <!-- Top Breadcrumb -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.users.index') }}" class="text-xs sm:text-sm font-semibold text-[#6E6E73] hover:text-[#1D1D1F] flex items-center gap-1.5">
            ← Back to User Management
        </a>
        <span class="text-xs text-[#8E8E93]">Admin/Owner Managed Profile</span>
    </div>

    <!-- Edit Profile Card -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 sm:p-8 shadow-sm space-y-6">
        <div class="border-b border-[#E5E5EA] pb-4">
            <h1 class="text-xl font-extrabold text-[#1D1D1F]">Edit Staff Profile</h1>
            <p class="text-xs sm:text-sm text-[#6E6E73] mt-0.5">
                Update account info for <strong>{{ $user->name }}</strong> ({{ $user->role_badge['label'] }}).
            </p>
        </div>

        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div>
                <label for="name" class="block font-bold text-[#1D1D1F] mb-2">
                    Full Name <span class="text-[#780000]">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name', $user->name) }}" 
                       required
                       class="w-full px-4 py-2.5 rounded-xl border border-[#D1D1D6] focus:border-[#780000] text-sm text-[#1D1D1F] bg-white">
                @error('name')
                    <p class="text-xs text-[#FF3B3C] font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block font-bold text-[#1D1D1F] mb-2">
                    Email Address <span class="text-[#780000]">*</span>
                </label>
                <input type="email" 
                       name="email" 
                       id="email" 
                       value="{{ old('email', $user->email) }}" 
                       required
                       class="w-full px-4 py-2.5 rounded-xl border border-[#D1D1D6] focus:border-[#780000] text-sm text-[#1D1D1F] bg-white">
                @error('email')
                    <p class="text-xs text-[#FF3B3C] font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Phone -->
            <div>
                <label for="phone" class="block font-bold text-[#1D1D1F] mb-2">
                    Mobile / Phone Number
                </label>
                <input type="tel" 
                       name="phone" 
                       id="phone" 
                       value="{{ old('phone', $user->phone) }}" 
                       placeholder="0917 123 4567"
                       class="w-full px-4 py-2.5 rounded-xl border border-[#D1D1D6] focus:border-[#780000] text-sm text-[#1D1D1F] bg-white">
                @error('phone')
                    <p class="text-xs text-[#FF3B3C] font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role & Status -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="role" class="block font-bold text-[#1D1D1F] mb-2">
                        Assigned Role <span class="text-[#780000]">*</span>
                    </label>
                    <select name="role" id="role" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white font-medium">
                        <option value="coach" {{ old('role', $user->role) === 'coach' ? 'selected' : '' }}>Freediving Coach</option>
                        @if($currentUser->isOwner())
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Camp Admin</option>
                            <option value="owner" {{ old('role', $user->role) === 'owner' ? 'selected' : '' }}>Camp Owner</option>
                        @endif
                    </select>
                </div>

                <div>
                    <label for="status" class="block font-bold text-[#1D1D1F] mb-2">
                        Account Status <span class="text-[#780000]">*</span>
                    </label>
                    <select name="status" id="status" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white font-medium">
                        <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactive / Deactivated</option>
                    </select>
                </div>
            </div>

            <!-- Optional Password Reset by Admin/Owner -->
            <div class="pt-3 border-t border-[#E5E5EA]">
                <label for="new_password" class="block font-bold text-[#1D1D1F] mb-2">
                    Assign New Temporary Password (Optional)
                </label>
                <input type="text" 
                       name="new_password" 
                       id="new_password" 
                       placeholder="Leave blank to keep existing password"
                       class="w-full px-4 py-2.5 rounded-xl border border-[#D1D1D6] focus:border-[#780000] text-sm font-mono text-[#1D1D1F] bg-white">
                <span class="text-xs text-[#6E6E73] mt-1 block">
                    Setting a new password will force the user to change it upon their next login.
                </span>
            </div>

            <!-- Submit Controls -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E5E5EA]">
                <a href="{{ route('admin.users.index') }}" class="btn-secondary px-4 py-2.5 text-sm">Cancel</a>
                <button type="submit" class="btn-primary px-6 py-2.5 text-sm font-bold shadow-sm">
                    Save Profile Changes
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
