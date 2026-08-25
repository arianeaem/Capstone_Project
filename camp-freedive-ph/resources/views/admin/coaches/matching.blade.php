@extends('layouts.admin')

@section('title', 'Students Needing a Coach | Camp FreedivePH')

@section('content')
<div class="space-y-6 text-sm">
    
    <!-- Top Header & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight">Students Needing a Coach</h1>
            <p class="text-xs sm:text-sm text-[#6E6E73] mt-1">
                Assign certified coaches to upcoming weekend batches and student groups.
            </p>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            <a href="{{ route('admin.coaches.requests') }}" class="btn-secondary px-4 py-2.5 text-xs sm:text-sm font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 text-[#FF3B3C]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                <span>Coach Requests</span>
                @if(isset($pendingRequestsCount) && $pendingRequestsCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-[#FF3B3C] text-white">
                        {{ $pendingRequestsCount }}
                    </span>
                @endif
            </a>

            <a href="{{ route('admin.coaches.index') }}" class="btn-secondary px-4 py-2.5 text-xs sm:text-sm font-semibold flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                <span>Coach Roster</span>
            </a>
        </div>
    </div>

    <!-- Batch Groups Queue -->
    @if(isset($batchGroups) && count($batchGroups) > 0)
        <div class="space-y-6">
            @foreach($batchGroups as $group)
            @php
                $batch = $group['batch'];
                $students = $group['unassigned_students'];
                $availableCoaches = $group['available_coaches'];
            @endphp
            <div class="bg-white rounded-2xl border border-[#E5E5EA] p-6 space-y-5 shadow-2xs">
                <!-- Group Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-4">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="font-extrabold text-base sm:text-lg text-[#1D1D1F]">
                                {{ $batch->name }} ({{ $batch->batch_number }})
                            </h2>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                {{ $group['unassigned_count'] }} Student(s) Unassigned
                            </span>
                        </div>
                        <p class="text-xs text-[#6E6E73] mt-1">
                            📅 {{ $batch->start_date->format('M d') }} – {{ $batch->end_date->format('M d, Y') }} • 
                            @foreach($group['class_counts'] as $class => $count)
                                <span class="font-semibold text-[#1D1D1F]">{{ ucfirst($class) }}: {{ $count }}pax</span>{{ !$loop->last ? ' | ' : '' }}
                            @endforeach
                        </p>
                    </div>

                    <!-- Broadcast Opening to Portal if no coaches -->
                    <div>
                        @if($group['open_broadcast'])
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200 inline-flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-600 animate-pulse"></span>
                                Slot Broadcast Open on Coach Portal
                            </span>
                        @else
                            <form action="{{ route('admin.coaches.matching.broadcast') }}" method="POST" class="inline-block">
                                @csrf
                                <input type="hidden" name="batch_id" value="{{ $batch->id }}">
                                <button type="submit" 
                                        onclick="return confirm('Broadcast an open coaching slot for this batch to all available coaches in the portal?')"
                                        class="px-3.5 py-2 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 text-xs font-bold transition-colors flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 11 18-5v12L3 14v-3z"></path><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"></path></svg>
                                    <span>Broadcast Slot Opening</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Assignment Form -->
                <form action="{{ route('admin.coaches.matching.assign') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="batch_id" value="{{ $batch->id }}">

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Students Selection List -->
                        <div class="lg:col-span-2 space-y-3">
                            <label class="block font-bold text-[#1D1D1F] text-xs">
                                1. Select Students to Assign
                            </label>
                            <div class="border border-[#E5E5EA] rounded-xl divide-y divide-[#E5E5EA] max-h-72 overflow-y-auto bg-[#FAFAFC]">
                                @foreach($students as $student)
                                <label class="flex items-center justify-between p-3 hover:bg-white cursor-pointer transition-colors text-xs">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" 
                                               name="participant_ids[]" 
                                               value="{{ $student->id }}" 
                                               checked
                                               class="w-4 h-4 rounded text-[#780000] focus:ring-[#780000]">
                                        <div>
                                            <strong class="text-[#1D1D1F] font-bold block">{{ $student->name }}</strong>
                                            <span class="text-[#6E6E73] text-[11px]">
                                                Booking #{{ $student->booking->booking_number }} • Age: {{ $student->age }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-semibold text-[11px] border border-blue-200">
                                            {{ ucfirst($student->booking->class_type ?? 'Discovery') }}
                                        </span>
                                        <span class="text-[10px] text-[#6E6E73] block mt-0.5">{{ ucfirst(str_replace('_', ' ', $student->swimmer_status)) }}</span>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Coach Selection & Submit -->
                        <div class="space-y-3 flex flex-col justify-between">
                            <div class="space-y-3">
                                <label class="block font-bold text-[#1D1D1F] text-xs">
                                    2. Assign to Available Coach
                                </label>
                                <select name="coach_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white font-medium">
                                    <option value="" disabled selected>-- Select a Coach --</option>
                                    @forelse($availableCoaches as $c)
                                        <option value="{{ $c['id'] }}">
                                            Coach {{ $c['name'] }} (Current load: {{ $c['current_load'] }}/4 Pax)
                                        </option>
                                    @empty
                                        <option value="" disabled>No coaches currently available for this date</option>
                                    @endforelse
                                </select>
                                <p class="text-[11px] text-[#6E6E73]">
                                    Standard safety ratio is 4:1. Assigning more than 4 students will log an ratio override exception.
                                </p>
                            </div>

                            <div>
                                <button type="submit" 
                                        @if(empty($availableCoaches) || count($availableCoaches) === 0) disabled @endif
                                        class="btn-primary w-full py-2.5 text-xs font-bold shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                    Confirm Assignment
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white rounded-2xl border border-[#E5E5EA] p-12 text-center space-y-3">
            <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto text-xl font-bold">
                ✓
            </div>
            <h3 class="text-base font-extrabold text-[#1D1D1F]">All Students Are Assigned!</h3>
            <p class="text-xs text-[#6E6E73] max-w-md mx-auto">
                There are currently no unassigned students in upcoming confirmed batches. All active participants are matched with certified instructors.
            </p>
            <div class="pt-2">
                <a href="{{ route('admin.coaches.index') }}" class="btn-secondary px-5 py-2 text-xs font-semibold inline-block">
                    View Coach Roster →
                </a>
            </div>
        </div>
    @endif

</div>
@endsection
