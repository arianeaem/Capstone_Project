@extends('layouts.admin')

@section('title', 'Edit Profile: ' . $user->name . ' | Camp FreedivePH')

@section('content')
<div class="max-w-2xl mx-auto space-y-6 text-sm">
    
    <!-- Top Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.users.index') }}" class="text-xs font-semibold text-[#6E6E73] hover:text-[#780000] transition-colors flex items-center gap-1">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
            <span>Back to User Management</span>
        </a>
        <span class="text-xs text-[#8E8E93]">Admin/Owner Managed Profile</span>
    </div>

    <!-- Edit User Profile Form -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] p-5 sm:p-7 shadow-2xs space-y-5">
        <div class="border-b border-[#E5E5EA] pb-3">
            <h1 class="text-xl font-extrabold text-[#1D1D1F] tracking-tight">Edit Staff Profile</h1>
            <p class="text-xs text-[#6E6E73] mt-0.5">
                Update account info for <strong>{{ $user->name }}</strong> ({{ $user->role_badge['label'] }}).
            </p>
        </div>

        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div>
                <label for="name" class="block font-bold text-[#1D1D1F] mb-1 text-xs">
                    First & Last Name <span class="text-[#780000]">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name', $user->name) }}" 
                       required
                       placeholder="First & Last Name"
                       class="w-full px-3 py-2 rounded-lg border border-[#D1D1D6] focus:border-[#780000] text-xs text-[#1D1D1F] bg-white">
                @error('name')
                    <p class="text-xs text-[#780000] font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block font-bold text-[#1D1D1F] mb-1 text-xs">
                    Email Address <span class="text-[#780000]">*</span>
                </label>
                <input type="email" 
                       name="email" 
                       id="email" 
                       value="{{ old('email', $user->email) }}" 
                       required
                       class="w-full px-3 py-2 rounded-lg border border-[#D1D1D6] focus:border-[#780000] text-xs text-[#1D1D1F] bg-white">
                @error('email')
                    <p class="text-xs text-[#780000] font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Phone -->
            <div>
                <label for="phone" class="block font-bold text-[#1D1D1F] mb-1 text-xs">
                    Mobile / Phone Number
                </label>
                <input type="tel" 
                       name="phone" 
                       id="phone" 
                       value="{{ old('phone', $user->phone) }}" 
                       placeholder="0917 123 4567"
                       class="w-full px-3 py-2 rounded-lg border border-[#D1D1D6] focus:border-[#780000] text-xs text-[#1D1D1F] bg-white">
                @error('phone')
                    <p class="text-xs text-[#780000] font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role & Status -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="role" class="block font-bold text-[#1D1D1F] mb-1 text-xs">
                        Assigned Role <span class="text-[#780000]">*</span>
                    </label>
                    <select name="role" id="role" class="w-full px-2.5 py-2 rounded-lg border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white font-medium">
                        <option value="coach" {{ old('role', $user->role) === 'coach' ? 'selected' : '' }}>Freediving Coach</option>
                        @if($currentUser->isOwner())
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Camp Admin</option>
                            <option value="owner" {{ old('role', $user->role) === 'owner' ? 'selected' : '' }}>Camp Owner</option>
                        @endif
                    </select>
                </div>

                <div>
                    <label for="status" class="block font-bold text-[#1D1D1F] mb-1 text-xs">
                        Account Status <span class="text-[#780000]">*</span>
                    </label>
                    <select name="status" id="status" class="w-full px-2.5 py-2 rounded-lg border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white font-medium">
                        <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactive / Deactivated</option>
                    </select>
                </div>
            </div>

            <!-- Optional Password Reset by Admin/Owner -->
            <div class="pt-3 border-t border-[#E5E5EA]">
                <label for="new_password" class="block font-bold text-[#1D1D1F] mb-1 text-xs">
                    Assign New Temporary Password (Optional)
                </label>
                <input type="text" 
                       name="new_password" 
                       id="new_password" 
                       placeholder="Leave blank to keep existing password"
                       class="w-full px-3 py-2 rounded-lg border border-[#D1D1D6] focus:border-[#780000] text-xs font-mono text-[#1D1D1F] bg-white">
                <span class="text-xs text-[#6E6E73] mt-1 block">
                    Setting a new password will force the user to change it upon their next login.
                </span>
            </div>

            <!-- Submit Controls -->
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#E5E5EA]">
                <a href="{{ route('admin.users.index') }}" class="btn-secondary px-3.5 py-1.5 text-xs font-semibold">Cancel</a>
                <button type="submit" class="btn-primary px-4 py-1.5 text-xs font-bold shadow-2xs">
                    Save Profile Changes
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
