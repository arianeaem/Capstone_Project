@extends('layouts.admin')

@section('title', 'System Audit Logs | Camp FreedivePH')

@section('content')
<div class="space-y-6 text-sm">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-5">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight">System Audit Logs</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200">
                    Owner Exclusive
                </span>
            </div>
            <p class="text-xs sm:text-sm text-[#6E6E73] mt-1">
                Review account activity, security logs, and changes made across the system.
            </p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-xl border border-[#E5E5EA]">
        <form method="GET" action="{{ route('admin.audit_logs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">
            <div class="lg:col-span-2">
                <label class="block font-bold text-[#1D1D1F] mb-2">Search Logs</label>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Actor name, description details, IP..." 
                       class="w-full px-3.5 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
            </div>

            <div>
                <label class="block font-bold text-[#1D1D1F] mb-2">Event Action</label>
                <select name="action" class="w-full px-3.5 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white font-medium">
                    <option value="">All Actions</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>
                            {{ $act }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-[#1D1D1F] mb-2">Date From</label>
                <input type="date" 
                       name="date_from" 
                       value="{{ request('date_from') }}" 
                       class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary px-4 py-2 text-xs font-bold w-full">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'action', 'date_from', 'date_to']))
                    <a href="{{ route('admin.audit_logs.index') }}" class="btn-secondary px-3 py-2 text-xs text-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Audit Logs Ledger Table -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] overflow-hidden shadow-2xs">
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
                        <td class="py-3 px-4 text-left text-[#6E6E73] whitespace-nowrap font-mono text-[11px]">
                            {{ $log->created_at ? $log->created_at->format('M d, Y g:i:s A') : '—' }}
                        </td>

                        <!-- Action Badge -->
                        <td class="py-3 px-4 text-left whitespace-nowrap">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border inline-block {{ $log->action_badge['class'] ?? 'bg-gray-100 text-gray-700 border-gray-200' }}">
                                {{ $log->action_badge['label'] ?? $log->action }}
                            </span>
                        </td>

                        <!-- Actor -->
                        <td class="py-3 px-4 text-left whitespace-nowrap">
                            <strong class="text-[#1D1D1F] block">{{ $log->actor_name ?? 'System' }}</strong>
                            @if($log->user)
                                <span class="text-[11px] text-[#6E6E73]">{{ $log->user->email }} ({{ ucfirst($log->user->role) }})</span>
                            @endif
                        </td>

                        <!-- Description -->
                        <td class="py-3 px-4 text-left text-[#1D1D1F] max-w-md">
                            <p class="leading-relaxed">{{ $log->description }}</p>
                        </td>

                        <!-- IP Address -->
                        <td class="py-3 px-4 text-left font-mono text-[11px] text-[#6E6E73] whitespace-nowrap">
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

        @if($logs->hasPages())
        <div class="p-4 border-t border-[#E5E5EA] bg-[#FAFAFC]">
            {{ $logs->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
