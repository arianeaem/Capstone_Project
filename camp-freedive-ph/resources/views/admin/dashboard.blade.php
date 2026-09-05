@extends('layouts.admin')

@section('title', ($isOwner && $activeView === 'executive' ? 'Executive Analytics' : 'Operations Dashboard') . ' | Camp FreedivePH')

@section('content')
<div class="space-y-6">

    <!-- Top Command Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 py-1">
        <div class="space-y-1">
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-[#1D1D1F]">
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#63a5c4] via-[#164B60] to-[#2e80a3]">Welcome back, {{ $user->name }}</span>
            </h1>
            <p class="text-xs sm:text-sm text-[#6E6E73]">
                <span>It's {{ now('Asia/Manila')->format('l, F d, Y') }}</span>
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            @if($isOwner)
                <!-- Owner View Switcher -->
                <div class="inline-flex p-1 rounded-lg bg-white border border-[#E5E5EA] text-xs font-bold shadow-2xs">
                    <a href="{{ route('admin.dashboard') }}" 
                       class="px-3 py-1.5 rounded-md transition-all {{ $activeView === 'executive' ? 'bg-[#780000] text-white shadow-2xs' : 'text-[#6E6E73] hover:text-[#1D1D1F]' }}">
                        Executive Analytics
                    </a>
                    <a href="{{ route('admin.dashboard', ['view' => 'operations']) }}" 
                       class="px-3 py-1.5 rounded-md transition-all {{ $activeView === 'operations' ? 'bg-[#780000] text-white shadow-2xs' : 'text-[#6E6E73] hover:text-[#1D1D1F]' }}">
                        Operations View
                    </a>
                </div>
            @endif

            <a href="{{ route('admin.bookings.create') }}" class="px-4 py-2 rounded-lg text-xs font-extrabold bg-[#00c3d0] hover:bg-[#00abb7] text-[#1D1D1F] shadow-sm transition-all hover:scale-[1.02] flex items-center gap-1.5">
                <span>Walk-in Booking</span>
            </a>
        </div>
    </div>

    @if($activeView === 'operations')
        <!-- ========================================================================= -->
        <!-- VIEW 1: OPERATIONS COMMAND CENTER (ADMIN & OWNER OPS) -->
        <!-- ========================================================================= -->

        <!-- 1. Operational KPI Metrics (Single Box with Vertical Line Dividers) -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 shadow-2xs">
            <div class="grid grid-cols-2 lg:grid-cols-4 items-center gap-y-3">
                
                <!-- Active Divers -->
                <div class="px-4 py-1">
                    <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Active Divers (This Month)</span>
                    <div class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] mt-0.5">{{ $operationalStats['active_divers_month'] }}</div>
                </div>

                <!-- Average Trip Occupancy -->
                <div class="relative px-4 py-1">
                    <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                    <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Average Trip Occupancy</span>
                    <div class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] mt-0.5">{{ $operationalStats['avg_occupancy'] }}%</div>
                </div>

                <!-- Active Instructors -->
                <div class="relative px-4 py-1">
                    <div class="hidden lg:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                    <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Active Instructors</span>
                    <div class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] mt-0.5">{{ $operationalStats['active_coaches_count'] }}</div>
                </div>

                <!-- Unmatched Students -->
                <div class="relative px-4 py-1">
                    <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                    <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Unmatched Students</span>
                    <div class="text-2xl sm:text-3xl font-extrabold mt-0.5 {{ $operationalStats['unmatched_students_count'] > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                        {{ $operationalStats['unmatched_students_count'] }}
                    </div>
                </div>

            </div>
        </div>

        <!-- 2. Combined Row: Action Sub-Cards (Left) & Upcoming Weekend Batches 4 Cards (Right) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
            
            @if($actionInbox['total_count'] > 0)
                <!-- Left Column: Action Required Sub-Cards (Borderless, Using Tint Backgrounds, Covering Full Height) -->
                <div class="lg:col-span-4 flex flex-col h-full space-y-3">
                    <div class="flex items-center justify-between pb-0.5">
                        <h2 class="text-base font-bold text-[#1D1D1F]">Action Items</h2>
                        <span class="text-xs font-bold text-[#780000]">{{ $actionInbox['total_count'] }} alert(s)</span>
                    </div>

                    <div class="flex-1 flex flex-col justify-between gap-3">
                        <!-- Reschedules -->
                        @if($actionInbox['reschedules']->count() > 0)
                            <div class="flex-1 min-h-[85px] p-4 sm:p-5 rounded-xl bg-[#FEF3C7] flex items-center justify-between gap-3 group shadow-2xs transition-all">
                                <div class="space-y-1">
                                    <span class="text-sm font-extrabold text-amber-950 block">Pending Reschedules</span>
                                    <p class="text-xs text-amber-800 font-medium">{{ $actionInbox['reschedules']->count() }} guest request(s)</p>
                                </div>
                                <a href="{{ route('admin.bookings.requests') }}" class="p-2 rounded-lg text-amber-950 hover:bg-amber-200/60 transition-colors shrink-0" title="Review Reschedule Requests">
                                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg>
                                </a>
                            </div>
                        @endif

                        <!-- Cancellations -->
                        @if($actionInbox['cancellations']->count() > 0)
                            <div class="flex-1 min-h-[85px] p-4 sm:p-5 rounded-xl bg-[#FEE2E2] flex items-center justify-between gap-3 group shadow-2xs transition-all">
                                <div class="space-y-1">
                                    <span class="text-sm font-extrabold text-rose-950 block">Cancellation Requests</span>
                                    <p class="text-xs text-rose-800 font-medium">{{ $actionInbox['cancellations']->count() }} pending cancellation(s)</p>
                                </div>
                                <a href="{{ route('admin.bookings.requests') }}" class="p-2 rounded-lg text-rose-950 hover:bg-rose-200/60 transition-colors shrink-0" title="Review Cancellation Requests">
                                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg>
                                </a>
                            </div>
                        @endif

                        <!-- Coach Releases -->
                        @if($actionInbox['releases']->count() > 0)
                            <div class="flex-1 min-h-[85px] p-4 sm:p-5 rounded-xl bg-[#DBEAFE] flex items-center justify-between gap-3 group shadow-2xs transition-all">
                                <div class="space-y-1">
                                    <span class="text-sm font-extrabold text-blue-950 block">Coach Release Requests</span>
                                    <p class="text-xs text-blue-800 font-medium">{{ $actionInbox['releases']->count() }} instructor release(s)</p>
                                </div>
                                <a href="{{ route('admin.coaches.requests') }}" class="p-2 rounded-lg text-blue-950 hover:bg-blue-200/60 transition-colors shrink-0" title="Review Coach Release Requests">
                                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg>
                                </a>
                            </div>
                        @endif

                        <!-- Pending Refunds -->
                        @if($actionInbox['refunds']->count() > 0)
                            <div class="flex-1 min-h-[85px] p-4 sm:p-5 rounded-xl bg-[#F3E8FF] flex items-center justify-between gap-3 group shadow-2xs transition-all">
                                <div class="space-y-1">
                                    <span class="text-sm font-extrabold text-purple-950 block">Pending Refunds</span>
                                    <p class="text-xs text-purple-800 font-medium">{{ $actionInbox['refunds']->count() }} refund(s) to process</p>
                                </div>
                                <a href="{{ route('admin.payments.refunds') }}" class="p-2 rounded-lg text-purple-950 hover:bg-purple-200/60 transition-colors shrink-0" title="Process Pending Refunds">
                                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg>
                                </a>
                            </div>
                        @endif

                        <!-- Understaffed Batches -->
                        @if($actionInbox['understaffed']->count() > 0)
                            <div class="flex-1 min-h-[85px] p-4 sm:p-5 rounded-xl bg-[#FEF3C7] flex items-center justify-between gap-3 group shadow-2xs transition-all">
                                <div class="space-y-1">
                                    <span class="text-sm font-extrabold text-amber-950 block">Under-staffed Batches</span>
                                    <p class="text-xs text-amber-800 font-medium">{{ $actionInbox['understaffed']->count() }} batch(es) need instructors</p>
                                </div>
                                <a href="{{ route('admin.coaches.matching') }}" class="p-2 rounded-lg text-amber-950 hover:bg-amber-200/60 transition-colors shrink-0" title="Match Coaches">
                                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg>
                                </a>
                            </div>
                        @endif

                        <!-- Marine Weather Warnings -->
                        @if($actionInbox['weather_alerts']->count() > 0)
                            <div class="flex-1 min-h-[85px] p-4 sm:p-5 rounded-xl bg-[#FEE2E2] flex items-center justify-between gap-3 group shadow-2xs transition-all">
                                <div class="space-y-1">
                                    <span class="text-sm font-extrabold text-red-950 block">Marine Weather Warning</span>
                                    <p class="text-xs text-red-800 font-medium">{{ $actionInbox['weather_alerts']->count() }} batch(es) under advisory</p>
                                </div>
                                <a href="{{ route('admin.weather.index') }}" class="p-2 rounded-lg text-red-950 hover:bg-red-200/60 transition-colors shrink-0" title="Inspect Weather Advisory">
                                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Right Column: Upcoming Weekend Batches (4 cards in 2x2 grid) -->
                <div class="lg:col-span-8 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-[#1D1D1F]">Upcoming Weekend Batches</h2>
                        </div>
                        <a href="{{ route('admin.batches.index') }}" class="text-xs font-bold text-[#780000] hover:underline inline-flex items-center gap-0.5">
                            <span>View All Batches</span>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        @forelse($upcomingBatches as $batch)
                            @php
                                $pax = $batch->total_participants_count;
                                $coachesNeeded = max(1, (int) ceil($pax / 4));
                                $coachesAssigned = $batch->assigned_coaches_count;
                            @endphp
                            <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-5 shadow-2xs hover:border-[#D1D1D6] transition-all space-y-3 flex flex-col justify-between">
                                <div class="space-y-2.5">
                                    <div class="flex items-center justify-between gap-2">
                                        <h3 class="font-extrabold text-[#1D1D1F] text-base">{{ $batch->batch_number }}</h3>
                                        <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold border {{ $batch->risk_badge['class'] }}">
                                            {{ $batch->risk_badge['label'] }}
                                        </span>
                                    </div>

                                    <div>
                                        <p class="text-xs text-[#6E6E73]">
                                            {{ $batch->start_date->format('M d, Y') }} — {{ $batch->end_date->format('M d, Y') }}
                                            <span class="text-[#780000] font-semibold">({{ $batch->start_date->diffForHumans() }})</span>
                                        </p>
                                    </div>

                                    <!-- Participants Count -->
                                    <div class="flex items-center justify-between pt-1 border-t border-[#F2F2F7] text-xs">
                                        <span class="text-[#6E6E73] font-medium">Participants:</span>
                                        <span class="font-bold text-[#1D1D1F]">{{ $pax }} {{ Str::plural('participant', $pax) }}</span>
                                    </div>

                                    <!-- Staffing Status Pill -->
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-[#6E6E73] font-medium">Coaches:</span>
                                        @if($coachesAssigned >= $coachesNeeded)
                                            <span class="text-emerald-700 font-bold flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                {{ $coachesAssigned }}/{{ $coachesNeeded }} Staffed
                                            </span>
                                        @else
                                            <span class="text-amber-700 font-bold flex items-center gap-1">
                                                {{ $coachesAssigned }}/{{ $coachesNeeded }} (Needs {{ $coachesNeeded - $coachesAssigned }})
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-2 py-8 text-center bg-white rounded-xl border border-[#E5E5EA] text-xs text-[#6E6E73]">
                                No upcoming confirmed batches found. <a href="{{ route('admin.batches.create') }}" class="text-[#780000] font-bold hover:underline inline-flex items-center gap-0.5"><span>Create a new batch</span> <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg></a>
                            </div>
                        @endforelse
                    </div>
                </div>
            @else
                <!-- When no action alerts, Upcoming Weekend Batches spans 4 columns -->
                <div class="lg:col-span-12 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-[#1D1D1F]">Upcoming Weekend Batches</h2>
                        </div>
                        <a href="{{ route('admin.batches.index') }}" class="text-xs font-bold text-[#780000] hover:underline inline-flex items-center gap-0.5">
                            <span>View All Batches</span>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        @forelse($upcomingBatches as $batch)
                            @php
                                $pax = $batch->total_participants_count;
                                $coachesNeeded = max(1, (int) ceil($pax / 4));
                                $coachesAssigned = $batch->assigned_coaches_count;
                            @endphp
                            <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-5 shadow-2xs hover:border-[#D1D1D6] transition-all space-y-3 flex flex-col justify-between">
                                <div class="space-y-2.5">
                                    <div class="flex items-center justify-between gap-2">
                                        <h3 class="font-extrabold text-[#1D1D1F] text-base">{{ $batch->batch_number }}</h3>
                                        <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold border {{ $batch->risk_badge['class'] }}">
                                            {{ $batch->risk_badge['label'] }}
                                        </span>
                                    </div>

                                    <div>
                                        <p class="text-xs text-[#6E6E73]">
                                            {{ $batch->start_date->format('M d, Y') }} — {{ $batch->end_date->format('M d, Y') }}
                                            <span class="text-[#780000] font-semibold">({{ $batch->start_date->diffForHumans() }})</span>
                                        </p>
                                    </div>

                                    <!-- Participants Count -->
                                    <div class="flex items-center justify-between pt-1 border-t border-[#F2F2F7] text-xs">
                                        <span class="text-[#6E6E73] font-medium">Participants:</span>
                                        <span class="font-bold text-[#1D1D1F]">{{ $pax }} {{ Str::plural('participant', $pax) }}</span>
                                    </div>

                                    <!-- Staffing Status Pill -->
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-[#6E6E73] font-medium">Coaches:</span>
                                        @if($coachesAssigned >= $coachesNeeded)
                                            <span class="text-emerald-700 font-bold flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                {{ $coachesAssigned }}/{{ $coachesNeeded }} Staffed
                                            </span>
                                        @else
                                            <span class="text-amber-700 font-bold flex items-center gap-1">
                                                {{ $coachesAssigned }}/{{ $coachesNeeded }} (Needs {{ $coachesNeeded - $coachesAssigned }})
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-4 py-8 text-center bg-white rounded-xl border border-[#E5E5EA] text-xs text-[#6E6E73]">
                                No upcoming confirmed batches found. <a href="{{ route('admin.batches.create') }}" class="text-[#780000] font-bold hover:underline inline-flex items-center gap-0.5"><span>Create a new batch</span> <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg></a>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif

        </div>

        <!-- 4. Recent Confirmed Bookings Feed -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] overflow-hidden shadow-2xs">
            <div class="p-4 sm:p-5 border-b border-[#E5E5EA] flex items-center justify-between">
                <div>
                    <h2 class="text-sm sm:text-base font-bold text-[#1D1D1F]">Recent Confirmed Reservations</h2>
                    <p class="text-xs text-[#6E6E73]">Real-time incoming bookings with paid downpayments.</p>
                </div>
                <a href="{{ route('admin.bookings.index') }}" class="text-xs font-bold text-[#780000] hover:underline inline-flex items-center gap-0.5">
                    <span>All Bookings ({{ $operationalStats['total_active_batches'] }} Batches)</span>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-[#FAFAFC] text-[#6E6E73] font-bold uppercase tracking-wider border-b border-[#E5E5EA]">
                            <th class="py-3 px-4">Booking #</th>
                            <th class="py-3 px-4">Lead Guest</th>
                            <th class="py-3 px-4">Class Package</th>
                            <th class="py-3 px-4">Trip Dates</th>
                            <th class="py-3 px-4">Headcount</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E5EA]">
                        @forelse($recentBookings as $b)
                            <tr class="hover:bg-[#FAFAFC] transition-colors">
                                <td class="py-3 px-4 font-mono font-bold text-[#780000]">
                                    <a href="{{ route('admin.bookings.show', $b) }}" class="hover:underline">
                                        {{ $b->booking_number }}
                                    </a>
                                </td>
                                <td class="py-3 px-4 font-semibold text-[#1D1D1F]">
                                    {{ $b->contact_name }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-md font-bold bg-[#FAFAFC] text-[#1D1D1F] border border-[#E5E5EA]">
                                        {{ $b->formatted_class_type }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-[#6E6E73]">
                                    {{ $b->start_date->format('M d') }} - {{ $b->end_date->format('M d, Y') }}
                                </td>
                                <td class="py-3 px-4 font-bold text-[#1D1D1F]">
                                    {{ $b->participants->count() }} pax
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-md font-bold border {{ $b->status_badge['class'] }}">
                                        {{ $b->status_badge['label'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('admin.bookings.show', $b) }}" class="text-[#780000] font-bold hover:underline inline-flex items-center gap-0.5">
                                        <span>Details</span>
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-xs text-[#6E6E73]">
                                    No confirmed reservations yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @else
        <!-- ========================================================================= -->
        <!-- VIEW 2: OWNER EXECUTIVE & FINANCIAL ANALYTICS -->
        <!-- ========================================================================= -->

        <!-- 1. Executive Financial Balance Sheet (Single Box with Vertical Line Dividers) -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 shadow-2xs">
            <div class="grid grid-cols-2 lg:grid-cols-4 items-center gap-y-3">
                
                <!-- Gross Collected Revenue -->
                <div class="px-4 py-1">
                    <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Gross Collected Revenue</span>
                    <div class="text-2xl sm:text-3xl font-extrabold text-[#780000] mt-0.5">₱{{ number_format($financials['gross_revenue'], 2) }}</div>
                </div>

                <!-- Outstanding Balances -->
                <div class="relative px-4 py-1">
                    <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                    <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Outstanding Balances</span>
                    <div class="text-2xl sm:text-3xl font-extrabold text-amber-700 mt-0.5">₱{{ number_format($financials['outstanding_balances'], 2) }}</div>
                </div>

                <!-- Total Refunds Issued -->
                <div class="relative px-4 py-1">
                    <div class="hidden lg:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                    <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Total Refunds Issued</span>
                    <div class="text-2xl sm:text-3xl font-extrabold text-rose-700 mt-0.5">₱{{ number_format($financials['refunds_processed'], 2) }}</div>
                </div>

                <!-- Net Business Yield -->
                <div class="relative px-4 py-1">
                    <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                    <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Net Business Yield</span>
                    <div class="text-2xl sm:text-3xl font-extrabold text-emerald-700 mt-0.5">₱{{ number_format($financials['net_revenue'], 2) }}</div>
                </div>

            </div>
        </div>

        <!-- 2. Class Package Revenue Contribution (Segmented Progress Bar) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="lg:col-span-2 bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-6 shadow-2xs space-y-5">
                <div>
                    <h2 class="text-base font-bold text-[#1D1D1F]">Class Package Revenue Contribution</h2>
                </div>

                <!-- Multi-Segment Stacked Progress Bar (Reference Design) -->
                <div class="space-y-2">
                    <div class="w-full bg-[#E5E5EA] h-6 sm:h-7 rounded-xl overflow-hidden flex shadow-inner">
                        @foreach($packageAnalytics as $pKey => $pData)
                            @if($pData['share_percentage'] > 0)
                                <div class="{{ $pData['bg_color'] }} transition-all flex items-center justify-center text-white text-[10px] sm:text-xs font-bold px-1 truncate"
                                     style="width: {{ $pData['share_percentage'] }}%"
                                     title="{{ $pData['name'] }}: {{ $pData['share_percentage'] }}% ({{ $pData['bookings_count'] }} bookings)">
                                    @if($pData['share_percentage'] >= 10)
                                        <span>{{ $pData['share_percentage'] }}%</span>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                    
                    <!-- Progress Bar 0% and 100% Labels -->
                    <div class="flex justify-between items-center text-[10px] font-bold text-[#8E8E93] px-0.5">
                        <span>0%</span>
                        <span>100%</span>
                    </div>
                </div>

                <!-- 3-Column Stats Sub-Card (Matching reference design) -->
                <div class="p-4 sm:p-5 rounded-xl border border-[#E5E5EA] bg-[#FAFAFC]">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 divide-y sm:divide-y-0 sm:divide-x divide-[#E5E5EA]">
                        @foreach($packageAnalytics as $pKey => $pData)
                            <div class="space-y-2 {{ !$loop->first ? 'pt-3 sm:pt-0 sm:pl-4 lg:pl-6' : '' }}">
                                <!-- Dot Indicator & Package Name -->
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $pData['dot_class'] }} shrink-0"></span>
                                    <span class="font-extrabold text-sm text-[#1D1D1F]">{{ $pData['name'] }}</span>
                                </div>

                                <!-- Large Primary Booking Count & Share Percentage -->
                                <div>
                                    <div class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight">
                                        {{ $pData['bookings_count'] }}
                                    </div>
                                    <div class="text-xs font-bold {{ $pData['text_color'] }} mt-0.5">
                                        {{ $pData['share_percentage'] }}%
                                    </div>
                                </div>

                                <!-- Revenue & Divers Metric -->
                                <div class="text-[11px] text-[#6E6E73]">
                                    <span class="font-bold text-[#1D1D1F]">₱{{ number_format($pData['revenue'], 2) }}</span>
                                    <span class="block text-[10px] text-[#8E8E93] mt-0.5">{{ $pData['pax_count'] }} divers trained</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- 3. Dynamic Pricing Strategy Card -->
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-6 shadow-2xs space-y-4 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                        <div>
                            <h2 class="text-base font-bold text-[#1D1D1F]">Dynamic Pricing Yield</h2>
                        </div>
                        <a href="{{ route('admin.pricing.index') }}" class="text-xs font-bold text-[#780000] hover:underline inline-flex items-center gap-0.5">
                            <span>Rules</span>
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg>
                        </a>
                    </div>

                        <div class="flex justify-between items-center text-xs">
                            <span class="text-[#6E6E73]">Active Pricing Rules:</span>
                            <strong class="text-[#1D1D1F]">{{ $dynamicPricingStats['active_rules'] }} Rules</strong>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-[#6E6E73]">Demand Surge Lift:</span>
                            <strong class="text-emerald-700">+₱{{ number_format($dynamicPricingStats['positive_yield'], 2) }}</strong>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-[#6E6E73]">Incentive Discounts:</span>
                            <strong class="text-rose-700">−₱{{ number_format($dynamicPricingStats['discount_given'], 2) }}</strong>
                        </div>
                        <div class="pt-2 border-t border-[#E5E5EA] flex justify-between items-center text-xs font-bold">
                            <span class="text-[#1D1D1F]">Net Dynamic Yield:</span>
                            <span class="{{ $dynamicPricingStats['net_lift'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ $dynamicPricingStats['net_lift'] >= 0 ? '+' : '' }}₱{{ number_format($dynamicPricingStats['net_lift'], 2) }}
                            </span>
                        </div>
                </div>
            </div>

        </div>

        <!-- 4. Governance & Audit Log Stream -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-6 shadow-2xs space-y-4">
            <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                <div>
                    <h2 class="text-base font-bold text-[#1D1D1F]">Audit Trail & System Activity</h2>
                    <p class="text-xs text-[#6E6E73]">Immutable records of administrative decisions, price changes, and refunds.</p>
                </div>
                <a href="{{ route('admin.audit_logs.index') }}" class="text-xs font-bold text-[#780000] hover:underline inline-flex items-center gap-0.5">
                    <span>Full Audit Log</span>
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M7 17L17 7M17 7H7M17 7V17"/></svg>
                </a>
            </div>

            <div class="divide-y divide-[#E5E5EA]">
                @forelse($recentAuditLogs as $log)
                    <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-[#1D1D1F]">{{ $log->user?->name ?? 'System' }}</span>
                                <span class="px-2 py-0.5 rounded-md font-bold bg-[#FAFAFC] text-[#780000] border border-[#E5E5EA] font-mono text-[10px]">
                                    {{ $log->action }}
                                </span>
                            </div>
                            <p class="text-[#6E6E73]">{{ $log->description ?? $log->details }}</p>
                        </div>
                        <span class="text-[#8E8E93] text-[11px] shrink-0">
                            {{ $log->created_at->diffForHumans() }}
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-[#6E6E73] py-4 text-center">No audit logs recorded yet.</p>
                @endforelse
            </div>
        </div>

    @endif

</div>
@endsection
