@extends('layouts.admin')

@section('title', 'System Audit Logs | Camp FreedivePH')

@section('content')
<div class="space-y-6 text-sm">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-5">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight">System Audit Logs</h1>
            </div>
            <p class="text-xs sm:text-sm text-[#6E6E73] mt-1">
                Review account activity, security logs, and changes made across the system.
            </p>
        </div>
    </div>

    <!-- Audit Logs Ledger -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] overflow-hidden shadow-2xs">
        
        <!-- Audit Log Filters and Search -->
        <div class="p-3 sm:p-4 border-b border-[#E5E5EA]">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                
                <!-- Action Category Filters -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 lg:pb-0 scrollbar-none">
                    <a href="{{ request()->fullUrlWithQuery(['action' => '']) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 {{ !request('action') ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                        All Events
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['action' => 'LOGIN_SUCCESS']) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 {{ request('action') === 'LOGIN_SUCCESS' ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                        Logins
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['action' => 'LOGIN_FAILED']) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 {{ request('action') === 'LOGIN_FAILED' ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                        Failed Logins
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['action' => 'USER_UPDATED']) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 {{ request('action') === 'USER_UPDATED' ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                        User Updates
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['action' => 'USER_STATUS_TOGGLED']) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 {{ request('action') === 'USER_STATUS_TOGGLED' ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                        Status Toggles
                    </a>
                </div>

                <!-- Search and Advanced Filters -->
                <div class="flex items-center gap-2" x-data="{ openFilters: false }">
                    <form method="GET" action="{{ route('admin.audit_logs.index') }}" class="flex items-center gap-2">
                        @if(request('action'))
                            <input type="hidden" name="action" value="{{ request('action') }}">
                        @endif
                        @if(request('date_from'))
                            <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                        @endif
                        @if(request('date_to'))
                            <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                        @endif

                        <div class="relative w-48 sm:w-64">
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Search actor, details, IP..." 
                                   class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-[#D1D1D6] bg-[#FAFAFC] focus:bg-white focus:border-[#780000]">
                            <svg class="w-3.5 h-3.5 text-[#8E8E93] absolute left-2.5 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </div>
                    </form>

                    <!-- Advanced Filter Toggle -->
                    <div class="relative">
                        <button type="button" 
                                @click="openFilters = !openFilters" 
                                class="btn-secondary flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-[#6E6E73]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>
                            <span>Filter</span>
                            @if(request()->anyFilled(['action', 'date_from', 'date_to']))
                                <span class="w-2 h-2 rounded-full bg-[#780000]"></span>
                            @endif
                        </button>

                        <!-- Advanced Filter Options -->
                        <div x-show="openFilters" 
                             @click.outside="openFilters = false" 
                             x-cloak 
                             class="absolute right-0 mt-2 w-72 sm:w-80 bg-white rounded-xl border border-[#E5E5EA] shadow-xl p-4 z-50 space-y-3">
                            <div class="flex items-center justify-between pb-2">
                                <h4 class="font-bold text-xs text-[#1D1D1F]">Filter Audit Logs</h4>
                                <a href="{{ route('admin.audit_logs.index') }}" class="text-[11px] text-[#780000] hover:underline font-semibold">Reset</a>
                            </div>

                            <form method="GET" action="{{ route('admin.audit_logs.index') }}" class="space-y-3 text-xs">
                                @if(request('search'))
                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                @endif

                                <div>
                                    <label class="block font-semibold text-[#6E6E73] mb-1">Event Action</label>
                                    <select name="action" class="w-full px-2.5 py-1.5 rounded-lg border border-[#D1D1D6] text-xs font-medium">
                                        <option value="">All Actions</option>
                                        @foreach($actions as $act)
                                            <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>
                                                {{ str_replace('_', ' ', $act) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block font-semibold text-[#6E6E73] mb-1">Date From</label>
                                        <input type="date" 
                                               name="date_from" 
                                               value="{{ request('date_from') }}" 
                                               class="w-full px-2.5 py-1.5 rounded-lg border border-[#D1D1D6] text-xs">
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-[#6E6E73] mb-1">Date To</label>
                                        <input type="date" 
                                               name="date_to" 
                                               value="{{ request('date_to') }}" 
                                               class="w-full px-2.5 py-1.5 rounded-lg border border-[#D1D1D6] text-xs">
                                    </div>
                                </div>

                                <div class="pt-2 border-t border-[#E5E5EA] flex justify-end">
                                    <button type="submit" class="btn-primary w-full py-1.5 text-xs font-bold">
                                        Apply Filters
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
                        <th class="py-3 px-4 text-left">Timestamp</th>
                        <th class="py-3 px-4 text-left">Event Type</th>
                        <th class="py-3 px-4 text-left">Actor</th>
                        <th class="py-3 px-4 text-left">Details / Summary</th>
                        <th class="py-3 px-4 text-left">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E5EA]">
                    @forelse($logs as $log)
                    <tr class="hover:bg-[#FAFAFC] transition-colors">
                        <!-- Timestamp -->
                        <td class="py-3 px-4 text-left text-[#6E6E73] whitespace-nowrap font-mono text-xs">
                            {{ $log->created_at ? $log->created_at->format('M d, Y g:i:s A') : '—' }}
                        </td>

                        <!-- Action Badge -->
                        <td class="py-3 px-4 text-left whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded-md text-xs font-bold border inline-block {{ $log->action_badge['class'] ?? 'bg-gray-100 text-gray-700 border-gray-200' }}">
                                {{ $log->action_badge['label'] ?? $log->action }}
                            </span>
                        </td>

                        <!-- Actor -->
                        <td class="py-3 px-4 text-left whitespace-nowrap">
                            <strong class="text-[#1D1D1F] block">{{ $log->actor_name ?? 'System' }}</strong>
                            @if($log->user)
                                <span class="text-xs text-[#6E6E73]">{{ $log->user->email }} ({{ ucfirst($log->user->role) }})</span>
                            @endif
                        </td>

                        <!-- Description -->
                        <td class="py-3 px-4 text-left text-[#1D1D1F] max-w-md">
                            <p class="leading-relaxed">{{ $log->description }}</p>
                        </td>

                        <!-- IP Address -->
                        <td class="py-3 px-4 text-left font-mono text-xs text-[#6E6E73] whitespace-nowrap">
                            {{ $log->ip_address ?? '127.0.0.1' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-xs text-[#8E8E93]">
                            No audit log events recorded yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $logs->links() }}
    </div>

</div>
@endsection
