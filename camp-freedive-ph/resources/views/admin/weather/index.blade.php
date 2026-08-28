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
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-[#6E6E73]">aalisin ko rin for checking lang</h3>
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

    <!-- Modern Integrated Toolbar (Pill Tabs + Secondary Filter Popover) -->
    <div class="bg-white rounded-2xl border border-[#E5E5EA] p-3 sm:p-4 shadow-2xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            
            <!-- Left: Risk Level Pill Tabs (Primary: #780000) -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 scrollbar-none">
                <a href="{{ request()->fullUrlWithQuery(['risk' => '']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ !request('risk') ? 'bg-[#780000] text-white shadow-xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                    All Risks
                </a>
                <a href="{{ request()->fullUrlWithQuery(['risk' => 'very_safe']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ request('risk') === 'very_safe' ? 'bg-[#780000] text-white shadow-xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                    Very Safe
                </a>
                <a href="{{ request()->fullUrlWithQuery(['risk' => 'safe']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ request('risk') === 'safe' ? 'bg-[#780000] text-white shadow-xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                    Safe
                </a>
                <a href="{{ request()->fullUrlWithQuery(['risk' => 'moderate']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ request('risk') === 'moderate' ? 'bg-[#780000] text-white shadow-xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                    Moderate
                </a>
                <a href="{{ request()->fullUrlWithQuery(['risk' => 'high_risk']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ request('risk') === 'high_risk' ? 'bg-[#780000] text-white shadow-xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                    High Risk
                </a>
                <a href="{{ request()->fullUrlWithQuery(['risk' => 'critical_risk']) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 {{ request('risk') === 'critical_risk' ? 'bg-[#780000] text-white shadow-xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                    Critical
                </a>
            </div>

            <!-- Right: Secondary Filter Popover -->
            <div class="flex items-center gap-2 self-end md:self-auto shrink-0" x-data="{ openFilters: false }">
                @if(request()->anyFilled(['status', 'date_from', 'date_to']))
                    <a href="{{ route('admin.weather.index', ['risk' => request('risk')]) }}" 
                       class="text-xs text-[#6E6E73] hover:text-[#780000] underline font-medium px-2 py-1">
                        Clear Extras
                    </a>
                @endif

                <div class="relative">
                    <button type="button" 
                            @click="openFilters = !openFilters" 
                            class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-[#D1D1D6] bg-white hover:bg-[#FAFAFC] text-xs font-bold text-[#1D1D1F] transition-all shadow-2xs cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-[#6E6E73]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                        </svg>
                        <span>Filter</span>
                        @if(request()->anyFilled(['status', 'date_from', 'date_to']))
                            <span class="w-2 h-2 rounded-full bg-[#780000]"></span>
                        @endif
                    </button>

                    <!-- Filter Dropdown Card -->
                    <div x-show="openFilters" 
                         @click.outside="openFilters = false" 
                         x-cloak 
                         class="absolute right-0 mt-2 w-72 bg-white rounded-2xl border border-[#E5E5EA] shadow-xl p-4 z-50 space-y-3">
                        <form action="{{ route('admin.weather.index') }}" method="GET" class="space-y-3 text-xs">
                            <input type="hidden" name="risk" value="{{ request('risk') }}">

                            <div>
                                <label class="block font-bold text-[#1D1D1F] mb-2">Batch Status</label>
                                <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white font-medium">
                                    <option value="">All Statuses</option>
                                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed (Active)</option>
                                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="rescheduled" {{ request('status') === 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                                    <option value="cancelled_by_camp" {{ request('status') === 'cancelled_by_camp' ? 'selected' : '' }}>Cancelled by Camp</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-bold text-[#1D1D1F] mb-2">Dive Date From</label>
                                <input type="date" name="date_from" onchange="this.form.submit()" value="{{ request('date_from') }}" class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white font-medium">
                            </div>

                            <div class="flex items-center justify-between pt-2">
                                <a href="{{ route('admin.weather.index') }}" class="text-xs text-[#8E8E93] hover:text-[#1D1D1F]">Reset All</a>
                                <button type="button" @click="openFilters = false" class="btn-secondary px-3 py-1.5 text-xs font-semibold">Done</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Weather Safety Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($batches as $batch)
        @php
            $day1 = $batch->latestDay1Assessment;
            $day2 = $batch->latestDay2Assessment;
            $override = $batch->latestManualOverride;

            $day1Bg = match($day1?->overall_classification) {
                'Very Safe', 'Safe' => 'bg-emerald-50/70 border-emerald-200',
                'Moderate' => 'bg-amber-50/70 border-amber-200',
                'High Risk' => 'bg-rose-50/70 border-rose-200',
                'Critical Risk' => 'bg-red-100/70 border-red-300',
                default => 'bg-[#FAFAFC] border-[#E5E5EA]',
            };

            $day2Bg = match($day2?->overall_classification) {
                'Very Safe', 'Safe' => 'bg-emerald-50/70 border-emerald-200',
                'Moderate' => 'bg-amber-50/70 border-amber-200',
                'High Risk' => 'bg-rose-50/70 border-rose-200',
                'Critical Risk' => 'bg-red-100/70 border-red-300',
                default => 'bg-[#FAFAFC] border-[#E5E5EA]',
            };
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
                    <div class="p-3 rounded-xl border space-y-1.5 {{ $day1Bg }}">
                        <div class="text-[11px] font-bold text-[#6E6E73]">
                            Day 1 ({{ $batch->start_date->format('M d') }})
                        </div>
                        @if($day1)
                            <div class="text-[11px] text-[#6E6E73]">
                                Worst: <strong class="text-[#1D1D1F]">{{ $day1->worst_hour ? $day1->worst_hour->format('g:i A') : 'N/A' }}</strong>
                            </div>
                        @else
                            <span class="text-xs text-[#8E8E93] italic">Not Assessed</span>
                        @endif
                    </div>

                    <!-- Day 2 Assessment -->
                    <div class="p-3 rounded-xl border space-y-1.5 {{ $day2Bg }}">
                        <div class="text-[11px] font-bold text-[#6E6E73]">
                            Day 2 ({{ $batch->end_date->format('M d') }})
                        </div>
                        @if($day2)
                            <div class="text-[11px] text-[#6E6E73]">
                                Worst: <strong class="text-[#1D1D1F]">{{ $day2->worst_hour ? $day2->worst_hour->format('g:i A') : 'N/A' }}</strong>
                            </div>
                        @else
                            <span class="text-xs text-[#8E8E93] italic">Not Assessed</span>
                        @endif
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
