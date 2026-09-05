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
                    class="btn-primary px-4 py-2 text-xs sm:text-sm font-bold flex items-center gap-1.5 shadow-2xs">
                <span>New Account</span>
            </button>
        </div>
    </div>

    <!-- Staff Summary Metrics -->
    @if(isset($stats))
    <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 shadow-2xs">
        <div class="grid grid-cols-2 lg:grid-cols-4 items-center gap-y-3">
            <!-- Total Staff -->
            <div class="px-4 py-1">
                <span class="text-xs text-[#6E6E73] font-bold uppercase tracking-wider block">Total Staff</span>
                <div class="text-2xl font-extrabold text-[#1D1D1F] mt-0.5">{{ $stats['total'] }}</div>
            </div>

            <!-- Coaches -->
            <div class="relative px-4 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs text-[#6E6E73] font-bold uppercase tracking-wider block">Freedive Coaches</span>
                <div class="text-2xl font-extrabold text-[#780000] mt-0.5">{{ $stats['coaches'] }}</div>
            </div>

            <!-- Admins & Owners -->
            <div class="relative px-4 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs text-[#6E6E73] font-bold uppercase tracking-wider block">Admins & Owners</span>
                <div class="text-2xl font-extrabold text-[#1D1D1F] mt-0.5">{{ $stats['admins'] }}</div>
            </div>

            <!-- Active Accounts -->
            <div class="relative px-4 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs text-[#6E6E73] font-bold uppercase tracking-wider block">Active Accounts</span>
                <div class="text-2xl font-extrabold text-emerald-700 mt-0.5">{{ $stats['active'] }}</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Staff Directory Table -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] overflow-hidden shadow-2xs">
        
        <!-- Table Toolbar Header -->
        <div class="p-3 sm:p-4 border-b border-[#E5E5EA]">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                
                <!-- Role Filter Tabs -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 lg:pb-0 scrollbar-none">
                    <a href="{{ request()->fullUrlWithQuery(['role' => '']) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 {{ !request('role') ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                        All Staff
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['role' => 'coach']) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 {{ request('role') === 'coach' ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                        Coaches
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['role' => 'admin']) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 {{ request('role') === 'admin' ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                        Admins
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['role' => 'owner']) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 {{ request('role') === 'owner' ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                        Owners
                    </a>
                </div>

                <!-- Search and Filter Controls -->
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
                                   class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-[#D1D1D6] bg-[#FAFAFC] focus:bg-white focus:border-[#780000]">
                            <svg class="w-3.5 h-3.5 text-[#8E8E93] absolute left-2.5 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </div>
                    </form>

                    <!-- Filter Controls -->
                    <div class="relative">
                        <button type="button" 
                                @click="openFilters = !openFilters" 
                                class="btn-secondary flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-[#6E6E73]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>
                            <span>Filter</span>
                            @if(request()->filled('status'))
                                <span class="w-2 h-2 rounded-full bg-[#780000]"></span>
                            @endif
                        </button>

                        <!-- Filter Form Dropdown -->
                        <div x-show="openFilters" 
                             @click.outside="openFilters = false" 
                             x-cloak 
                             class="absolute right-0 mt-2 w-72 bg-white rounded-xl border border-[#E5E5EA] shadow-xl p-4 z-50 space-y-3">
                            <div class="flex items-center justify-between pb-2">
                                <h4 class="font-bold text-xs text-[#1D1D1F]">Filter Staff</h4>
                                <a href="{{ route('admin.users.index') }}" class="text-[11px] text-[#780000] hover:underline font-semibold">Reset</a>
                            </div>

                            <form method="GET" action="{{ route('admin.users.index') }}" class="space-y-3 text-xs">
                                @if(request('role'))
                                    <input type="hidden" name="role" value="{{ request('role') }}">
                                @endif
                                @if(request('search'))
                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                @endif

                                <div>
                                    <label class="block font-semibold text-[#6E6E73] mb-1">Account Status</label>
                                    <select name="status" class="w-full px-2.5 py-1.5 rounded-lg border border-[#D1D1D6] text-xs">
                                        <option value="">All Statuses</option>
                                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>

                                <div class="pt-2 border-t border-[#E5E5EA] flex justify-end">
                                    <button type="submit" class="btn-primary w-full py-1.5 text-xs font-bold">
                                        Apply Filter
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FAFAFC] border-b border-[#E5E5EA] text-[#6E6E73] font-bold">
                    <tr>
                        <th class="py-3 px-4 text-left">Staff Member</th>
                        <th class="py-3 px-4 text-left">Contact Phone</th>
                        <th class="py-3 px-4 text-left">Role</th>
                        <th class="py-3 px-4 text-left">Status</th>
                        <th class="py-3 px-4 text-left">Last Login</th>
                        <th class="py-3 px-4 text-right pr-6">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E5EA]">
                    @forelse($users as $user)
                    <tr onclick="window.location='{{ route('admin.users.edit', $user) }}'" class="hover:bg-[#FAFAFC] cursor-pointer transition-colors group">
                        <!-- Staff Details -->
                        <td class="py-3 px-4 text-left">
                            <div class="font-bold text-sm text-[#1D1D1F] group-hover:text-[#780000]">
                                {{ $user->name }}
                                @if($user->id === $currentUser->id)
                                    <span class="text-[10px] px-1.5 py-0.2 rounded bg-gray-100 text-gray-700 font-bold ml-1">You</span>
                                @endif
                            </div>
                            <span class="text-xs text-[#6E6E73] block mt-0.5">{{ $user->email }}</span>
                        </td>

                        <!-- Phone Number -->
                        <td class="py-3 px-4 text-left text-[#1D1D1F]">
                            {{ $user->phone ?? '—' }}
                        </td>

                        <!-- Role Badge -->
                        <td class="py-3 px-4 text-left">
                            <span class="px-2 py-0.5 rounded-md text-xs font-bold border inline-block {{ $user->role_badge['class'] }}">
                                {{ $user->role_badge['label'] }}
                            </span>
                        </td>

                        <!-- Status Badge -->
                        <td class="py-3 px-4 text-left">
                            @if($user->isActive())
                                <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Active
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    Inactive
                                </span>
                            @endif
                        </td>

                        <!-- Last Login Timestamp -->
                        <td class="py-3 px-4 text-left text-[#6E6E73] whitespace-nowrap">
                            {{ $user->last_login_at ? $user->last_login_at->format('M d, Y g:i A') : 'Never logged in' }}
                        </td>

                        <!-- Actions Menu -->
                        <td class="py-3 px-4 text-right pr-6 whitespace-nowrap" onclick="event.stopPropagation()">
                            @if($currentUser->isOwner() || ($currentUser->isAdmin() && $user->isCoach()))
                                <div class="relative inline-block text-left" x-data="{ openMenu: false }">
                                    <button type="button" 
                                            @click="openMenu = !openMenu" 
                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] transition-colors cursor-pointer"
                                            title="Actions">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                            <circle cx="12" cy="5" r="2"></circle>
                                            <circle cx="12" cy="12" r="2"></circle>
                                            <circle cx="12" cy="19" r="2"></circle>
                                        </svg>
                                    </button>

                                    <!-- Actions Dropdown -->
                                    <div x-show="openMenu" 
                                         @click.outside="openMenu = false" 
                                         x-cloak 
                                         class="absolute right-0 mt-1 w-44 bg-white rounded-xl border border-[#E5E5EA] shadow-lg p-1.5 z-50 space-y-1 text-left">
                                        
                                        <!-- Edit Account -->
                                        <a href="{{ route('admin.users.edit', $user) }}" 
                                           class="w-full flex items-center gap-2 px-3 py-2 text-xs font-semibold text-[#1D1D1F] hover:bg-[#F2F2F7] rounded-lg transition-colors">
                                            <span>Edit Account</span>
                                        </a>

                                        <!-- Account Status Toggle -->
                                        @if($user->id !== $currentUser->id)
                                            <form action="{{ route('admin.users.toggle_status', $user) }}" method="POST" class="block w-full">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                        onclick="return confirm('Are you sure you want to {{ $user->isActive() ? 'deactivate' : 'activate' }} this account?')"
                                                        class="w-full flex items-center gap-2 px-3 py-2 text-xs font-semibold {{ $user->isActive() ? 'text-[#780000] hover:bg-[#FEF2F2]' : 'text-emerald-700 hover:bg-emerald-50' }} rounded-lg transition-colors text-left cursor-pointer">
                                                    <span>{{ $user->isActive() ? 'Deactivate' : 'Activate' }}</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <span class="text-xs text-[#8E8E93] italic">Owner only</span>
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

    <!-- Provision Account Modal -->
    <div x-show="openAddModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-lg w-full p-6 sm:p-7 space-y-5 shadow-2xl border border-[#E5E5EA]" @click.outside="openAddModal = false">
            <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                <div>
                    <h3 class="text-lg font-bold text-[#1D1D1F]">Provision Staff Account</h3>
                    <p class="text-xs text-[#6E6E73] mt-0.5">Create login credentials for a new instructor or administrative team member.</p>
                </div>
                <button type="button" @click="openAddModal = false" class="text-lg font-bold text-[#8E8E93] hover:text-[#1D1D1F]">✕</button>
            </div>

            <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-3.5 text-xs">
                @csrf

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-1">First & Last Name <span class="text-[#780000]">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Maria Santos" class="w-full px-3 py-2 rounded-lg border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-1">Email Address (Login Username) <span class="text-[#780000]">*</span></label>
                    <input type="email" name="email" required placeholder="name@campfreedive.ph" class="w-full px-3 py-2 rounded-lg border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-1">Mobile Phone Number</label>
                    <input type="text" name="phone" placeholder="0917 123 4567" class="w-full px-3 py-2 rounded-lg border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-1">Assigned Staff Role <span class="text-[#780000]">*</span></label>
                    <select name="role" required class="w-full px-3 py-2 rounded-lg border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white font-medium">
                        <option value="coach" selected>Freediving Coach (Instructor Portal Access)</option>
                        @if($currentUser->isOwner())
                            <option value="admin">Camp Admin (Operations & Booking Coordinator)</option>
                        @endif
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-1">Temporary Initial Password (Optional)</label>
                    <input type="text" name="temp_password" placeholder="Leave blank to auto-generate secure password" class="w-full px-3 py-2 rounded-lg border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                    <span class="text-xs text-[#6E6E73] mt-1 block">Staff member will be prompted to change password on first login.</span>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#E5E5EA]">
                    <button type="button" @click="openAddModal = false" class="btn-secondary px-3.5 py-1.5 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary px-4 py-1.5 text-xs font-bold shadow-2xs">Provision Account</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
