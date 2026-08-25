@extends('layouts.admin')

@section('title', 'User Management | Camp FreedivePH')

@section('content')
<div class="space-y-6 text-sm" x-data="{ openAddModal: false }">
    
    <!-- Top Header & Add User Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight">User Management</h1>
            <p class="text-xs sm:text-sm text-[#6E6E73] mt-1">
                Create and manage staff accounts for coaches, administrators, and owners.
            </p>
        </div>

        <div>
            <button type="button" 
                    @click="openAddModal = true"
                    class="btn-primary px-5 py-2.5 text-xs sm:text-sm font-bold flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Provision New Account</span>
            </button>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white p-4 rounded-xl border border-[#E5E5EA]">
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2">
                <label class="block font-bold text-[#1D1D1F] text-xs mb-1">Search Staff</label>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Name, email, or phone number..." 
                       class="w-full px-3.5 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
            </div>

            <div>
                <label class="block font-bold text-[#1D1D1F] text-xs mb-1">Filter Role</label>
                <select name="role" class="w-full px-3.5 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white font-medium">
                    <option value="">All Staff Roles</option>
                    <option value="owner" {{ request('role') === 'owner' ? 'selected' : '' }}>Camp Owner</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Camp Admin</option>
                    <option value="coach" {{ request('role') === 'coach' ? 'selected' : '' }}>Freediving Coach</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label class="block font-bold text-[#1D1D1F] text-xs mb-1">Status</label>
                    <select name="status" class="w-full px-3.5 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white font-medium">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary px-4 py-2 text-xs font-bold shrink-0">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'role', 'status']))
                    <a href="{{ route('admin.users.index') }}" class="btn-secondary px-3 py-2 text-xs text-center shrink-0">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FAFAFC] border-b border-[#E5E5EA] text-[#6E6E73] font-bold">
                    <tr>
                        <th class="py-3 px-4">Staff Member</th>
                        <th class="py-3 px-4">Contact Phone</th>
                        <th class="py-3 px-4">Role</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4">Last Login</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E5EA]">
                    @forelse($users as $user)
                    <tr class="hover:bg-[#FAFAFC] transition-colors">
                        <!-- Staff Name & Email -->
                        <td class="py-3 px-4">
                            <div class="font-bold text-sm text-[#1D1D1F]">
                                {{ $user->name }}
                                @if($user->id === $currentUser->id)
                                    <span class="text-[10px] px-1.5 py-0.2 rounded bg-gray-100 text-gray-700 font-bold ml-1">You</span>
                                @endif
                            </div>
                            <span class="text-xs text-[#6E6E73] block mt-0.5">{{ $user->email }}</span>
                        </td>

                        <!-- Phone -->
                        <td class="py-3 px-4 text-[#1D1D1F]">
                            {{ $user->phone ?? '—' }}
                        </td>

                        <!-- Role Badge -->
                        <td class="py-3 px-4">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border inline-block {{ $user->role_badge['class'] }}">
                                {{ $user->role_badge['label'] }}
                            </span>
                        </td>

                        <!-- Status Badge -->
                        <td class="py-3 px-4 text-center">
                            @if($user->isActive())
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Active
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    Inactive
                                </span>
                            @endif
                        </td>

                        <!-- Last Login -->
                        <td class="py-3 px-4 text-[#6E6E73] whitespace-nowrap">
                            {{ $user->last_login_at ? $user->last_login_at->format('M d, Y g:i A') : 'Never logged in' }}
                        </td>

                        <!-- Actions -->
                        <td class="py-3 px-4 text-right whitespace-nowrap space-x-1.5">
                            @if($currentUser->isOwner() || ($currentUser->isAdmin() && $user->isCoach()))
                                <!-- Edit Profile -->
                                <a href="{{ route('admin.users.edit', $user) }}" class="px-3 py-1.5 rounded-lg border border-[#D1D1D6] hover:bg-[#F2F2F7] font-semibold text-xs text-[#1D1D1F] transition-colors inline-block">
                                    Edit Profile
                                </a>

                                <!-- Status Toggle (Cannot toggle self) -->
                                @if($user->id !== $currentUser->id)
                                    <form action="{{ route('admin.users.toggle_status', $user) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                onclick="return confirm('Are you sure you want to {{ $user->isActive() ? 'deactivate' : 'activate' }} this account?')"
                                                class="px-3 py-1.5 rounded-lg border text-xs font-semibold transition-colors {{ $user->isActive() ? 'border-[#FECACA] text-[#DC2626] hover:bg-[#FEF2F2]' : 'border-[#A7F3D0] text-[#059669] hover:bg-[#ECFDF5]' }}">
                                            {{ $user->isActive() ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                @endif
                            @else
                                <span class="text-[11px] text-[#8E8E93] italic">Managed by Owner</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-xs text-[#8E8E93]">
                            No staff accounts found matching your search.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="p-4 border-t border-[#E5E5EA] bg-[#FAFAFC]">
            {{ $users->links() }}
        </div>
        @endif
    </div>

    <!-- PROVISION ACCOUNT MODAL -->
    <div x-show="openAddModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 sm:p-8 space-y-6 shadow-2xl border border-[#E5E5EA]" @click.outside="openAddModal = false">
            <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-4">
                <div>
                    <h3 class="text-xl font-bold text-[#1D1D1F]">Provision Staff Account</h3>
                    <p class="text-xs text-[#6E6E73] mt-0.5">Create login credentials for a new instructor or administrative team member.</p>
                </div>
                <button type="button" @click="openAddModal = false" class="text-lg font-bold text-[#8E8E93] hover:text-[#1D1D1F]">✕</button>
            </div>

            <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-1">Full Name <span class="text-[#780000]">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Maria Santos" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-1">Email Address (Login Username) <span class="text-[#780000]">*</span></label>
                    <input type="email" name="email" required placeholder="name@campfreedive.ph" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-1">Mobile Phone Number</label>
                    <input type="text" name="phone" placeholder="0917 123 4567" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-1">Assigned Staff Role <span class="text-[#780000]">*</span></label>
                    <select name="role" required class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white font-medium">
                        <option value="coach" selected>Freediving Coach (Instructor Portal Access)</option>
                        @if($currentUser->isOwner())
                            <option value="admin">Camp Admin (Operations & Booking Coordinator)</option>
                        @endif
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-1">Temporary Initial Password (Optional)</label>
                    <input type="text" name="temp_password" placeholder="Leave blank to auto-generate secure password" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                    <span class="text-[11px] text-[#6E6E73] mt-1 block">Staff member will be prompted to change password on first login.</span>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E5E5EA]">
                    <button type="button" @click="openAddModal = false" class="btn-secondary px-4 py-2 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary px-6 py-2 text-xs font-bold shadow-sm">Provision Account</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
