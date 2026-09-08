@extends('layouts.admin')

@section('title', 'Open Slot Requests | Coach Portal')

@section('content')
<div class="space-y-6" x-data="{ activeTab: '{{ $activeTab }}', applyModalOpen: false, selectedOpening: null }">
    
    <!-- Top Header & Tabs -->
    <div class="bg-white rounded-2xl p-6 border border-[#E5E5EA] shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-[#1D1D1F] mt-1">Open Dive Slot Requests</h1>
            <p class="text-xs text-[#6E6E73]">
                Volunteer to take unstaffed dive batches. Submitting interest alerts Camp Admin to review and make the official match.
            </p>
        </div>

        <!-- Tab Switcher -->
        <div class="flex items-center bg-[#F2F2F7] rounded-xl border border-[#E5E5EA] p-1">
            <button type="button" 
                    @click="activeTab = 'open_slots'"
                    :class="activeTab === 'open_slots' ? 'bg-white text-[#1D1D1F] shadow-xs font-bold' : 'text-[#6E6E73] font-semibold hover:text-[#1D1D1F]'"
                    class="px-4 py-2 rounded-lg text-xs transition-all flex items-center gap-2">
                <img src="{{ asset('icons/icons8-coach-60.png') }}" class="w-4 h-4 object-contain" alt="OpenSlots">
                <span>Available Openings ({{ count($openings) }})</span>
            </button>
            <button type="button" 
                    @click="activeTab = 'my_requests'"
                    :class="activeTab === 'my_requests' ? 'bg-white text-[#1D1D1F] shadow-xs font-bold' : 'text-[#6E6E73] font-semibold hover:text-[#1D1D1F]'"
                    class="px-4 py-2 rounded-lg text-xs transition-all flex items-center gap-2">
                <svg class="w-4 h-4 text-current" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                <span>My Requests ({{ count($myRequests) }})</span>
            </button>
        </div>
    </div>

    <!-- Available Camp Openings -->
    <div x-show="activeTab === 'open_slots'">
        @if(count($openings) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($openings as $op)
                    @php
                        $batch = $op->batch;
                        $hasApplied = in_array($op->id, $myRequestedOpeningIds);
                    @endphp

                    <div class="bg-white rounded-2xl border border-[#E5E5EA] p-6 shadow-xs hover:border-[#00C3D0] hover:shadow-md transition-all flex flex-col justify-between space-y-5">
                        
                        <!-- Slot Opening Information -->
                        <div class="space-y-3.5">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-bold px-3 py-1 rounded-full bg-[#00C3D0]/10 text-[#00AAB6] border border-[#00C3D0]/20 font-mono">
                                    {{ $batch?->batch_number ?? 'Dive Batch' }}
                                </span>
                            </div>

                            <div>
                                <h3 class="text-base font-extrabold text-[#1D1D1F] line-clamp-1">
                                    {{ $batch?->name ?? 'Camp Freediving Session' }}
                                </h3>
                            </div>

                            <div class="p-3.5 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] space-y-2 text-xs text-[#1D1D1F]">
                                <div class="flex items-center justify-between">
                                    <span class="text-[#6E6E73] font-medium">Dive Dates:</span>
                                    <span class="font-bold text-[#1D1D1F]">
                                        @if($batch && $batch->start_date && $batch->end_date && $batch->start_date->ne($batch->end_date))
                                            {{ $batch->start_date->format('M d') }} - {{ $batch->end_date->format('d, Y') }} ({{ $batch->start_date->format('D') }} - {{ $batch->end_date->format('D') }})
                                        @else
                                            {{ $op->dive_date->format('M d, Y') }}
                                        @endif
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-[#6E6E73] font-medium">Students Needing Coach:</span>
                                    <span class="font-extrabold text-[#00AAB6] bg-[#00C3D0]/10 px-2 py-0.5 rounded-md">
                                        {{ $op->needed_students_count }} student(s)
                                    </span>
                                </div>
                            </div>

                            @if($op->notes)
                                <div class="p-3 rounded-xl bg-[#F2F2F7]/70 border border-[#E5E5EA] text-xs text-[#3A3A3C] space-y-1">
                                    <span class="block text-[10px] font-bold uppercase tracking-wider text-[#8E8E93]">Camp Staff Note</span>
                                    <p class="text-[11px] leading-relaxed">{{ $op->notes }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Request Action -->
                        <div class="pt-2">
                            @if($hasApplied)
                                <div class="w-full py-2.5 rounded-xl bg-[#ffffff] text-[#8E8E93] border border-[#E5E5EA] text-xs font-bold text-center">
                                    Request Pending Admin Review
                                </div>
                            @else
                                <button type="button" 
                                        @click="selectedOpening = {{ json_encode($op) }}; applyModalOpen = true"
                                        class="w-full py-2.5 rounded-xl bg-[#00C3D0] hover:bg-[#00AAB6] text-white text-xs font-bold shadow-xs transition-all flex items-center justify-center gap-2">
                                    <span>Request This Slot</span>
                                    <span>→</span>
                                </button>
                            @endif
                        </div>

                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-2xl p-12 border border-[#E5E5EA] text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-[#ECFDF5] text-[#065F46] flex items-center justify-center mx-auto text-xl font-bold">
                    ✓
                </div>
                <h3 class="text-base font-bold text-[#1D1D1F]">All Camp Slots Currently Staffed</h3>
                <p class="text-xs text-[#6E6E73] max-w-md mx-auto">
                    There are no unstaffed dive openings broadcasted at the moment. When the camp has overflow students needing a coach, openings will appear here.
                </p>
            </div>
        @endif
    </div>

    <!-- Submitted Requests -->
    <div x-show="activeTab === 'my_requests'" class="space-y-4">
        @forelse($myRequests as $req)
            @php
                $batch = $req->batch ?: $req->opening?->batch;
                $badge = $req->status_badge;
            @endphp

            <div class="bg-white rounded-2xl border border-[#E5E5EA] p-5 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold px-2 py-0.5 rounded bg-gray-100 text-gray-700 font-mono">
                            {{ $batch?->batch_number ?? 'Dive Batch' }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded text-xs font-bold border {{ $badge['class'] }}">
                            {{ $badge['label'] }}
                        </span>
                    </div>

                    <h4 class="text-sm font-bold text-[#1D1D1F]">{{ $batch?->name ?? 'Camp Freedive Batch' }}</h4>
                    
                    <div class="text-xs text-[#6E6E73]">
                        Submitted on {{ $req->created_at->format('M d, Y • g:i A') }}
                    </div>

                    @if($req->status === 'not_selected')
                        <div class="text-xs text-[#6E6E73] italic">
                            ℹ️ This slot was filled by another coach. Thank you for volunteering!
                        </div>
                    @endif
                </div>

                <!-- Request Actions -->
                @if($req->status === 'pending')
                <div class="shrink-0">
                    <form action="{{ route('coach.requests.withdraw', $req) }}" method="POST" onsubmit="return confirm('Withdraw your request for this slot?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3.5 py-2 rounded-xl text-xs font-bold text-[#8E8E93] hover:text-rose-600 hover:bg-rose-50 border border-[#E5E5EA] transition-all">
                            Withdraw Request
                        </button>
                    </form>
                </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-2xl p-10 border border-[#E5E5EA] text-center text-xs text-[#8E8E93]">
                You haven't submitted any slot requests yet.
            </div>
        @endforelse
    </div>

    <!-- Request Slot Confirmation Modal -->
    <div x-show="applyModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-[#E5E5EA] space-y-6 relative" @click.away="applyModalOpen = false">
            
            <div class="flex items-start justify-between border-b border-[#E5E5EA] pb-4">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-[#00AAB6]">Volunteer Request</div>
                    <h3 class="text-lg font-black text-[#1D1D1F] mt-0.5">Request Open Dive Slot</h3>
                </div>
                <button type="button" @click="applyModalOpen = false" class="text-gray-400 hover:text-gray-600 text-lg font-bold">✕</button>
            </div>

            <template x-if="selectedOpening">
                <form :action="'{{ url('/coach/open-requests') }}/' + selectedOpening.id + '/apply'" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="p-4 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] space-y-2 text-xs">
                        <div class="font-bold text-sm text-[#1D1D1F]" x-text="selectedOpening.batch?.name"></div>
                        <div>Dive Date: <strong x-text="selectedOpening.dive_date"></strong></div>
                        <div>Students Needing Coach: <strong x-text="selectedOpening.needed_students_count"></strong></div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-[#1D1D1F]">
                            Optional Note to Camp Admin
                        </label>
                        <textarea name="notes" 
                                  rows="3" 
                                  placeholder="E.g., I have gear ready and available for this entire weekend..."
                                  class="w-full text-xs rounded-xl border-[#E5E5EA] focus:border-[#00C3D0] focus:ring-[#00C3D0] p-3"></textarea>
                    </div>

                    <div class="p-3 rounded-xl bg-blue-50 border border-blue-200 text-xs text-blue-900 leading-relaxed">
                        Submitting interest notifies Camp Admin. If selected, students will be automatically matched to your roster.
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="applyModalOpen = false" class="px-4 py-2.5 rounded-xl border border-[#E5E5EA] text-xs font-bold text-[#6E6E73] hover:bg-[#F2F2F7]">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#00C3D0] hover:bg-[#00AAB6] text-white text-xs font-bold shadow-sm transition-all">
                            Submit Slot Request
                        </button>
                    </div>
                </form>
            </template>

        </div>
    </div>

</div>
@endsection
