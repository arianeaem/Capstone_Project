@extends('layouts.admin')

@section('title', 'Batch Coach Assignment | Camp FreedivePH')

@section('content')
<div class="space-y-6 text-sm">
    
    <!-- Top Header & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-5">
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

    <!-- Batch Groups Queue -->
    @if(isset($batchData) && count($batchData) > 0)
        <div class="space-y-6">
            @foreach($batchData as $group)
            @php
                $batch = $group['batch'];
                $totalPax = $group['total_participants'];
                $neededCoaches = $group['needed_coaches'];
                $assignedCoaches = $group['assigned_coaches'];
                $assignedCount = $group['assigned_count'];
                $availableCoaches = $group['available_coaches'];
                $isStaffed = $assignedCount >= $neededCoaches;
            @endphp
            <div class="bg-white rounded-xl border border-[#E5E5EA] p-5 sm:p-6 space-y-5 shadow-2xs" x-data="{ selectedCoaches: [] }">
                
                <!-- Group Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-4">
                    <div>
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <h2 class="font-extrabold text-lg text-[#1D1D1F]">
                                {{ $batch->batch_number }}
                            </h2>
                            <span class="text-xs px-2.5 py-1 rounded-md font-bold {{ $isStaffed ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : ($assignedCount > 0 ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-rose-50 text-rose-800 border border-rose-200') }}">
                                {{ $isStaffed ? '✓ Fully Staffed (' . $assignedCount . '/' . $neededCoaches . ')' : ($assignedCount > 0 ? 'Partially Staffed (' . $assignedCount . '/' . $neededCoaches . ')' : 'Unstaffed (0/' . $neededCoaches . ')') }}
                            </span>
                        </div>
                        <div class="space-y-1 text-xs text-[#6E6E73] mt-2">
                            <div>
                                <span class="font-bold text-[#1D1D1F]">{{ $batch->start_date->format('M d') }} – {{ $batch->end_date->format('M d, Y') }}</span>
                            </div>
                            <div>
                                <span>Participants: <strong class="text-[#1D1D1F]">{{ $totalPax }}</strong> {{ Str::plural('Participant', $totalPax) }}</span>
                            </div>
                            <div>
                                <span>Recommended Coaches: <strong class="text-[#1D1D1F]">{{ $neededCoaches }}</strong> {{ Str::plural('Coach', $neededCoaches) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- Broadcast Slot Button -->
                        @if($group['open_broadcast'])
                            <span class="px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 inline-flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                Slot Shared to Portal
                            </span>
                        @else
                            <form action="{{ route('admin.coaches.matching.broadcast') }}" method="POST" class="inline-block">
                                @csrf
                                <input type="hidden" name="batch_id" value="{{ $batch->id }}">
                                <button type="submit" 
                                        onclick="return confirm('Share an open coaching slot for {{ $batch->batch_number }} to all coaches in the portal?')"
                                        class="btn-secondary px-3.5 py-1.5 text-xs font-semibold flex items-center gap-1.5">
                                    <span>Share to Coaches</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- 2-Section Grid: Currently Assigned Coaches (Left) + Available Coaches to Assign (Right) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    
                    <!-- Left: Currently Assigned Coaches (4 Cols) -->
                    <div class="lg:col-span-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold text-[#1D1D1F] uppercase tracking-wider flex items-center gap-1.5">
                                <span>Assigned Coaches</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#F2F2F7] text-[#1D1D1F]">
                                    {{ $assignedCount }}
                                </span>
                            </h3>
                        </div>

                        @if($assignedCount > 0)
                            <div class="space-y-2">
                                @foreach($assignedCoaches as $assignedCoach)
                                    <div class="p-3 rounded-xl bg-[#FAFAFC] flex items-center justify-between gap-2.5 shadow-2xs">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <div class="w-7 h-7 rounded-full bg-[#F8EAEA] text-[#780000] font-bold text-xs flex items-center justify-center shrink-0">
                                                {{ strtoupper(substr($assignedCoach->name, 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-bold text-xs text-[#1D1D1F] truncate">{{ $assignedCoach->name }}</div>
                                            </div>
                                        </div>

                                        <form action="{{ route('admin.coaches.matching.unassign') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="batch_id" value="{{ $batch->id }}">
                                            <input type="hidden" name="coach_id" value="{{ $assignedCoach->id }}">
                                            <button type="submit" 
                                                    onclick="return confirm('Remove Coach {{ $assignedCoach->name }} from {{ $batch->batch_number }}?')"
                                                    class="text-xs font-bold text-rose-600 hover:text-rose-800 hover:bg-rose-50 px-2 py-1 rounded-lg transition-colors">
                                                Remove
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-4 rounded-xl text-center space-y-1 bg-[#FAFAFC]">
                                <div class="text-xs font-semibold text-[#6E6E73]">No coaches assigned yet</div>
                                <div class="text-[11px] text-[#8E8E93]">Select from available coaches to assign.</div>
                            </div>
                        @endif
                    </div>

                    <!-- Right: Available Coaches (8 Cols) -->
                    <div class="lg:col-span-8 space-y-3">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <h3 class="text-xs font-bold text-[#1D1D1F] uppercase tracking-wider">
                                Available Coaches for this Batch
                            </h3>

                            <!-- Bulk Assign Action -->
                            <form action="{{ route('admin.coaches.matching.assign') }}" method="POST" x-show="selectedCoaches.length > 0" x-cloak>
                                @csrf
                                <input type="hidden" name="batch_id" value="{{ $batch->id }}">
                                <template x-for="cId in selectedCoaches" :key="cId">
                                    <input type="hidden" name="coach_ids[]" :value="cId">
                                </template>
                                <button type="submit" class="btn-primary px-3 py-1 text-xs font-bold shadow-2xs">
                                    Assign Selected (<span x-text="selectedCoaches.length"></span>)
                                </button>
                            </form>
                        </div>

                        <!-- Compact Card Format Grid for Available Coaches -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-2.5">
                            @forelse($availableCoaches as $coachItem)
                                <label class="p-3 rounded-xl bg-[#FAFAFC] hover:bg-white hover:shadow-2xs flex items-center gap-2.5 transition-all cursor-pointer select-none">
                                    <input type="checkbox" 
                                           :value="{{ $coachItem['id'] }}" 
                                           x-model="selectedCoaches"
                                           class="rounded text-[#780000] focus:ring-[#780000] w-4 h-4 cursor-pointer shrink-0">
                                    <div class="min-w-0 flex-1">
                                        <div class="font-bold text-xs text-[#1D1D1F] truncate">{{ $coachItem['name'] }}</div>
                                    </div>
                                </label>
                            @empty
                                <div class="col-span-full p-4 rounded-xl text-center text-xs text-[#8E8E93] bg-[#FAFAFC]">
                                    No available coaches for this batch's dates.
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>

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
