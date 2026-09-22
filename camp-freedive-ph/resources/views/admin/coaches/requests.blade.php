@extends('layouts.admin')

@section('title', 'Review Coach Requests | Camp FreedivePH')

@section('breadcrumb')
    <a href="{{ portal_route('coaches.index') }}" class="text-[#6E6E73] hover:text-[#780000] font-medium transition-colors">Coaches &amp; Schedules</a>
    <svg class="w-3.5 h-3.5 text-[#8E8E93] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
    <span class="font-bold text-[#1D1D1F]">Coach Requests</span>
@endsection

@section('content')
<div class="space-y-6 text-sm">
    
    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight">Review Coach Requests</h1>
        </div>

        <a href="{{ route('admin.coaches.matching') }}" class="btn-primary px-4 py-2 text-sm sm:text-sm font-bold shadow-2xs">
            Matching Queue
        </a>
    </div>

    <!-- Pending Requests Grouped by Batch -->
    <div class="space-y-6">

        @forelse($pendingRequests as $batchId => $groupRequests)
        @php 
            $batch = $groupRequests->first()->batch;
            $headcount = (int) $batch->total_participants_count;
            $coachesNeeded = $headcount > 0 ? (int) ceil($headcount / 4) : 0;
            $alreadyApproved = \App\Models\CoachRequest::where('batch_id', $batch->id)->where('status', 'approved')->count();
        @endphp
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-5 sm:p-6 shadow-2xs space-y-4" x-data="{ selectedRequests: [] }">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-mono font-extrabold text-base text-[#780000]">{{ $batch->batch_code }}</span>
                        <span class="px-2.5 py-1 rounded-md text-sm font-bold bg-amber-50 text-amber-900 border border-amber-200">
                            {{ $groupRequests->count() }} {{ Str::plural('Applicant', $groupRequests->count()) }}
                        </span>
                        <span class="px-2.5 py-1 rounded-md text-sm font-bold bg-[#F8EAEA] text-[#780000] border border-[#F1D5D5]">
                            {{ $alreadyApproved }} Approved (Recommended: {{ $coachesNeeded }})
                        </span>
                    </div>
                    <div class="space-y-1 text-sm text-[#6E6E73] mt-2">
                        <div>
                            <span class="font-bold text-[#1D1D1F]">{{ $batch->start_date->format('F d, Y (l)') }} to {{ $batch->end_date->format('F d, Y (l)') }}</span>
                        </div>
                        <div>
                            <span>Students Booked: <strong class="text-[#1D1D1F]">{{ $headcount }}</strong> {{ Str::plural('Student', $headcount) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Bulk Approve Action -->
                <form action="{{ auth()->user()->isOwner() ? route('owner.coaches.requests.bulk_approve') : route('admin.coaches.requests.bulk_approve') }}" 
                      method="POST" 
                      x-show="selectedRequests.length > 0" 
                      x-cloak>
                    @csrf
                    <template x-for="rId in selectedRequests" :key="rId">
                        <input type="hidden" name="request_ids[]" :value="rId">
                    </template>
                    <button type="submit" 
                            onclick="return confirm('Approve the selected ' + selectedRequests.length + ' coach applicant(s) for ' + '{{ $batch->batch_code }}' + '?')"
                            class="btn-primary px-4 py-2 text-sm font-bold shadow-2xs flex items-center gap-1.5">
                        <span>Approve Selected (<span x-text="selectedRequests.length"></span>)</span>
                    </button>
                </form>
            </div>

            <!-- Applied Coaches List with Multi-Select -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                @foreach($groupRequests as $req)
                <label class="bg-[#F2F2F7] p-4 rounded-xl flex flex-col justify-between gap-2.5 shadow-2xs hover:bg-white hover:border-[#D1D1D6] border border-[#E5E5EA] transition-all cursor-pointer select-none"
                       :class="selectedRequests.includes({{ $req->id }}) ? 'border-[#780000] bg-white' : ''">
                    <div class="space-y-2.5">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" 
                                   :value="{{ $req->id }}" 
                                   x-model="selectedRequests" 
                                   class="rounded text-[#780000] focus:ring-[#780000] w-4 h-4 cursor-pointer shrink-0">

                            <div class="w-8 h-8 rounded-full bg-[#F8EAEA] text-[#780000] font-bold text-sm flex items-center justify-center shrink-0">
                                {{ strtoupper(substr($req->coach->name, 0, 1)) }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <strong class="font-bold text-sm text-[#1D1D1F] block truncate">{{ $req->coach->name }}</strong>
                                <span class="text-sm text-[#8E8E93] block">Applied {{ $req->created_at->format('M d, Y g:i A') }}</span>
                            </div>
                        </div>

                        @if($req->notes)
                            <p class="text-sm text-[#3A3A3C] italic bg-white p-2.5 rounded-lg border border-[#E5E5EA] leading-relaxed">
                                "{{ $req->notes }}"
                            </p>
                        @endif
                    </div>
                </label>
                @endforeach
            </div>

        </div>
        @empty
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-8 text-center text-sm text-[#6E6E73] shadow-2xs">
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
            <div class="bg-[#F2F2F7] p-3.5 rounded-xl flex items-center justify-between gap-3 text-sm shadow-2xs">
                <div class="min-w-0 flex-1">
                    <strong class="text-[#1D1D1F] block truncate">{{ $rev->coach->name }}</strong>
                    <span class="text-[#6E6E73] text-sm block">{{ $rev->batch->batch_code }}</span>
                    <span class="text-sm text-[#8E8E93] block mt-0.5">
                        Reviewed by {{ $rev->reviewer ? $rev->reviewer->name : 'System' }} on {{ $rev->reviewed_at ? $rev->reviewed_at->format('M d, Y') : 'N/A' }}
                    </span>
                </div>

                <span class="px-2 py-0.5 rounded-md text-sm font-bold shrink-0 {{ $rev->status_badge['class'] }}">
                    {{ $rev->status_badge['label'] }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
