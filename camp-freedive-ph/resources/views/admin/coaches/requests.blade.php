@extends('layouts.admin')

@section('title', 'Review Coach Requests | Camp FreedivePH')

@section('content')
<div class="space-y-6 text-sm">
    
    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-5">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.coaches.index') }}" class="text-xs text-[#6E6E73] hover:text-[#1D1D1F]">
                    ← Back to Coach Roster
                </a>
                <span class="text-[#D1D1D6]">/</span>
                <span class="font-bold text-[#780000]">Coach Requests Queue</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight mt-1">Review Coach Requests</h1>
            <p class="text-xs sm:text-sm text-[#6E6E73] mt-1">
                Review slot applications submitted by coaches for upcoming weekend batches.
            </p>
        </div>

        <a href="{{ route('admin.coaches.matching') }}" class="btn-primary px-4 py-2 text-xs sm:text-sm font-bold shadow-sm">
            Matching Queue →
        </a>
    </div>

    <!-- PENDING REQUESTS GROUPED BY BATCH -->
    <div class="space-y-6">
        <h2 class="text-base font-bold text-[#1D1D1F]">Pending Open Slot Applications</h2>

        @forelse($pendingRequests as $batchId => $groupRequests)
        @php 
            $batch = $groupRequests->first()->batch;
            $headcount = $batch->booked_headcount ?: 4;
            $coachesNeeded = max(1, (int) ceil($headcount / 4));
            $alreadyApproved = \App\Models\CoachRequest::where('batch_id', $batch->id)->where('status', 'approved')->count();
            $slotsRemaining = max(0, $coachesNeeded - $alreadyApproved);
        @endphp
        <div class="bg-white rounded-xl p-6 shadow-sm space-y-4">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#E5E5EA] pb-3">
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-mono font-extrabold text-base text-[#780000]">{{ $batch->batch_code }}</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#FEF3C7] text-[#92400E] border border-[#FDE68A]">
                            {{ $groupRequests->count() }} Applicant(s)
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-800 border border-blue-200">
                            Needs {{ $coachesNeeded }} Coach(es) • {{ $alreadyApproved }}/{{ $coachesNeeded }} Approved
                        </span>
                    </div>
                    <span class="text-xs text-[#6E6E73] block mt-0.5">
                        {{ $batch->start_date->format('F d, Y (l)') }} - {{ $batch->end_date->format('F d, Y (l)') }} • {{ $headcount }} Students Booked
                    </span>
                </div>
            </div>

            <!-- Coaches Applied Table / List -->
            <div class="divide-y divide-[#E5E5EA]">
                @foreach($groupRequests as $req)
                <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#780000] text-white flex items-center justify-center font-bold text-base shrink-0 shadow-sm">
                            {{ substr($req->coach->name, 0, 1) }}
                        </div>
                        <div>
                            <strong class="font-bold text-[#1D1D1F] text-sm block">{{ $req->coach->name }}</strong>
                            <span class="text-xs text-[#8E8E93] block mt-0.5">Applied at {{ $req->created_at->format('M d, Y h:i A') }}</span>
                            @if($req->notes)
                                <p class="text-xs text-[#1D1D1F] italic mt-1 bg-[#FAFAFC] p-2 rounded-lg border border-[#E5E5EA]">
                                    "{{ $req->notes }}"
                                </p>
                            @endif
                        </div>
                    </div>

                    <form action="{{ route('admin.coaches.requests.approve', $req) }}" method="POST" 
                          onsubmit="return confirm('Approve Coach {{ $req->coach->name }} for {{ $batch->batch_code }}? {{ $slotsRemaining > 1 ? 'Slot ' . ($alreadyApproved + 1) . ' of ' . $coachesNeeded . ' will be filled. Other applicants remain available for the next slot.' : 'This will fill the final coach slot and complete the roster for this batch.' }}');">
                        @csrf
                        <button type="submit" class="btn-primary px-5 py-2 text-xs font-bold shadow-sm whitespace-nowrap">
                            Approve Coach (Slot {{ $alreadyApproved + 1 }}/{{ $coachesNeeded }})
                        </button>
                    </form>
                </div>
                @endforeach
            </div>

        </div>
        @empty
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-8 text-center text-xs text-[#6E6E73] shadow-sm">
            No pending coach requests at this time.
        </div>
        @endforelse
    </div>

    <!-- REVIEWED HISTORY -->
    @if($reviewedRequests->count() > 0)
    <div class="bg-white rounded-xl p-6 shadow-sm space-y-4">
        <h3 class="text-base font-bold text-[#1D1D1F] border-b border-[#E5E5EA] pb-3">Reviewed History</h3>

        <div class="divide-y divide-[#E5E5EA]">
            @foreach($reviewedRequests as $rev)
            <div class="py-3 flex items-center justify-between text-xs">
                <div>
                    <strong class="text-[#1D1D1F]">{{ $rev->coach->name }}</strong>
                    <span class="text-[#6E6E73] ml-2">for {{ $rev->batch->batch_code }}</span>
                    <span class="text-xs text-[#8E8E93] block mt-0.5">
                        Reviewed by {{ $rev->reviewer ? $rev->reviewer->name : 'System' }} on {{ $rev->reviewed_at ? $rev->reviewed_at->format('M d, Y') : 'N/A' }}
                    </span>
                </div>

                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $rev->status_badge['class'] }}">
                    {{ $rev->status_badge['label'] }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
