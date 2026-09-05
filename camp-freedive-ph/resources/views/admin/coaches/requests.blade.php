@extends('layouts.admin')

@section('title', 'Review Coach Requests | Camp FreedivePH')

@section('content')
<div class="space-y-6 text-sm">
    
    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-5">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <a href="{{ route('admin.coaches.index') }}" class="text-xs font-semibold text-[#6E6E73] hover:text-[#780000] transition-colors flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                    <span>Coach Roster</span>
                </a>
                <span class="text-[#D1D1D6]">/</span>
                <span class="font-bold text-[#780000] text-xs">Coach Requests Queue</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight">Review Coach Requests</h1>
            <p class="text-xs sm:text-sm text-[#6E6E73] mt-1">
                Review slot applications submitted by coaches for upcoming weekend batches.
            </p>
        </div>

        <a href="{{ route('admin.coaches.matching') }}" class="btn-primary px-4 py-2 text-xs sm:text-sm font-bold shadow-2xs">
            Matching Queue
        </a>
    </div>

    <!-- Pending Requests Grouped by Batch -->
    <div class="space-y-6">

        @forelse($pendingRequests as $batchId => $groupRequests)
        @php 
            $batch = $groupRequests->first()->batch;
            $headcount = (int) $batch->total_participants_count ?: 4;
            $coachesNeeded = max(1, (int) ceil($headcount / 4));
            $alreadyApproved = \App\Models\CoachRequest::where('batch_id', $batch->id)->where('status', 'approved')->count();
            $slotsRemaining = max(0, $coachesNeeded - $alreadyApproved);
        @endphp
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-5 sm:p-6 shadow-2xs space-y-4">
            
            <div class="border-b border-[#E5E5EA] pb-4">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-mono font-extrabold text-base text-[#780000]">{{ $batch->batch_code }}</span>
                    <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-amber-50 text-amber-900 border border-amber-200">
                        {{ $groupRequests->count() }} {{ Str::plural('Applicant', $groupRequests->count()) }}
                    </span>
                    <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-[#F8EAEA] text-[#780000] border border-[#F1D5D5]">
                        {{ $alreadyApproved }}/{{ $coachesNeeded }} Approved (Recommended: {{ $coachesNeeded }})
                    </span>
                </div>
                <div class="space-y-1 text-xs text-[#6E6E73] mt-2">
                    <div>
                        <span class="font-bold text-[#1D1D1F]">{{ $batch->start_date->format('F d, Y (l)') }} – {{ $batch->end_date->format('F d, Y (l)') }}</span>
                    </div>
                    <div>
                        <span>Students Booked: <strong class="text-[#1D1D1F]">{{ $headcount }}</strong> {{ Str::plural('Student', $headcount) }}</span>
                    </div>
                </div>
            </div>

            <!-- Applied Coaches List -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                @foreach($groupRequests as $req)
                <div class="bg-[#FAFAFC] p-4 rounded-xl flex flex-col justify-between gap-3 shadow-2xs">
                    <div class="space-y-2.5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-full bg-[#F8EAEA] text-[#780000] font-bold text-xs flex items-center justify-center shrink-0">
                                {{ strtoupper(substr($req->coach->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <strong class="font-bold text-xs text-[#1D1D1F] block truncate">{{ $req->coach->name }}</strong>
                                <span class="text-[11px] text-[#8E8E93] block">Applied {{ $req->created_at->format('M d, Y g:i A') }}</span>
                            </div>
                        </div>

                        @if($req->notes)
                            <p class="text-xs text-[#3A3A3C] italic bg-white p-2.5 rounded-lg border border-[#E5E5EA] leading-relaxed">
                                "{{ $req->notes }}"
                            </p>
                        @endif
                    </div>

                    <div class="pt-2 border-t border-[#E5E5EA]">
                        <form action="{{ route('admin.coaches.requests.approve', $req) }}" method="POST" class="w-full"
                              onsubmit="return confirm('Approve Coach {{ $req->coach->name }} for {{ $batch->batch_code }}? {{ $slotsRemaining > 1 ? 'Slot ' . ($alreadyApproved + 1) . ' of ' . $coachesNeeded . ' will be filled. Other applicants remain available for the next slot.' : 'This will fill the final coach slot and complete the roster for this batch.' }}');">
                            @csrf
                            <button type="submit" class="btn-primary w-full py-2 text-xs font-bold shadow-2xs">
                                Approve Coach (Slot {{ $alreadyApproved + 1 }}/{{ $coachesNeeded }})
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>

        </div>
        @empty
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-8 text-center text-xs text-[#6E6E73] shadow-2xs">
            No pending coach requests at this time.
        </div>
        @endforelse
    </div>

    <!-- Reviewed Request History -->
    @if($reviewedRequests->count() > 0)
    <div class="bg-white rounded-xl border border-[#E5E5EA] p-5 sm:p-6 shadow-2xs space-y-4">
        <h3 class="text-base font-bold text-[#1D1D1F] border-b border-[#E5E5EA] pb-3">Reviewed History</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
            @foreach($reviewedRequests as $rev)
            <div class="bg-[#FAFAFC] p-3.5 rounded-xl flex items-center justify-between gap-3 text-xs shadow-2xs">
                <div class="min-w-0 flex-1">
                    <strong class="text-[#1D1D1F] block truncate">{{ $rev->coach->name }}</strong>
                    <span class="text-[#6E6E73] text-[11px] block">{{ $rev->batch->batch_code }}</span>
                    <span class="text-[10px] text-[#8E8E93] block mt-0.5">
                        Reviewed by {{ $rev->reviewer ? $rev->reviewer->name : 'System' }} on {{ $rev->reviewed_at ? $rev->reviewed_at->format('M d, Y') : 'N/A' }}
                    </span>
                </div>

                <span class="px-2 py-0.5 rounded-md text-[11px] font-bold border shrink-0 {{ $rev->status_badge['class'] }}">
                    {{ $rev->status_badge['label'] }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
