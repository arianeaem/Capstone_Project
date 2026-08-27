@extends('layouts.admin')

@section('title', 'Admin Dashboard | Camp FreedivePH')

@section('content')
<div class="space-y-8">
    
    <!-- Top Greeting Banner -->
    <div class="bg-gradient-to-r from-[#780000] to-[#5E0000] rounded-xl p-6 sm:p-8 text-white shadow-md flex flex-col sm:flex-row sm:items-center justify-between gap-6">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-white/10 border border-white/20 text-[#F8EAEA] uppercase tracking-wider mb-2">
                <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                <span>Camp Operational Overview</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Welcome back, {{ $user->name }}</h1>
            <div class="text-sm text-[#F8EAEA]/90 mt-1">
                <span>Role: {{ $user->role_badge['label'] }}</span>
                <span class="block text-xs text-[#F8EAEA]/75">Anilao, Mabini Base Camp</span>
            </div>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            <a href="{{ route('admin.bookings.index') }}" class="btn-secondary px-4 py-2.5 text-xs sm:text-sm font-semibold">
                View All Bookings
            </a>
            <a href="{{ route('admin.bookings.create') }}" class="btn-primary px-4 py-2.5 text-xs sm:text-sm font-bold shadow-sm bg-white text-[#780000] hover:bg-[#F8EAEA] border-none">
                Add Booking
            </a>
        </div>
    </div>

    <!-- Quick Stats Grid (Single Box with Vertical Dividers with Top/Bottom Margin) -->
    <div class="bg-white rounded-2xl border border-[#E5E5EA] p-4 sm:p-5">
        <div class="grid grid-cols-2 lg:grid-cols-4 items-center gap-y-4">
            <!-- Total Reservations -->
            <div class="px-4 sm:px-6 py-1">
                <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Total Reservations</span>
                <div class="text-2xl font-extrabold text-[#1D1D1F] mt-1">{{ $stats['total_bookings'] }}</div>
                <span class="text-xs text-[#34C759] font-semibold block mt-1 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                    {{ $stats['confirmed_bookings'] }} Confirmed
                </span>
            </div>

            <!-- Pending Staff Actions -->
            <div class="relative px-4 sm:px-6 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Pending Actions</span>
                <div class="text-2xl font-extrabold text-[#FF8D28] mt-1">
                    {{ $stats['pending_reschedules'] + $stats['pending_cancellations'] }}
                </div>
                <span class="text-xs text-[#6E6E73] block mt-1">
                    {{ $stats['pending_reschedules'] }} Resched / {{ $stats['pending_cancellations'] }} Cancel
                </span>
            </div>

            <!-- Verified Revenue -->
            <div class="relative px-4 sm:px-6 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Verified Revenue</span>
                <div class="text-2xl font-extrabold text-[#780000] mt-1">
                    ₱{{ number_format($stats['total_revenue'], 2) }}
                </div>
                <span class="text-xs text-[#6E6E73] block mt-1">PayMongo Gateway Sync</span>
            </div>

            <!-- Active Coaches -->
            <div class="relative px-4 sm:px-6 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Active Coaches</span>
                <div class="text-2xl font-extrabold text-[#008E98] mt-1">
                    {{ $stats['active_coaches'] }}
                </div>
                <span class="text-xs text-[#6E6E73] block mt-1">Total Staff: {{ $stats['total_users'] }}</span>
            </div>
        </div>
    </div>

    <!-- PayMongo Feature Cards Quick Launch -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Card 1: Bookings Management -->
        <div class="feature-card">
            <div class="icon-tile">
                <svg viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            </div>
            <h4 class="text-base font-bold text-[#1D1D1F]">Manage Reservations</h4>
            <p class="text-xs text-[#6E6E73] leading-relaxed">View confirmed diver rosters, modify schedules, or record manual walk-in reservations.</p>
            <div class="mt-4 pt-3 border-t border-[#E5E5EA]">
                <a href="{{ route('admin.bookings.index') }}" class="text-xs font-bold text-[#780000] hover:underline flex items-center gap-1">
                    Open Bookings List →
                </a>
            </div>
        </div>

        <!-- Card 2: Payments & Refunds -->
        <div class="feature-card">
            <div class="icon-tile">
                <svg viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
            </div>
            <h4 class="text-base font-bold text-[#1D1D1F]">Payments & Refunds</h4>
            <p class="text-xs text-[#6E6E73] leading-relaxed">Track PayMongo transactions, execute policy refunds, and collect balance settlements.</p>
            <div class="mt-4 pt-3 border-t border-[#E5E5EA]">
                <a href="{{ route('admin.payments.index') }}" class="text-xs font-bold text-[#780000] hover:underline flex items-center gap-1">
                    Open Payments Ledger →
                </a>
            </div>
        </div>

        <!-- Card 3: User Provisioning -->
        <div class="feature-card">
            <div class="icon-tile">
                <svg viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </div>
            <h4 class="text-base font-bold text-[#1D1D1F]">Staff & Coaches</h4>
            <p class="text-xs text-[#6E6E73] leading-relaxed">Provision staff accounts, manage roles, and review immutable security audit logs.</p>
            <div class="mt-4 pt-3 border-t border-[#E5E5EA]">
                <a href="{{ route('admin.users.index') }}" class="text-xs font-bold text-[#780000] hover:underline flex items-center gap-1">
                    Manage Accounts →
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Bookings & Audit Trail -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Recent Bookings Table -->
        <div class="table-card p-6">
            <div class="flex items-center justify-between mb-4 border-b border-[#E5E5EA] pb-3">
                <h3 class="text-base font-bold text-[#1D1D1F]">Recent Reservations</h3>
                <a href="{{ route('admin.bookings.index') }}" class="text-xs font-bold text-[#780000] hover:underline">
                    View All →
                </a>
            </div>

            <div class="divide-y divide-[#E5E5EA]">
                @forelse($recentBookings as $b)
                <div class="py-3 flex items-center justify-between gap-3 text-xs sm:text-sm">
                    <div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.bookings.show', $b) }}" class="font-mono font-bold text-[#780000] hover:underline">
                                {{ $b->booking_number }}
                            </a>
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold border {{ $b->status_badge['bg'] }}">
                                {{ $b->status_badge['label'] }}
                            </span>
                        </div>
                        <div class="text-xs text-[#6E6E73] mt-0.5 space-y-0.5">
                            <div class="font-medium text-[#1D1D1F]">{{ $b->contact_name }}</div>
                            <div>{{ $b->formatted_class_type }} ({{ $b->start_date->format('M d, Y') }})</div>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="font-bold text-xs text-[#1D1D1F] block">₱{{ number_format($b->downpayment_amount, 2) }}</span>
                        <span class="text-xs text-[#34C759] font-semibold">Downpayment</span>
                    </div>
                </div>
                @empty
                <p class="text-xs text-[#6E6E73] py-4 text-center">No bookings recorded yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Audit Trail (Owner & Admin) -->
        <div class="table-card p-6">
            <div class="flex items-center justify-between mb-4 border-b border-[#E5E5EA] pb-3">
                <h3 class="text-base font-bold text-[#1D1D1F]">Recent Security & Staff Activity</h3>
                @if($user->isOwner())
                    <a href="{{ route('admin.audit_logs.index') }}" class="text-xs font-bold text-[#780000] hover:underline">
                        View All Logs →
                    </a>
                @endif
            </div>

            <div class="divide-y divide-[#E5E5EA]">
                @forelse($recentAuditLogs as $log)
                <div class="py-3 text-xs space-y-1">
                    <div class="flex items-center justify-between gap-2">
                        <span class="px-2 py-0.5 rounded text-xs font-bold border {{ $log->action_badge['class'] }}">
                            {{ $log->action_badge['label'] }}
                        </span>
                        <span class="text-[#8E8E93]">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-[#1D1D1F] leading-snug">{{ $log->description }}</p>
                    <div class="text-xs text-[#6E6E73] space-y-0.5 pt-0.5">
                        <div>Actor: <strong>{{ $log->actor_name }}</strong></div>
                        <div class="text-[#8E8E93]">IP: {{ $log->ip_address ?: '127.0.0.1' }}</div>
                    </div>
                </div>
                @empty
                <p class="text-xs text-[#6E6E73] py-4 text-center">No security logs recorded yet.</p>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
