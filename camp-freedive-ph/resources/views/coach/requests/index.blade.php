@extends('layouts.admin')

@section('title', 'Open Slot Requests | Coach Portal')

@section('content')
<div class="space-y-6" x-data="{ activeTab: '{{ $activeTab }}', applyModalOpen: false, selectedOpening: null }">
    
    <!-- Top Header & Tabs -->
    <div class="bg-white rounded-2xl p-4 sm:p-6 border border-[#E5E5EA] flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-[#1D1D1F] mt-1">Open Dive Slot Requests</h1>
            <p class="text-sm text-[#6E6E73] leading-relaxed">
                Volunteer to take unstaffed dive batches. Submitting interest alerts Camp Admin to review and make the official match.
            </p>
        </div>

        <!-- Tab Switcher -->
        <div class="flex items-center bg-[#F2F2F7] rounded-xl border border-[#E5E5EA] p-1 w-full md:w-auto">
            <button type="button" 
                    @click="activeTab = 'open_slots'"
                    :class="activeTab === 'open_slots' ? 'bg-white text-[#1D1D1F] font-bold' : 'text-[#6E6E73] font-semibold hover:text-[#1D1D1F]'"
                    class="flex-1 md:flex-initial px-3 sm:px-4 py-2 rounded-lg text-sm transition-all flex items-center justify-center gap-1.5 sm:gap-2">
                <span>Available Openings ({{ count($openings) }})</span>
            </button>
            <button type="button" 
                    @click="activeTab = 'my_requests'"
                    :class="activeTab === 'my_requests' ? 'bg-white text-[#1D1D1F] font-bold' : 'text-[#6E6E73] font-semibold hover:text-[#1D1D1F]'"
                    class="flex-1 md:flex-initial px-3 sm:px-4 py-2 rounded-lg text-sm transition-all flex items-center justify-center gap-1.5 sm:gap-2">
                <span>My Requests ({{ count($myRequests) }})</span>
            </button>
        </div>
    </div>

    <!-- Available Camp Openings (3-column grid) -->
    <div x-show="activeTab === 'open_slots'">
        @if(count($openings) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                @foreach($openings as $op)
                    @php
                        $batch = $op->batch;
                        $hasApplied = in_array($op->id, $myRequestedOpeningIds);
                    @endphp

                    <div class="bg-white rounded-2xl border border-[#E5E5EA] p-4 sm:p-5 hover:border-[#00C3D0] hover:shadow-md transition-all flex flex-col justify-between space-y-4">
                        
                        <!-- Slot Opening Information -->
                        <div class="space-y-3">
                            <div class="flex items-start justify-between gap-2.5">
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-base sm:text-lg font-black text-[#1D1D1F] tracking-tight">
                                        {{ $batch?->batch_number ?? $batch?->name ?? 'Camp Freediving Session' }}
                                    </h3>
                                </div>
                                <span class="text-sm font-black px-2.5 py-1 rounded-lg bg-amber-100 text-amber-900 tracking-wider uppercase shrink-0 whitespace-nowrap">
                                    Needs Coach
                                </span>
                            </div>

                            <div class="space-y-1 text-sm text-[#6E6E73] pt-0.5">
                                <p>
                                    Dive Dates: <strong class="text-[#1D1D1F]">
                                        @if($batch && $batch->start_date && $batch->end_date && $batch->start_date->ne($batch->end_date))
                                            {{ $batch->start_date->format('M d') }} - {{ $batch->end_date->format('d, Y') }} ({{ $batch->start_date->format('D') }} - {{ $batch->end_date->format('D') }})
                                        @else
                                            {{ $op->dive_date->format('M d, Y') }}
                                        @endif
                                    </strong>
                                </p>
                                <p>
                                    Students Needing Coach: <strong class="text-[#1D1D1F]">{{ $op->needed_students_count }} Diver(s)</strong>
                                </p>
                            </div>

                            @if($op->notes)
                                <div >
                                    <span class="block text-sm font-bold uppercase tracking-wider text-[#8E8E93]">Camp Staff Note</span>
                                    <p class="text-sm text-[#3A3A3C] leading-relaxed">{{ $op->notes }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Request Action -->
                        <div class="pt-2">
                            @if($hasApplied)
                                <div class="w-full py-2.5 rounded-xl bg-[#F2F2F7] text-[#8E8E93] text-sm font-bold text-center">
                                    Request Pending Admin Review
                                </div>
                            @else
                                <button type="button" 
                                        @click="selectedOpening = {{ json_encode($op) }}; applyModalOpen = true"
                                        class="w-full py-2.5 rounded-xl bg-[#00C3D0] hover:bg-[#00AAB6] text-white text-sm font-bold transition-all flex items-center justify-center gap-2">
                                    <span>Request This Slot</span>
                                    <span>→</span>
                                </button>
                            @endif
                        </div>

                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-2xl p-8 sm:p-12 border border-[#E5E5EA] text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-[#ECFDF5] text-[#065F46] flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6 text-[#065F46]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <h3 class="text-base font-bold text-[#1D1D1F]">All Camp Slots Currently Staffed</h3>
                <p class="text-sm text-[#6E6E73] max-w-md mx-auto leading-relaxed">
                    There are no unstaffed dive openings broadcasted at the moment. When the camp has overflow students needing a coach, openings will appear here.
                </p>
            </div>
        @endif
    </div>

    <!-- Submitted Requests (3-column grid) -->
    <div x-show="activeTab === 'my_requests'">
        @if(count($myRequests) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                @foreach($myRequests as $req)
                    @php
                        $batch = $req->batch ?: $req->opening?->batch;
                        $badge = $req->status_badge;
                        $diveDate = $batch?->start_date ?: $req->opening?->dive_date;
                    @endphp

                    <div class="bg-white rounded-2xl border border-[#E5E5EA] p-4 sm:p-5 hover:border-[#00C3D0] hover:shadow-md transition-all flex flex-col justify-between space-y-4">
                        
                        <!-- Request Details -->
                        <div class="space-y-3">
                            <div class="flex items-start justify-between gap-2.5">
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-base sm:text-lg font-black text-[#1D1D1F] tracking-tight">
                                        {{ $batch?->batch_number ?? $batch?->name ?? 'Camp Freedive Batch' }}
                                    </h3>
                                </div>
                                <span class="px-2.5 py-1 rounded-lg text-sm font-black tracking-wider uppercase shrink-0 whitespace-nowrap {{ $badge['class'] }}">
                                    {{ $badge['label'] }}
                                </span>
                            </div>

                            <div class="space-y-1 text-sm text-[#6E6E73] pt-0.5">
                                <p>
                                    Dive Dates: <strong class="text-[#1D1D1F]">
                                        @if($batch && $batch->start_date && $batch->end_date && $batch->start_date->ne($batch->end_date))
                                            {{ $batch->start_date->format('M d') }} - {{ $batch->end_date->format('d, Y') }} ({{ $batch->start_date->format('D') }} - {{ $batch->end_date->format('D') }})
                                        @elseif($diveDate)
                                            {{ $diveDate->format('M d, Y') }}
                                        @else
                                            Weekend Batch
                                        @endif
                                    </strong>
                                </p>
                                <p>
                                    Submitted: <strong class="text-[#1D1D1F]">{{ $req->created_at->format('M d, Y') }} at {{ $req->created_at->format('g:i A') }}</strong>
                                </p>
                            </div>

                            @if($req->notes)
                                <div >
                                    <span class="block text-sm font-bold uppercase tracking-wider text-[#8E8E93]">Your Note</span>
                                    <p class="text-sm text-[#3A3A3C] leading-relaxed">{{ $req->notes }}</p>
                                </div>
                            @endif

                                @if($req->status === 'not_selected')
                                <div class="p-2.5 rounded-xl bg-gray-50 border border-gray-200 text-sm text-[#6E6E73] flex items-center gap-2">
                                    <svg class="w-4 h-4 shrink-0 text-[#8E8E93]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                                    <span>This slot was filled by another coach. Thank you for volunteering!</span>
                                </div>
                            @endif
                        </div>

                        <!-- Request Actions -->
                        <div class="pt-2">
                            @if($req->status === 'pending')
                                <form action="{{ route('coach.requests.withdraw', $req) }}" method="POST" onsubmit="return confirm('Withdraw your request for this slot?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full py-2.5 rounded-xl text-sm font-bold text-[#8E8E93] hover:text-rose-600 hover:bg-rose-50 border border-[#E5E5EA] hover:border-rose-200 transition-all text-center">
                                        Withdraw Request
                                    </button>
                                </form>
                            @elseif($req->status === 'approved')
                                <div class="w-full py-2 rounded-xl bg-emerald-50 text-emerald-700 text-sm font-bold text-center border border-emerald-200">
                                    Added to Your Schedule
                                </div>
                            @else
                                <div class="w-full py-2 rounded-xl bg-gray-100 text-gray-500 text-sm font-bold text-center">
                                    Request Concluded
                                </div>
                            @endif
                        </div>

                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-2xl p-10 border border-[#E5E5EA] text-center text-sm text-[#8E8E93]">
                You haven't submitted any slot requests yet.
            </div>
        @endif
    </div>

    <!-- Request Slot Confirmation Modal -->
    <div x-show="applyModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-[#E5E5EA] space-y-6 relative" @click.away="applyModalOpen = false">
            
            <div class="flex items-start justify-between border-b border-[#E5E5EA] pb-4">
                <div>
                    <h3 class="text-lg font-black text-[#1D1D1F]">Request Open Dive Slot</h3>
                    <p class="text-sm font-semibold text-[#00838F] mt-0.5">Volunteer Request</p>
                </div>
                <button type="button" @click="applyModalOpen = false" aria-label="Close volunteer request modal" class="text-gray-400 hover:text-gray-600 text-lg font-bold">✕</button>
            </div>

            <template x-if="selectedOpening">
                <form :action="'{{ url('/coach/open-requests') }}/' + selectedOpening.id + '/apply'" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="p-4 rounded-xl bg-[#F2F2F7] space-y-2 text-sm shadow-2xs">
                        <div class="font-bold text-sm text-[#1D1D1F]" x-text="selectedOpening.batch?.name"></div>
                        <div>Dive Date: <strong x-text="selectedOpening.dive_date"></strong></div>
                        <div>Students Needing Coach: <strong x-text="selectedOpening.needed_students_count"></strong></div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-sm font-bold text-[#1D1D1F]">
                            Optional Note to Camp Admin
                        </label>
                        <textarea name="notes" 
                                  rows="3" 
                                  placeholder="E.g., I have gear ready and available for this entire weekend..."
                                  class="w-full text-sm rounded-xl border-[#E5E5EA] focus:border-[#00C3D0] focus:ring-[#00C3D0] p-3"></textarea>
                    </div>

                    <div class="p-3 rounded-xl bg-blue-50 border border-blue-200 text-sm text-blue-900 leading-relaxed">
                        Submitting interest notifies Camp Admin. If selected, students will be automatically matched to your roster.
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="applyModalOpen = false" class="btn-secondary px-4 py-2 text-sm font-bold">
                            Cancel
                        </button>
                        <button type="submit" class="btn-primary px-5 py-2 text-sm font-bold">
                            Submit Slot Request
                        </button>
                    </div>
                </form>
            </template>

        </div>
    </div>

</div>
@endsection
