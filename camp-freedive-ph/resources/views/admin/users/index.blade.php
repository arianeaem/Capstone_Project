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
                        <img src="{{ asset('icons/icons8-plus-math-60.png') }}" class="w-5 h-5 shrink-0" alt="ProvisionNewAccount">
                <span>Provision New Account</span>
            </button>
        </div>
    </div>

    <!-- Summary Metrics (Single Box with Vertical Dividers with Top/Bottom Margin) -->
    @if(isset($stats))
    <div class="bg-white rounded-2xl border border-[#E5E5EA] p-4 sm:p-5">
        <div class="grid grid-cols-2 lg:grid-cols-4 items-center">
            <!-- Total Staff -->
            <div class="px-4 sm:px-6 py-1">
                <span class="text-xs text-[#6E6E73] font-bold uppercase tracking-wider block">Total Staff</span>
                <div class="text-2xl font-extrabold text-[#1D1D1F] mt-1">{{ $stats['total'] }}</div>
            </div>

            <!-- Coaches -->
            <div class="relative px-4 sm:px-6 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs text-[#6E6E73] font-bold uppercase tracking-wider block">Freedive Coaches</span>
                <div class="text-2xl font-extrabold text-[#008E98] mt-1">{{ $stats['coaches'] }}</div>
            </div>

            <!-- Admins & Owners -->
            <div class="relative px-4 sm:px-6 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs text-[#6E6E73] font-bold uppercase tracking-wider block">Admins & Owners</span>
                <div class="text-2xl font-extrabold text-[#780000] mt-1">{{ $stats['admins'] }}</div>
            </div>

            <!-- Active Accounts -->
            <div class="relative px-4 sm:px-6 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs text-[#34C759] font-bold uppercase tracking-wider block">Active Accounts</span>
                <div class="text-2xl font-extrabold text-[#1D1D1F] mt-1">{{ $stats['active'] }}</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Staff Directory Table Container with Integrated Toolbar Header -->
    <div class="bg-white rounded-2xl border border-[#E5E5EA] overflow-hidden shadow-2xs">
        
        <!-- Integrated Toolbar Header (Role Pills + Search + Filter Popover) -->
        <div class="p-3 sm:p-4 border-b border-[#E5E5EA]">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                
                <!-- Left: Role Pill Tabs (Primary: #780000) -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 lg:pb-0 scrollbar-none">
                    <a href="{{ request()->fullUrlWithQuery(['role' => '']) }}" 
                       class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ !request('role') ? 'bg-[#780000] text-white shadow-xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                        All Staff
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['role' => 'coach']) }}" 
                       class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ request('role') === 'coach' ? 'bg-[#780000] text-white shadow-xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                        Coaches
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['role' => 'admin']) }}" 
                       class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ request('role') === 'admin' ? 'bg-[#780000] text-white shadow-xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                        Admins
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['role' => 'owner']) }}" 
                       class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ request('role') === 'owner' ? 'bg-[#780000] text-white shadow-xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                        Owners
                    </a>
                </div>

                <!-- Right: Search Input + Filter Popover -->
                <div class="flex items-center gap-2" x-data="{ openFilters: false }">
                    <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2">
                        @if(request('role'))
                            <input type="hidden" name="role" value="{{ request('role') }}">
                        @endif
                        @if(request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif

                        <div class="relative w-48 sm:w-64">
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Search name, email, phone..." 
                                   class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl border border-[#D1D1D6] bg-[#FAFAFC] focus:bg-white focus:border-[#780000] focus:ring-1 focus:ring-[#780000]">
                            <svg class="w-3.5 h-3.5 text-[#8E8E93] absolute left-2.5 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </div>
                    </form>

                    <!-- Filter Button with Popover -->
                    <div class="relative">
                        <button type="button" 
                                @click="openFilters = !openFilters" 
                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-[#D1D1D6] bg-white hover:bg-[#FAFAFC] text-xs font-bold text-[#1D1D1F] transition-all shadow-2xs cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-[#6E6E73]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>
                            <span>Filter</span>
                            @if(request()->filled('status'))
                                <span class="w-2 h-2 rounded-full bg-[#780000]"></span>
                            @endif
                        </button>

                        <!-- Filter Popover Menu -->
                        <div x-show="openFilters" 
                             @click.outside="openFilters = false" 
                             x-cloak 
                             class="absolute right-0 mt-2 w-72 sm:w-80 bg-white rounded-2xl border border-[#E5E5EA] shadow-xl p-4 z-50 space-y-3">
                            <form method="GET" action="{{ route('admin.users.index') }}" class="space-y-3 text-xs">
                                @if(request('role'))
                                    <input type="hidden" name="role" value="{{ request('role') }}">
                                @endif
                                @if(request('search'))
                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                @endif

                                <div>
                                    <label class="block font-bold text-[#1D1D1F] mb-2">Account Status</label>
                                    <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white font-medium">
                                        <option value="">All Statuses</option>
                                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>

                                <div class="flex items-center justify-between pt-2">
                                    <a href="{{ route('admin.users.index') }}" class="text-xs text-[#8E8E93] hover:text-[#1D1D1F]">Reset All</a>
                                    <button type="button" @click="openFilters = false" class="btn-secondary px-3 py-1.5 text-xs font-semibold">Done</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if(request()->anyFilled(['search', 'status']))
                        <a href="{{ route('admin.users.index', ['role' => request('role')]) }}" 
                           class="text-xs text-[#6E6E73] hover:text-[#780000] underline font-medium px-1.5 py-1">
                            Reset
                        </a>
                    @endif
                </div>

            </div>
        </div>

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
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn-secondary px-3 py-1.5 text-xs inline-block">
                                    Edit
                                </a>

                                <!-- Status Toggle (Cannot toggle self) -->
                                @if($user->id !== $currentUser->id)
                                    <form action="{{ route('admin.users.toggle_status', $user) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                onclick="return confirm('Are you sure you want to {{ $user->isActive() ? 'deactivate' : 'activate' }} this account?')"
                                                class="{{ $user->isActive() ? 'btn-danger' : 'btn-secondary' }} px-3 py-1.5 text-xs font-semibold">
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
                    <label class="block font-bold text-[#1D1D1F] mb-2">Full Name <span class="text-[#780000]">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Maria Santos" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-2">Email Address (Login Username) <span class="text-[#780000]">*</span></label>
                    <input type="email" name="email" required placeholder="name@campfreedive.ph" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-2">Mobile Phone Number</label>
                    <input type="text" name="phone" placeholder="0917 123 4567" class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-2">Assigned Staff Role <span class="text-[#780000]">*</span></label>
                    <select name="role" required class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white font-medium">
                        <option value="coach" selected>Freediving Coach (Instructor Portal Access)</option>
                        @if($currentUser->isOwner())
                            <option value="admin">Camp Admin (Operations & Booking Coordinator)</option>
                        @endif
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-2">Temporary Initial Password (Optional)</label>
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
