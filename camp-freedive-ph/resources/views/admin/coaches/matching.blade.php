@extends('layouts.admin')

@section('title', 'Batch Coach Assignment | Camp FreedivePH')

@section('content')
<div class="space-y-6 text-sm">
    
    <!-- Top Header & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight">Batch Coach Assignment</h1>
            <p class="text-xs sm:text-sm text-[#6E6E73] mt-1">
                Assign certified coaches to upcoming batches based on availability. Student groupings are organized on-site at camp.
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.coaches.requests') }}" class="btn-secondary px-3.5 py-2 text-xs sm:text-sm font-semibold flex items-center gap-2">
                <span>Coach Requests</span>
                @if(isset($pendingRequestsCount) && $pendingRequestsCount > 0)
                    <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-[#780000] text-white">
                        {{ $pendingRequestsCount }}
                    </span>
                @endif
            </a>

            <a href="{{ route('admin.coaches.index') }}" class="btn-secondary px-3.5 py-2 text-xs sm:text-sm font-semibold flex items-center gap-2">
                <span>Coach Roster</span>
            </a>
        </div>
    </div>

    <!-- Batch Groups Queue (2-Column Card Format) -->
    @if(isset($batchData) && count($batchData) > 0)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            @foreach($batchData as $group)
            @php
                $batch = $group['batch'];
                $totalPax = $group['total_participants'];
                $neededCoaches = $group['needed_coaches'];
                $assignedCoaches = $group['assigned_coaches'];
                $assignedCount = $group['assigned_count'];
                $availableCoaches = $group['available_coaches'];
                $isStaffed = $assignedCount >= $neededCoaches;
                $mlRec = $group['ml_recommendation'] ?? null;
            @endphp
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-5 sm:p-6 space-y-4 shadow-2xs flex flex-col justify-between" x-data="{ selectedCoaches: [] }">
                
                <!-- Group Header -->
                <div class="space-y-3 border-b border-[#E5E5EA] pb-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="font-extrabold text-base sm:text-lg text-[#1D1D1F]">
                                {{ $batch->batch_number }}
                            </h2>
                            <div class="space-y-0.5 text-xs text-[#6E6E73] mt-1.5">
                                <div>
                                    <span class="font-bold text-[#1D1D1F]">{{ $batch->start_date->format('M d') }} – {{ $batch->end_date->format('M d, Y') }}</span>
                                </div>
                                <div class="flex items-center gap-2 flex-wrap text-[#6E6E73] mt-0.5">
                                    <span>Participants: <strong class="text-[#1D1D1F]">{{ $totalPax }}</strong></span>
                                    <span>•</span>
                                    @if($totalPax > 0)
                                        <span>Recommended: <strong class="text-[#780000]">{{ $neededCoaches }} {{ Str::plural('coach', $neededCoaches) }}</strong></span>
                                    @else
                                        <span class="text-[#8E8E93] italic font-medium">0 Coaches Needed (No Participants)</span>
                                    @endif
                                </div>
                                @if(!empty($mlRec))
                                    <div class="pt-1.5">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-indigo-50/80 text-indigo-700 border border-indigo-200/70 shadow-2xs" title="Forecasted demand level: {{ $mlRec['demand_level'] ?? 'Normal' }} ({{ $mlRec['season_period'] ?? 'Season' }})">
                                            <svg class="w-3.5 h-3.5 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                            <span>Model Suggestion: <strong class="text-indigo-950">{{ $mlRec['instructors_needed'] ?? 2 }} {{ Str::plural('Coach', $mlRec['instructors_needed'] ?? 2) }}</strong> ({{ $mlRec['demand_level'] ?? 'Medium' }} Demand)</span>
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Right Column: Coaches to fill badge on top of Share to Coaches -->
                        <div class="flex flex-col items-end gap-2 shrink-0">
                            <!-- Coaches to Fill Badge -->
                            @if($totalPax > 0)
                                <span class="px-3 py-0.5 rounded-full text-xs font-black bg-[#F8EAEA] text-[#780000] border border-[#F1D5D5] inline-flex items-center justify-center whitespace-nowrap shadow-2xs">
                                    {{ $assignedCount }} / {{ $neededCoaches }} {{ Str::plural('Coach', $neededCoaches) }}
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#F2F2F7] text-[#6E6E73] border border-[#E5E5EA] inline-flex items-center justify-center whitespace-nowrap shadow-2xs">
                                    0 Needed
                                </span>
                            @endif

                            <!-- Broadcast Slot Button -->
                            @if($group['open_broadcast'])
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 inline-flex items-center gap-1.5 whitespace-nowrap">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                    Shared
                                </span>
                            @elseif($totalPax > 0)
                                <form action="{{ auth()->user()->isOwner() ? route('owner.coaches.matching.broadcast') : route('admin.coaches.matching.broadcast') }}" method="POST" class="inline-block">
                                    @csrf
                                    <input type="hidden" name="batch_id" value="{{ $batch->id }}">
                                    <button type="submit" 
                                            onclick="return confirm('Share an open coaching slot for {{ $batch->batch_number }} to all coaches in the portal?')"
                                            class="btn-secondary px-3 py-1.5 text-xs font-semibold whitespace-nowrap">
                                        <span>Share to Coaches</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                @if($totalPax > 0)
                    <!-- Assigned Coaches Section -->
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold text-[#1D1D1F] uppercase tracking-wider flex items-center gap-1.5">
                                <span>Assigned Coaches</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#F2F2F7] text-[#1D1D1F]">
                                    {{ $assignedCount }}
                                </span>
                            </h3>
                        </div>

                        @if($assignedCount > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($assignedCoaches as $assignedCoach)
                                    <div class="p-2.5 rounded-xl bg-[#FAFAFC] flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <div class="w-6 h-6 rounded-full bg-[#F8EAEA] text-[#780000] font-bold text-[10px] flex items-center justify-center shrink-0">
                                                {{ strtoupper(substr($assignedCoach->name, 0, 1)) }}
                                            </div>
                                            <span class="font-bold text-xs text-[#1D1D1F] truncate">{{ $assignedCoach->name }}</span>
                                        </div>

                                        <form action="{{ auth()->user()->isOwner() ? route('owner.coaches.matching.unassign') : route('admin.coaches.matching.unassign') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="batch_id" value="{{ $batch->id }}">
                                            <input type="hidden" name="coach_id" value="{{ $assignedCoach->id }}">
                                            <button type="submit" 
                                                    onclick="return confirm('Remove Coach {{ $assignedCoach->name }} from {{ $batch->batch_number }}?')"
                                                    class="text-[11px] font-bold text-rose-600 hover:text-rose-800 hover:bg-rose-50 px-1.5 py-0.5 rounded transition-colors shrink-0">
                                                Remove
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-3 rounded-xl text-center text-xs text-[#8E8E93] bg-[#FAFAFC] border border-dashed border-[#E5E5EA]">
                                No coaches assigned yet. Select available coaches below.
                            </div>
                        @endif
                    </div>

                    <!-- Available Coaches Section -->
                    <div class="space-y-2.5 pt-2">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <h3 class="text-xs font-bold text-[#1D1D1F] uppercase tracking-wider">
                                Available Coaches
                            </h3>

                            <!-- Bulk Assign Action -->
                            <form action="{{ auth()->user()->isOwner() ? route('owner.coaches.matching.assign') : route('admin.coaches.matching.assign') }}" method="POST" x-show="selectedCoaches.length > 0" x-cloak>
                                @csrf
                                <input type="hidden" name="batch_id" value="{{ $batch->id }}">
                                <template x-for="cId in selectedCoaches" :key="cId">
                                    <input type="hidden" name="coach_ids[]" :value="cId">
                                </template>
                                <button type="submit" class="btn-primary px-3 py-1 text-xs font-bold shadow-2xs flex items-center gap-1">
                                    <span>Assign Selected (<span x-text="selectedCoaches.length"></span>)</span>
                                </button>
                            </form>
                        </div>

                        <!-- Available Coach Selection Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @forelse($availableCoaches as $coachItem)
                                <label class="p-2.5 rounded-xl bg-[#FAFAFC] hover:bg-white hover:shadow-2xs  flex items-center gap-2 transition-all cursor-pointer select-none">
                                    <input type="checkbox" 
                                           :value="{{ $coachItem['id'] }}" 
                                           x-model="selectedCoaches"
                                           class="rounded text-[#780000] focus:ring-[#780000] w-4 h-4 cursor-pointer shrink-0">
                                    <span class="font-semibold text-xs text-[#1D1D1F] truncate">{{ $coachItem['name'] }}</span>
                                </label>
                            @empty
                                <div class="col-span-full p-3 rounded-xl text-center text-xs text-[#8E8E93] bg-[#FAFAFC]">
                                    No other available coaches on this schedule.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @else
                    <div class="py-5 px-4 rounded-xl text-center text-xs text-[#8E8E93] bg-[#FAFAFC] border border-dashed border-[#E5E5EA]">
                        No participants registered in this batch yet. Coach matching will open once students join.
                    </div>
                @endif

            </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-8 text-center space-y-2">
            <h3 class="font-bold text-base text-[#1D1D1F]">No Batches Currently Scheduled</h3>
            <p class="text-xs text-[#6E6E73]">When new batches are created, you can assign coaches to them here.</p>
        </div>
    @endif

</div>
@endsection
