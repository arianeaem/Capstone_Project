@extends('layouts.admin')

@section('title', 'Weather & Marine Safety Monitoring | Camp FreedivePH')

@section('content')
<div class="space-y-6 text-sm">
    
    <!-- Top Header & Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight">Weather & Marine Safety</h1>
            <p class="text-xs sm:text-sm text-[#6E6E73] mt-1">
                Real-time sea conditions and safety status for upcoming dive trips in Mabini, Batangas.
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <span class="text-xs text-[#6E6E73] bg-[#FAFAFC] px-3 py-1.5 rounded-xl border border-[#E5E5EA]">
                Timezone: <strong>Asia/Manila</strong>
            </span>

            <!-- Sync Forecast Cache Button -->
            <form action="{{ route('admin.weather.sync_cache') }}" method="POST">
                @csrf
                <button type="submit" class="btn-primary px-3.5 py-1.5 text-xs font-bold flex items-center gap-1.5 shadow-sm hover:opacity-95 transition-all">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                    <span>Sync Forecast Now</span>
                </button>
            </form>
        </div>
    </div>

    <!-- 16-Day Forecast Horizon Strip (if masterForecast cached) -->
    @if(isset($masterForecast['daily_summaries']) && !empty($masterForecast['daily_summaries']))
    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-[#6E6E73]">16-Day Whole-Day Horizon</h3>
            <span class="text-[11px] text-[#8E8E93]">Scroll horizontally →</span>
        </div>

        <div class="flex items-center gap-2.5 overflow-x-auto pb-2 scrollbar-thin">
            @foreach($masterForecast['daily_summaries'] as $dateStr => $daySummary)
            @php
                $dayCarbon = \Carbon\Carbon::parse($dateStr);
                $isToday = $dayCarbon->isToday();
                $riskColor = match($daySummary['overall_classification'] ?? 'Safe') {
                    'Very Safe', 'Safe' => 'border-[#A7F3D0] bg-[#ECFDF5] text-[#065F46]',
                    'Moderate' => 'border-[#FDE68A] bg-[#FFFBEB] text-[#92400E]',
                    'High Risk', 'Critical Risk' => 'border-[#FECACA] bg-[#FEF2F2] text-[#991B1B]',
                    default => 'border-[#E5E5EA] bg-white text-[#1D1D1F]',
                };
            @endphp
            <div class="min-w-[130px] p-3 rounded-xl border {{ $riskColor }} text-center shrink-0 space-y-1">
                <div class="text-[11px] font-bold text-[#1D1D1F]">
                    {{ $dayCarbon->format('D, M d') }}
                </div>
                <div class="text-xs font-black uppercase tracking-wider">
                    {{ $daySummary['overall_classification'] ?? 'Safe' }}
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Critical / High Risk Advisory Banner -->
    @if($criticalCount > 0)
    <div class="p-4 bg-[#FEF2F2] rounded-xl border border-[#FECACA] flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-[#991B1B]">
        <div class="flex items-center gap-2.5">
            <div>
                <strong>{{ $criticalCount }} batch(es)</strong> are currently classified as <strong>High Risk</strong> or <strong>Critical Risk</strong>. Review conditions immediately to confirm go/no-go or initiate cancellation flows.
            </div>
        </div>
        <span class="font-bold uppercase tracking-wider text-xs text-[#DC2626]">Safety Advisory</span>
    </div>
    @endif

    <!-- Search & Filters (Flat border, no shadow) -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-5 space-y-4">
        <form action="{{ route('admin.weather.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            
            <!-- Risk Classification -->
            <div>
                <label class="block font-bold text-[#1D1D1F] mb-1">Risk Level</label>
                <select name="risk" onchange="this.form.submit()" class="w-full px-3.5 pr-10 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white font-medium">
                    <option value="">All Risk Levels</option>
                    <option value="very_safe" {{ request('risk') === 'very_safe' ? 'selected' : '' }}>Very Safe (Calm)</option>
                    <option value="safe" {{ request('risk') === 'safe' ? 'selected' : '' }}>Safe</option>
                    <option value="moderate" {{ request('risk') === 'moderate' ? 'selected' : '' }}>Moderate</option>
                    <option value="high_risk" {{ request('risk') === 'high_risk' ? 'selected' : '' }}>High Risk</option>
                    <option value="critical_risk" {{ request('risk') === 'critical_risk' ? 'selected' : '' }}>Critical</option>
                </select>
            </div>

            <!-- Batch Status -->
            <div>
                <label class="block font-bold text-[#1D1D1F] mb-1">Batch Status</label>
                <select name="status" onchange="this.form.submit()" class="w-full px-3.5 pr-10 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white font-medium">
                    <option value="">All Statuses</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed (Active)</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="rescheduled" {{ request('status') === 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                    <option value="cancelled_by_camp" {{ request('status') === 'cancelled_by_camp' ? 'selected' : '' }}>Cancelled by Camp</option>
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block font-bold text-[#1D1D1F] mb-1">Date From</label>
                <input type="date" name="date_from" onchange="this.form.submit()" value="{{ request('date_from') }}" class="w-full px-3.5 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white font-medium">
            </div>

            <!-- Reset Button (If Filtered) -->
            <div class="flex items-end">
                @if(request()->anyFilled(['risk', 'status', 'date_from', 'date_to']))
                    <a href="{{ route('admin.weather.index') }}" class="btn-secondary px-4 py-2 text-xs text-center w-full block font-semibold">
                        Clear Filters
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Weather Safety Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($batches as $batch)
        @php
            $day1 = $batch->latestDay1Assessment;
            $day2 = $batch->latestDay2Assessment;
            $override = $batch->latestManualOverride;
        @endphp
        
        <div class="bg-white rounded-2xl border border-[#E5E5EA] p-5 hover:border-[#008E98]/40 transition-all flex flex-col justify-between space-y-4 {{ in_array($batch->risk_classification, ['high_risk', 'critical_risk']) ? 'border-[#FECACA] bg-[#FFF8F8]' : '' }}">
            
            <!-- Card Header: Title, Code & Overall Risk Badge -->
            <div>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <a href="{{ route('admin.weather.show', $batch) }}" class="font-extrabold text-[#1D1D1F] hover:text-[#780000] text-base block leading-tight">
                            {{ $batch->batch_number }}
                        </a>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border block {{ $batch->risk_badge['class'] }}">
                            {{ $batch->risk_badge['label'] }}
                        </span>
                        @if($batch->status === 'cancelled_by_camp')
                            <span class="text-[11px] text-[#DC2626] font-bold block mt-0.5">Cancelled</span>
                        @endif
                    </div>
                </div>

                <!-- 2-Day AM & PM Conditions Grid -->
                <div class="mt-3.5 grid grid-cols-2 gap-2.5">
                    
                    <!-- Day 1 Assessment -->
                    <div class="p-3 rounded-xl bg-white border border-[#E5E5EA] space-y-1.5">
                        @if($day1)
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold border inline-block {{ $day1->classification_badge['class'] }}">
                                {{ $day1->overall_classification }}
                            </span>
                            <div class="text-[11px] text-[#6E6E73]">
                                Worst: <strong class="text-[#1D1D1F]">{{ $day1->worst_hour ? $day1->worst_hour->format('g:i A') : 'N/A' }}</strong>
                            </div>
                        @else
                            <span class="text-xs text-[#8E8E93] italic">Not Assessed</span>
                        @endif
                        <div class="text-[11px] font-bold text-[#6E6E73]">
                            Day 1 ({{ $batch->start_date->format('M d') }})
                        </div>
                    </div>

                    <!-- Day 2 Assessment -->
                    <div class="p-3 rounded-xl bg-white border border-[#E5E5EA] space-y-1.5">
                        @if($day2)
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold border inline-block {{ $day2->classification_badge['class'] }}">
                                {{ $day2->overall_classification }}
                            </span>
                            <div class="text-[11px] text-[#6E6E73]">
                                Worst: <strong class="text-[#1D1D1F]">{{ $day2->worst_hour ? $day2->worst_hour->format('g:i A') : 'N/A' }}</strong>
                            </div>
                        @else
                            <span class="text-xs text-[#8E8E93] italic">Not Assessed</span>
                        @endif
                        <div class="text-[11px] font-bold text-[#6E6E73]">
                            Day 2 ({{ $batch->end_date->format('M d') }})
                        </div>
                    </div>

                </div>

                <!-- Override Badge (if present) -->
                @if($override)
                    <div class="mt-3 p-2.5 rounded-xl bg-[#FFFBEB] border border-[#FDE68A] text-xs text-[#92400E]">
                        <span class="font-bold">Manual Override:</span> {{ ucfirst($override->manual_classification) }}
                        <span class="text-[11px] text-[#A16207] block mt-0.5">{{ Str::limit($override->reason, 45) }}</span>
                    </div>
                @endif
            </div>

            <!-- Card Footer: Assess & Telemetry Button -->
            <div class="pt-2">
                <a href="{{ route('admin.weather.show', $batch) }}" 
                   class="w-full py-2.5 px-4 rounded-xl font-bold text-xs text-center flex items-center justify-center gap-1.5 whitespace-nowrap btn-secondary hover:bg-[#F2F2F7] transition-all">
                    <span>Assess & View</span>
                </a>
            </div>

        </div>
        @empty
        <div class="col-span-full py-12 text-center text-[#6E6E73] bg-white rounded-2xl border border-[#E5E5EA]">
            <p class="text-base font-bold text-[#1D1D1F]">No batches found for weather monitoring</p>
            <p class="text-xs text-[#6E6E73] mt-1">Adjust your filters or verify scheduled batches.</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pt-2">
        {{ $batches->links() }}
    </div>

</div>
@endsection
