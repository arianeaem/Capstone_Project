@extends('layouts.admin')

@section('title', 'Admin Dashboard | Camp FreedivePH')

@section('content')
<div class="space-y-8">
    
    <!-- Top Greeting Banner -->
    <div class="bg-gradient-to-r from-[#780000] via-[#650000] to-[#470000] rounded-2xl p-6 sm:p-8 text-white shadow-md flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden">
        <div class="space-y-1 relative z-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-white/15 backdrop-blur-xs text-[#F8EAEA] uppercase tracking-wider mb-1 border border-white/20">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Camp Operations Management</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">Welcome back, {{ $user->name }}</h1>
            <p class="text-xs sm:text-sm text-[#F8EAEA]/90">
                <span>Role: <strong class="text-white">{{ $user->role_badge['label'] }}</strong></span>
                <span class="mx-2">•</span>
                <span>Anilao, Mabini Base Operations</span>
            </p>
        </div>

        <div class="flex items-center gap-3 flex-wrap relative z-10">
            <a href="{{ route('admin.bookings.index') }}" class="btn-secondary px-4 py-2.5 text-xs font-bold bg-white/10 hover:bg-white/20 text-white border-white/30 backdrop-blur-xs">
                View Bookings
            </a>
            <a href="{{ route('admin.bookings.create') }}" class="btn-primary px-4 py-2.5 text-xs font-bold shadow-sm bg-white text-[#780000] hover:bg-[#F8EAEA] border-none">
                + New Reservation
            </a>
        </div>
    </div>

    <!-- Spotlight: Next Upcoming Dive Batch -->
    @if($nextBatch)
        @php
            $risk = $nextBatch->riskAssessment;
            $riskRating = $risk?->overall_risk_rating ?? $nextBatch->risk_classification ?? 'safe';
            $diverCount = $nextBatch->bookings->sum(fn($b) => $b->participants->count());
            $assignedCoaches = $nextBatch->coachAssignments->pluck('coach')->unique('id');
        @endphp
        <div class="bg-white rounded-2xl border border-[#E5E5EA] p-6 shadow-xs relative overflow-hidden">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-bold px-3 py-1 rounded-full bg-[#780000]/10 text-[#780000] border border-[#780000]/20 font-mono">
                            {{ $nextBatch->batch_number }}
                        </span>
                        <span class="text-xs font-bold px-3 py-1 rounded-full bg-[#008E98]/10 text-[#008E98] border border-[#008E98]/20">
                            Next Upcoming Batch
                        </span>

                        @if($nextBatch->is_coach_pending)
                            <span class="text-xs font-bold px-3 py-1 rounded-full bg-amber-50 text-amber-800 border border-amber-300">
                                Instructor Pending
                            </span>
                        @endif
                    </div>

                    <h2 class="text-xl font-black text-[#1D1D1F]">{{ $nextBatch->name }}</h2>

                    <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-[#6E6E73] pt-1">
                        <div>
                            <strong>Departure Date:</strong> {{ $nextBatch->start_date->format('M d, Y') }} ({{ $nextBatch->start_date->diffForHumans() }})
                        </div>
                        <div>
                            <strong>Registered Divers:</strong> {{ $diverCount }} pax ({{ $nextBatch->bookings->count() }} bookings)
                        </div>
                        <div>
                            <strong>Assigned Coaches:</strong> {{ $assignedCoaches->count() }} coach(es)
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 shrink-0 flex-wrap">
                    <a href="{{ route('admin.weather.show', $nextBatch) }}" 
                       class="px-4 py-2.5 rounded-xl border border-[#E5E5EA] hover:bg-[#F2F2F7] text-xs font-bold text-[#1D1D1F] transition-all flex items-center gap-2">
                        <span>Weather Safety</span>
                    </a>
                    <a href="{{ route('admin.batches.show', $nextBatch) }}" 
                       class="px-5 py-2.5 rounded-xl bg-[#780000] hover:bg-[#5E0000] text-white text-xs font-bold shadow-xs transition-all flex items-center gap-2">
                        <span>Batch Details</span>
                        <span>→</span>
                    </a>
                </div>

            </div>
        </div>
    @endif

    <!-- Operational KPI Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Total Reservations -->
        <div class="bg-white rounded-2xl border border-[#E5E5EA] p-5 shadow-xs space-y-2">
            <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Total Reservations</span>
            <div class="text-3xl font-black text-[#1D1D1F]">{{ $stats['total_bookings'] }}</div>
            <div class="text-xs text-emerald-600 font-semibold flex items-center gap-1.5 pt-1">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>{{ $stats['confirmed_bookings'] }} Confirmed Active</span>
            </div>
        </div>

        <!-- Pending Staff Actions -->
        <div class="bg-white rounded-2xl border border-[#E5E5EA] p-5 shadow-xs space-y-2">
            <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Pending Actions</span>
            <div class="text-3xl font-black text-amber-600">
                {{ $stats['pending_reschedules'] + $stats['pending_cancellations'] + $stats['pending_refunds'] }}
            </div>
            <div class="text-xs text-[#6E6E73] pt-1">
                {{ $stats['pending_reschedules'] }} Resched • {{ $stats['pending_cancellations'] }} Cancel • {{ $stats['pending_refunds'] }} Refund
            </div>
        </div>

        <!-- Verified Revenue -->
        <div class="bg-white rounded-2xl border border-[#E5E5EA] p-5 shadow-xs space-y-2">
            <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Verified Gross Revenue</span>
            <div class="text-3xl font-black text-[#780000]">
                ₱{{ number_format($stats['total_revenue'], 2) }}
            </div>
            <div class="text-xs text-[#6E6E73] pt-1">
                Collected via GCash / Maya / Card
            </div>
        </div>

        <!-- Active Coaches & Queue -->
        <div class="bg-white rounded-2xl border border-[#E5E5EA] p-5 shadow-xs space-y-2">
            <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Coach Staffing Pool</span>
            <div class="text-3xl font-black text-[#008E98]">
                {{ $stats['active_coaches'] }}
            </div>
            <div class="text-xs text-[#6E6E73] pt-1">
                {{ $stats['unmatched_students'] }} student(s) in matching queue
            </div>
        </div>

    </div>

    <!-- Operations Command Center Quick Launch Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <!-- Module 1: Batches & 2D1N Schedules -->
        <div class="bg-white rounded-2xl border border-[#E5E5EA] p-6 shadow-xs hover:border-[#780000] hover:shadow-md transition-all flex flex-col justify-between space-y-4">
            <div class="space-y-2">
                <div class="w-10 h-10 rounded-xl bg-[#780000]/10 text-[#780000] flex items-center justify-center font-bold text-lg">
                    📅
                </div>
                <h3 class="text-base font-extrabold text-[#1D1D1F]">Batches & 2D1N Schedules</h3>
                <p class="text-xs text-[#6E6E73] leading-relaxed">
                    Organize weekend dive batches, manage auto-linked reservations, and trigger force majeure cascading actions.
                </p>
            </div>
            <div class="pt-3 border-t border-[#E5E5EA]">
                <a href="{{ route('admin.batches.index') }}" class="text-xs font-bold text-[#780000] hover:underline flex items-center justify-between">
                    <span>Manage Batches</span>
                    <span>→</span>
                </a>
            </div>
        </div>

        <!-- Module 2: Coach Matching Queue -->
        <div class="bg-white rounded-2xl border border-[#E5E5EA] p-6 shadow-xs hover:border-[#008E98] hover:shadow-md transition-all flex flex-col justify-between space-y-4">
            <div class="space-y-2">
                <div class="w-10 h-10 rounded-xl bg-[#008E98]/10 text-[#008E98] flex items-center justify-center font-bold text-lg">
                    👥
                </div>
                <h3 class="text-base font-extrabold text-[#1D1D1F]">Coach Roster & Matching</h3>
                <p class="text-xs text-[#6E6E73] leading-relaxed">
                    Assign coaches with 4:1 safety ratios, review emergency release requests, and broadcast unstaffed slots.
                </p>
            </div>
            <div class="pt-3 border-t border-[#E5E5EA]">
                <a href="{{ route('admin.coaches.matching') }}" class="text-xs font-bold text-[#008E98] hover:underline flex items-center justify-between">
                    <span>Open Matching Queue</span>
                    <span>→</span>
                </a>
            </div>
        </div>

        <!-- Module 3: Marine Weather Safety -->
        <div class="bg-white rounded-2xl border border-[#E5E5EA] p-6 shadow-xs hover:border-emerald-600 hover:shadow-md transition-all flex flex-col justify-between space-y-4">
            <div class="space-y-2">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-lg">
                    🌊
                </div>
                <h3 class="text-base font-extrabold text-[#1D1D1F]">Weather & Safety Monitoring</h3>
                <p class="text-xs text-[#6E6E73] leading-relaxed">
                    Real-time marine telemetric models, multi-parameter risk assessments, hard-gate ceilings, and PAGASA gale overrides.
                </p>
            </div>
            <div class="pt-3 border-t border-[#E5E5EA]">
                <a href="{{ route('admin.weather.index') }}" class="text-xs font-bold text-emerald-700 hover:underline flex items-center justify-between">
                    <span>Safety Monitoring</span>
                    <span>→</span>
                </a>
            </div>
        </div>

    </div>

    <!-- Recent Reservations & Audit Trail -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        
        <!-- Recent Reservations Table -->
        <div class="bg-white rounded-2xl border border-[#E5E5EA] p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                <div>
                    <h3 class="text-base font-extrabold text-[#1D1D1F]">Recent Reservations</h3>
                    <span class="text-xs text-[#8E8E93]">Latest customer booking activity</span>
                </div>
                <a href="{{ route('admin.bookings.index') }}" class="text-xs font-bold text-[#780000] hover:underline">
                    View All →
                </a>
            </div>

            <div class="divide-y divide-[#E5E5EA]">
                @forelse($recentBookings as $b)
                <div class="py-3.5 flex items-center justify-between gap-3 text-xs">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.bookings.show', $b) }}" class="font-mono font-bold text-[#780000] hover:underline text-xs sm:text-sm">
                                {{ $b->booking_number }}
                            </a>
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold border {{ $b->status_badge['bg'] }}">
                                {{ $b->status_badge['label'] }}
                            </span>
                        </div>
                        <div class="text-xs text-[#6E6E73]">
                            <span class="font-bold text-[#1D1D1F]">{{ $b->contact_name }}</span>
                            <span>•</span>
                            <span>{{ $b->formatted_class_type }}</span>
                            <span>•</span>
                            <span>{{ $b->start_date->format('M d, Y') }}</span>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="font-black text-xs sm:text-sm text-[#1D1D1F] block">₱{{ number_format($b->total_amount, 2) }}</span>
                        <span class="text-[11px] text-[#8E8E93]">Paid: ₱{{ number_format($b->downpayment_amount, 2) }}</span>
                    </div>
                </div>
                @empty
                <p class="text-xs text-[#6E6E73] py-6 text-center">No bookings recorded yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Audit Trail (Owner & Admin) -->
        <div class="bg-white rounded-2xl border border-[#E5E5EA] p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                <div>
                    <h3 class="text-base font-extrabold text-[#1D1D1F]">Security & Operational Audit Log</h3>
                    <span class="text-xs text-[#8E8E93]">Immutable system event trail</span>
                </div>
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
                        <span class="px-2 py-0.5 rounded text-[11px] font-bold border {{ $log->action_badge['class'] }}">
                            {{ $log->action_badge['label'] }}
                        </span>
                        <span class="text-[#8E8E93] text-[11px]">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-[#1D1D1F] leading-snug text-xs font-medium">{{ $log->description }}</p>
                    <div class="text-[11px] text-[#6E6E73] flex items-center justify-between pt-0.5">
                        <span>Actor: <strong class="text-[#1D1D1F]">{{ $log->actor_name }}</strong></span>
                        <span class="text-[#8E8E93]">IP: {{ $log->ip_address ?: '127.0.0.1' }}</span>
                    </div>
                </div>
                @empty
                <p class="text-xs text-[#6E6E73] py-6 text-center">No security logs recorded yet.</p>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
