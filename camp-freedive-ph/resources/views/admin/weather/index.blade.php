@extends('layouts.admin')

@section('title', 'Weather & Marine Safety Monitoring | Camp FreedivePH')

@section('content')
<div class="space-y-6 text-sm" x-data="{
    activeTab: '{{ request('tab', 'batches') }}',
    selectedForecastDate: '{{ isset($masterForecast['daily_summaries']) && !empty($masterForecast['daily_summaries']) ? array_key_first($masterForecast['daily_summaries']) : now()->format('Y-m-d') }}'
}">
    
    <!-- Top Header & Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight">Weather & Marine Safety</h1>
            <p class="text-sm sm:text-sm text-[#6E6E73] mt-1">
                Real-time sea conditions, dive batch safety statuses, and ML predictive monitoring for Mabini, Batangas.
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <!-- ML Service Status Indicator -->
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg border {{ $isMLReachable ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-[#F2F2F7] text-[#6E6E73] border-[#E5E5EA]' }} text-sm font-bold">
                <span class="w-2 h-2 rounded-full {{ $isMLReachable ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400' }}"></span>
                <span>{{ $isMLReachable ? 'ML Microservice Online' : 'ML Microservice Standby' }}</span>
            </div>

            <!-- Sync Forecast Cache Button -->
            <form action="{{ route('admin.weather.sync_cache') }}" method="POST">
                @csrf
                <button type="submit" class="btn-primary px-3.5 py-2 text-sm font-bold flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                    <span>Sync 16-Day Forecast</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Safety Monitoring Tab Navigation -->
    <div class="border-b border-[#E5E5EA] flex items-center gap-6 overflow-x-auto no-scrollbar">
        <button type="button" 
                @click="activeTab = 'batches'"
                class="pb-3 text-sm sm:text-sm transition-all border-b-2 whitespace-nowrap cursor-pointer"
                :class="activeTab === 'batches' ? 'border-[#780000] text-[#780000] font-bold' : 'border-transparent text-[#6E6E73] hover:text-[#1D1D1F] hover:border-[#D1D1D6] font-semibold'">
            Batch Operations & Roster
        </button>

        <button type="button" 
                @click="activeTab = 'ml_model'"
                class="pb-3 text-sm sm:text-sm transition-all border-b-2 whitespace-nowrap cursor-pointer"
                :class="activeTab === 'ml_model' ? 'border-[#780000] text-[#780000] font-bold' : 'border-transparent text-[#6E6E73] hover:text-[#1D1D1F] hover:border-[#D1D1D6] font-semibold'">
            ML Safety Model
        </button>
    </div>

    <!-- ==================================================================== -->
    <!-- TAB 1: BATCH OPERATIONS & ROSTER (ORIGINAL VIEW) -->
    <!-- ==================================================================== -->
    <div x-show="activeTab === 'batches'" class="space-y-6" x-cloak>
        
        <!-- 16-Day Forecast Horizon -->
        @if(isset($masterForecast['daily_summaries']) && !empty($masterForecast['daily_summaries']))
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold uppercase tracking-wider text-[#6E6E73]">16-Day Marine Horizon Radar</h3>
                <span class="text-sm text-[#8E8E93]">Scroll horizontally</span>
            </div>

            <div class="flex items-center gap-2.5 overflow-x-auto pb-2 scrollbar-thin">
                @foreach($masterForecast['daily_summaries'] as $dateStr => $daySummary)
                @php
                    $dayCarbon = \Carbon\Carbon::parse($dateStr);
                    $isToday = $dayCarbon->isToday();
                    $riskColor = match($daySummary['overall_classification'] ?? 'Safe') {
                        'Very Safe', 'Safe' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                        'Moderate' => 'border-amber-200 bg-amber-50 text-amber-900',
                        'High Risk', 'Critical Risk' => 'border-rose-200 bg-rose-50 text-rose-900',
                        default => 'border-[#E5E5EA] bg-white text-[#1D1D1F]',
                    };
                @endphp
                <div @click="activeTab = 'ml_model'; selectedForecastDate = '{{ $dateStr }}'"
                     class="min-w-[140px] p-3 rounded-xl border {{ $riskColor }} text-center shrink-0 space-y-1 cursor-pointer hover:shadow-sm transition-all">
                    <div class="text-sm font-bold text-[#1D1D1F] flex items-center justify-center gap-1">
                        <span>{{ $dayCarbon->format('D, M d') }}</span>
                        @if($isToday)
                            <span class="text-sm px-1 py-0.2 rounded bg-[#780000] text-white font-extrabold uppercase">Today</span>
                        @endif
                    </div>
                    <div class="text-sm font-extrabold uppercase tracking-wider">
                        {{ $daySummary['overall_classification'] ?? 'Safe' }}
                    </div>
                    <div class="text-sm text-[#6E6E73] font-medium flex justify-between pt-0.5 px-1 border-t border-black/5">
                        <span>{{ number_format($daySummary['avg_wave_height'] ?? 0.70, 2) }}m</span>
                        <span>{{ round($daySummary['avg_wind_speed'] ?? 12) }} km/h</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Critical / High Risk Advisory Banner -->
        @if($criticalCount > 0)
        <div class="p-4 bg-rose-50 rounded-xl border border-rose-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-sm text-rose-900">
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-rose-600 animate-pulse"></span>
                <div>
                    <strong>{{ $criticalCount }} upcoming batch(es)</strong> are currently classified as <strong>High Risk</strong> or <strong>Critical Risk</strong>. Review conditions immediately to confirm go/no-go or initiate cancellation flows.
                </div>
            </div>
            <span class="font-bold uppercase tracking-wider text-sm text-[#780000] shrink-0">Safety Advisory</span>
        </div>
        @endif

        <!-- Risk Filters and Toolbar -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-2.5 sm:p-3 shadow-2xs">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2.5 sm:gap-3">
                
                <!-- Risk Level Filters -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 scrollbar-none -mx-0.5 px-0.5">
                    <a href="{{ request()->fullUrlWithQuery(['risk' => '', 'tab' => 'batches']) }}" 
                       class="px-3 py-1.5 rounded-lg text-sm font-bold transition-all shrink-0 {{ !request('risk') ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                        All Risks
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['risk' => 'very_safe', 'tab' => 'batches']) }}" 
                       class="px-3 py-1.5 rounded-lg text-sm font-bold transition-all shrink-0 {{ request('risk') === 'very_safe' ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                        Very Safe
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['risk' => 'safe', 'tab' => 'batches']) }}" 
                       class="px-3 py-1.5 rounded-lg text-sm font-bold transition-all shrink-0 {{ request('risk') === 'safe' ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                        Safe
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['risk' => 'moderate', 'tab' => 'batches']) }}" 
                       class="px-3 py-1.5 rounded-lg text-sm font-bold transition-all shrink-0 {{ request('risk') === 'moderate' ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                        Moderate
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['risk' => 'high_risk', 'tab' => 'batches']) }}" 
                       class="px-3 py-1.5 rounded-lg text-sm font-bold transition-all shrink-0 {{ request('risk') === 'high_risk' ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                        High Risk
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['risk' => 'critical_risk', 'tab' => 'batches']) }}" 
                       class="px-3 py-1.5 rounded-lg text-sm font-bold transition-all shrink-0 {{ request('risk') === 'critical_risk' ? 'bg-[#780000] text-white shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                        Critical
                    </a>
                </div>

                <!-- Advanced Filters -->
                <div class="flex items-center gap-2 self-end md:self-auto shrink-0" x-data="{ openFilters: false }">
                    @if(request()->anyFilled(['status', 'date_from', 'date_to']))
                        <a href="{{ route('admin.weather.index', ['risk' => request('risk'), 'tab' => 'batches']) }}" 
                           class="text-sm text-[#6E6E73] hover:text-[#780000] underline font-medium px-2 py-1">
                            Clear Extras
                        </a>
                    @endif

                    <div class="relative shrink-0">
                        <button type="button" 
                                @click="openFilters = !openFilters" 
                                class="btn-secondary flex items-center justify-center gap-1.5 px-3 py-1.5 text-sm font-semibold whitespace-nowrap shrink-0 cursor-pointer">
                            <img src="{{ asset('icons/icons8-filter-60.png') }}" alt="Filter" class="w-4.5 h-4.5 object-contain inline-block shrink-0">
                            <span class="whitespace-nowrap">Filter</span>
                            @if(request()->anyFilled(['status', 'date_from', 'date_to']))
                                <span class="w-2 h-2 rounded-full bg-[#780000] shrink-0"></span>
                            @endif
                        </button>

                        <!-- Filter Dropdown -->
                        <div x-show="openFilters" 
                             @click.outside="openFilters = false" 
                             x-cloak 
                             class="absolute right-0 mt-2 w-[calc(100vw-2rem)] sm:w-80 max-w-sm bg-white rounded-xl border border-[#E5E5EA] shadow-xl p-4 z-50 space-y-3">
                            <form action="{{ route('admin.weather.index') }}" method="GET" class="space-y-3 text-sm">
                                <input type="hidden" name="tab" value="batches">
                                <input type="hidden" name="risk" value="{{ request('risk') }}">

                                <div>
                                    <label class="block font-semibold text-[#6E6E73] mb-1">Batch Status</label>
                                    <select name="status" class="w-full px-2.5 py-1.5 rounded-lg border border-[#D1D1D6] text-sm">
                                        <option value="">All Statuses</option>
                                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="rescheduled" {{ request('status') === 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                                        <option value="cancelled_by_camp" {{ request('status') === 'cancelled_by_camp' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>

                                <div class="pt-2 border-t border-[#E5E5EA] flex justify-end">
                                    <button type="submit" class="btn-primary w-full py-1.5 text-sm font-bold">
                                        Apply Filter
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Weekend Batches Forecast List -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
            @forelse($batches as $batch)
            @php
                $day1 = $batch->riskAssessments->where('day_number', 1)->first() ?? $batch->riskAssessments->filter(fn($a) => $a->dive_date?->toDateString() === $batch->start_date?->toDateString())->first();
                $day2 = $batch->riskAssessments->where('day_number', 2)->first() ?? $batch->riskAssessments->filter(fn($a) => $a->dive_date?->toDateString() === $batch->end_date?->toDateString())->first();
                $override = $batch->manualOverrides->first();
            @endphp
            
            <div onclick="window.location='{{ route('admin.weather.show', ['batch' => $batch, 'profile' => 'operations']) }}'" 
                 class="rounded-xl border border-[#E5E5EA] p-4 sm:p-5 hover:border-[#780000] cursor-pointer transition-all flex flex-col justify-between space-y-3 shadow-2xs group bg-white">
                
                <!-- Batch Information -->
                <div>
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="font-extrabold text-[#1D1D1F] group-hover:text-[#780000] text-base block leading-tight transition-colors">
                                {{ $batch->batch_number }}
                            </span>
                            <div class="text-sm text-[#6E6E73] mt-0.5 font-medium">
                                {{ $batch->start_date->format('M d') }} to {{ $batch->end_date->format('M d, Y') }}
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="px-2 py-0.5 rounded-md text-sm font-bold block {{ $batch->risk_badge['class'] }}">
                                {{ $batch->risk_badge['label'] }}
                            </span>
                            @if($batch->status === 'cancelled_by_camp')
                                <span class="text-sm text-rose-700 font-bold block mt-0.5">Cancelled</span>
                            @endif
                        </div>
                    </div>

                    <!-- Day 1 & Day 2 Forecast Conditions -->
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        
                        <!-- Day 1 Assessment -->
                        <div class="p-2.5 rounded-lg border border-[#E5E5EA] bg-[#F2F2F7] space-y-1">
                            <div class="text-sm font-bold text-[#6E6E73]">
                                Day 1 ({{ $batch->start_date->format('M d') }})
                            </div>
                            @if($day1)
                                <div class="text-sm text-[#6E6E73]">
                                    Worst: <strong class="text-[#1D1D1F]">{{ $day1->worst_hour ? $day1->worst_hour->format('g:i A') : 'N/A' }}</strong>
                                </div>
                            @else
                                <span class="text-sm text-[#8E8E93] italic">Not Assessed</span>
                            @endif
                        </div>

                        <!-- Day 2 Assessment -->
                        <div class="p-2.5 rounded-lg border border-[#E5E5EA] bg-[#F2F2F7] space-y-1">
                            <div class="text-sm font-bold text-[#6E6E73]">
                                Day 2 ({{ $batch->end_date->format('M d') }})
                            </div>
                            @if($day2)
                                <div class="text-sm text-[#6E6E73]">
                                    Worst: <strong class="text-[#1D1D1F]">{{ $day2->worst_hour ? $day2->worst_hour->format('g:i A') : 'N/A' }}</strong>
                                </div>
                            @else
                                <span class="text-sm text-[#8E8E93] italic">Not Assessed</span>
                            @endif
                        </div>

                    </div>

                    <!-- Manual Override Notice -->
                    @if($override)
                        <div class="mt-2.5 p-2 rounded-lg bg-amber-50 border border-amber-200 text-sm text-amber-900">
                            <span class="font-bold">Manual Override:</span> {{ !empty($override->active_advisories) ? implode(', ', $override->active_advisories) : 'Advisory Active' }}
                            <span class="text-sm text-amber-800 block mt-0.5">{{ Str::limit($override->reason, 60) }}</span>
                        </div>
                    @endif
                </div>

            </div>
            @empty
            <div class="col-span-full py-10 text-center text-[#6E6E73] bg-white rounded-xl border border-[#E5E5EA]">
                <p class="text-base font-bold text-[#1D1D1F]">No batches found for weather monitoring</p>
                <p class="text-sm text-[#6E6E73] mt-1">Adjust your filters or verify scheduled batches.</p>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="bg-[#F2F2F7] rounded-xl border border-[#E5E5EA] overflow-hidden [&>*]:border-t-0">
            {{ $batches->links() }}
        </div>

    </div>

    <!-- ==================================================================== -->
    <!-- TAB 2: ML SAFETY MODEL (DEDICATED ML INTELLIGENCE TAB) -->
    <!-- ==================================================================== -->
    <div x-show="activeTab === 'ml_model'" class="space-y-6" x-cloak>

        <!-- Active Batches ML Safety Verdicts Table -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-5 sm:p-6 space-y-4 shadow-2xs">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-[#1D1D1F]">Batch ML Safety Verdicts & Recommendations</h3>
                    <p class="text-sm text-[#6E6E73]">Live machine learning risk evaluations across active dive batches.</p>
                </div>
                <span class="text-sm font-bold text-[#6E6E73]">5-Tier Standard Output</span>
            </div>

            <div class="overflow-x-auto rounded-xl border border-[#E5E5EA]">
                <table class="w-full text-left text-sm">
                    <thead class="bg-[#F2F2F7] border-b border-[#E5E5EA] text-sm uppercase font-extrabold text-[#6E6E73]">
                        <tr>
                            <th class="py-3 px-3">Batch Number</th>
                            <th class="py-3 px-3">Dates</th>
                            <th class="py-3 px-3">ML Recommendation</th>
                            <th class="py-3 px-3">Operational Horizon Status</th>
                            <th class="py-3 px-3">Day 1 ML Tier</th>
                            <th class="py-3 px-3">Day 2 ML Tier</th>
                            <th class="py-3 px-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E5EA] bg-white">
                        @forelse($batches as $b)
                        @php
                            $ml = $batchMLAssessments[$b->id] ?? null;
                            $mlRec = $ml['overall_recommendation'] ?? ($b->risk_badge['label'] ?? 'Safe');
                            $mlBadgeClass = match($mlRec) {
                                'Very Safe', 'Safe' => 'bg-emerald-600 text-white',
                                'Moderate' => 'bg-amber-500 text-white',
                                'High Risk' => 'bg-rose-600 text-white',
                                'Critical Risk' => 'bg-red-700 text-white',
                                default => 'bg-gray-600 text-white',
                            };
                            $opStatus = $ml['operational_status'] ?? 'PROVISIONAL_TREND_OUTLOOK';
                            $opLabel = $ml['operational_status_label'] ?? 'Provisional Trend Outlook (6h-24h)';
                            $opBadgeClass = match($opStatus) {
                                'TACTICAL_CLEARANCE' => 'bg-emerald-50 text-emerald-700',
                                'PROVISIONAL_TREND_OUTLOOK' => 'bg-blue-50 text-blue-700',
                                'EXTENDED_TREND_OUTLOOK' => 'bg-purple-50 text-purple-700',
                                default => 'bg-gray-50 text-gray-700',
                            };
                        @endphp
                        <tr onclick="window.location='{{ route('admin.weather.show', ['batch' => $b, 'profile' => 'ml_model']) }}'" class="hover:bg-[#F2F2F7] cursor-pointer transition-colors group">
                            <td class="py-3 px-3 font-extrabold text-[#1D1D1F] group-hover:text-[#780000]">
                                {{ $b->batch_number }}
                            </td>
                            <td class="py-3 px-3 text-[#6E6E73] font-medium">
                                {{ $b->start_date->format('M d') }} to {{ $b->end_date->format('M d, Y') }}
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2.5 py-0.5 rounded-full text-sm font-black uppercase {{ $mlBadgeClass }}">
                                    {{ $mlRec }}
                                </span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2.5 py-0.5 rounded-md text-sm font-bold inline-block {{ $opBadgeClass }}">
                                    {{ $opLabel }}
                                </span>
                            </td>
                            <td class="py-3 px-3 font-medium text-[#6E6E73]">
                                {{ $ml['day1']['overall_recommendation'] ?? 'Safe' }}
                            </td>
                            <td class="py-3 px-3 font-medium text-[#6E6E73]">
                                {{ $ml['day2']['overall_recommendation'] ?? 'Safe' }}
                            </td>
                            <td class="py-3 px-3 text-right">
                                <span class="text-sm font-bold text-[#780000] group-hover:underline">
                                    Deep-Dive
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-[#6E6E73]">No batches available.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 16-Day Marine Forecast Radar Explorer -->
        @if(isset($masterForecast['daily_summaries']) && !empty($masterForecast['daily_summaries']))
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-5 sm:p-6 space-y-4 shadow-2xs">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-[#1D1D1F]">16-Day Marine Forecast Explorer</h3>
                    <p class="text-sm text-[#6E6E73]">Select a date to inspect multi-variable marine conditions and daytime operational windows.</p>
                </div>
                <span class="text-sm text-[#8E8E93]">Click any date card</span>
            </div>

            <!-- Date Selector Chips -->
            <div class="flex items-center gap-3 overflow-x-auto pb-3 scrollbar-thin">
                @foreach($masterForecast['daily_summaries'] as $dateStr => $daySummary)
                @php
                    $dayCarbon = \Carbon\Carbon::parse($dateStr);
                    $riskColor = match($daySummary['overall_classification'] ?? 'Safe') {
                        'Very Safe', 'Safe' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                        'Moderate' => 'border-amber-200 bg-amber-50 text-amber-900',
                        'High Risk' => 'border-rose-200 bg-rose-50 text-rose-900',
                        'Critical Risk' => 'border-red-200 bg-red-50 text-red-900',
                        default => 'border-[#E5E5EA] bg-white text-[#1D1D1F]',
                    };
                @endphp
                <button type="button" 
                        @click="selectedForecastDate = '{{ $dateStr }}'"
                        class="min-w-[145px] p-3 rounded-xl border text-left shrink-0 transition-all cursor-pointer space-y-1"
                        :class="selectedForecastDate === '{{ $dateStr }}' ? 'ring-2 ring-[#780000] shadow-md {{ $riskColor }}' : 'hover:border-[#780000] bg-white border-[#E5E5EA]'">
                    <div class="text-sm font-bold text-[#1D1D1F]">{{ $dayCarbon->format('D, M d') }}</div>
                    <div class="text-sm font-black uppercase">{{ $daySummary['overall_classification'] ?? 'Safe' }}</div>
                    <div class="text-sm text-[#6E6E73] font-medium flex justify-between pt-0.5">
                        <span>Wave: {{ $daySummary['avg_wave_height'] ?? '0.70' }}m</span>
                        <span>Wind: {{ $daySummary['avg_wind_speed'] ?? '12' }}km/h</span>
                    </div>
                </button>
                @endforeach
            </div>

            <!-- Selected Date 24-Hour Breakdown -->
            @foreach($masterForecast['daily_summaries'] as $dateStr => $daySummary)
            <div x-show="selectedForecastDate === '{{ $dateStr }}'" class="pt-3 space-y-3" x-cloak>
                <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-2">
                    <span class="text-sm font-extrabold text-[#1D1D1F] uppercase tracking-wider">
                        {{ \Carbon\Carbon::parse($dateStr)->format('l, F d, Y') }}: Hourly Marine & Weather Conditions
                    </span>
                    <span class="text-sm font-bold text-[#6E6E73]">24 Hours Continuous</span>
                </div>

                <div class="overflow-x-auto rounded-xl border border-[#E5E5EA]">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[#F2F2F7] border-b border-[#E5E5EA] text-sm uppercase font-extrabold text-[#6E6E73]">
                            <tr>
                                <th class="py-3 px-3 whitespace-nowrap">Forecast Hour</th>
                                <th class="py-3 px-2 whitespace-nowrap">Risk Rating</th>
                                <th class="py-3 px-2 whitespace-nowrap">Wave Height (Hs)</th>
                                <th class="py-3 px-2 whitespace-nowrap">Wave Period (Tp)</th>
                                <th class="py-3 px-2 whitespace-nowrap">Swell Height</th>
                                <th class="py-3 px-2 whitespace-nowrap">Ocean Current</th>
                                <th class="py-3 px-2 whitespace-nowrap">Wind Wave</th>
                                <th class="py-3 px-2 whitespace-nowrap">Rain (mm)</th>
                                <th class="py-3 px-2 whitespace-nowrap">Pressure (hPa)</th>
                                <th class="py-3 px-3 whitespace-nowrap">Wind Speed & Dir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5E5EA] bg-white">
                            @foreach($daySummary['hourly'] ?? [] as $h)
                            @php
                                $isAmHour = in_array($h['hour'], [10, 11, 12]);
                                $isPmHour = in_array($h['hour'], [16, 17]);
                                $badgeClass = match($h['classification'] ?? 'Safe') {
                                    'Very Safe', 'Safe' => 'bg-emerald-50 text-emerald-700',
                                    'Moderate' => 'bg-amber-50 text-amber-700',
                                    'High Risk' => 'bg-rose-50 text-rose-700',
                                    'Critical Risk' => 'bg-red-50 text-red-700',
                                    default => 'bg-gray-50 text-gray-700',
                                };
                            @endphp
                            <tr class="hover:bg-[#F2F2F7] transition-colors {{ ($isAmHour || $isPmHour) ? 'bg-[#F8EAEA]/20 font-semibold' : '' }}">
                                <td class="py-2.5 px-3 whitespace-nowrap font-mono text-[#1D1D1F]">
                                    <div class="flex items-center gap-1.5">
                                        <span>{{ sprintf('%02d:00', $h['hour']) }}</span>
                                        @if($isAmHour)
                                            <span class="text-sm px-1.5 py-0.2 rounded bg-[#780000] text-white font-extrabold uppercase">AM Window</span>
                                        @elseif($isPmHour)
                                            <span class="text-sm px-1.5 py-0.2 rounded bg-[#780000] text-white font-extrabold uppercase">PM Window</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-2.5 px-2 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-full text-sm font-bold {{ $badgeClass }}">
                                        {{ $h['classification'] }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-2 whitespace-nowrap font-medium text-[#1D1D1F]">{{ number_format($h['wave_height'] ?? 0.7, 2) }} m</td>
                                <td class="py-2.5 px-2 whitespace-nowrap font-medium text-[#1D1D1F]">{{ number_format($h['wave_period'] ?? 6.1, 1) }} s</td>
                                <td class="py-2.5 px-2 whitespace-nowrap font-medium text-[#1D1D1F]">{{ number_format($h['swell_height'] ?? 0.6, 2) }} m</td>
                                <td class="py-2.5 px-2 whitespace-nowrap font-medium text-[#1D1D1F]">{{ number_format($h['ocean_current'] ?? 0.3, 2) }} m/s</td>
                                <td class="py-2.5 px-2 whitespace-nowrap font-medium text-[#1D1D1F]">{{ number_format($h['wind_wave_height'] ?? 0.35, 2) }} m</td>
                                <td class="py-2.5 px-2 whitespace-nowrap font-medium text-[#1D1D1F]">{{ number_format($h['rain'] ?? 0.0, 1) }} mm</td>
                                <td class="py-2.5 px-2 whitespace-nowrap font-medium text-[#1D1D1F]">{{ number_format($h['sea_level_pressure'] ?? 1010.5, 0) }} hPa</td>
                                <td class="py-2.5 px-3 whitespace-nowrap font-medium text-[#1D1D1F]">{{ round($h['wind_speed'] ?? 12.0) }} km/h ({{ round($h['wind_direction'] ?? 245) }}°)</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- 5 Official Safety Classifications Standard -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 space-y-4">
            <h3 class="text-base font-extrabold text-[#1D1D1F]">5-Tier Safety Classification Standard</h3>
            <div class="overflow-x-auto rounded-xl border border-[#E5E5EA]">
                <table class="w-full text-left text-sm">
                    <thead class="bg-[#F2F2F7] border-b border-[#E5E5EA] text-sm uppercase font-extrabold text-[#6E6E73]">
                        <tr>
                            <th class="py-3 px-3">Classification</th>
                            <th class="py-3 px-3">Risk Slug</th>
                            <th class="py-3 px-4">Standard Meaning & Operational Policy</th>
                            <th class="py-3 px-3">Operational Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E5EA] bg-white">
                        <tr>
                            <td class="py-3 px-3">
                                <span class="px-2.5 py-0.5 rounded-full text-sm font-black uppercase bg-emerald-600 text-white">Very Safe</span>
                            </td>
                            <td class="py-3 px-3 font-mono text-[#6E6E73]">very_safe</td>
                            <td class="py-3 px-4 text-[#1D1D1F] font-medium">Optimal freediving conditions. Minimal environmental hazards. Authoritative Go for all diver experience levels.</td>
                            <td class="py-3 px-3 text-emerald-700 font-bold">Tactical Clearance ($H \le 1\text{h}$)</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-3">
                                <span class="px-2.5 py-0.5 rounded-full text-sm font-black uppercase bg-emerald-600 text-white">Safe</span>
                            </td>
                            <td class="py-3 px-3 font-mono text-[#6E6E73]">safe</td>
                            <td class="py-3 px-4 text-[#1D1D1F] font-medium">Generally safe conditions. Standard camp safety protocols and buoy monitoring followed.</td>
                            <td class="py-3 px-3 text-emerald-700 font-bold">Tactical Clearance ($H \le 1\text{h}$)</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-3">
                                <span class="px-2.5 py-0.5 rounded-full text-sm font-black uppercase bg-amber-500 text-white">Moderate</span>
                            </td>
                            <td class="py-3 px-3 font-mono text-[#6E6E73]">moderate</td>
                            <td class="py-3 px-4 text-[#1D1D1F] font-medium">Some elevated chop, currents, or shifting winds. Increased coach monitoring and potential depth adjustments required.</td>
                            <td class="py-3 px-3 text-amber-700 font-bold">Caution / Advanced</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-3">
                                <span class="px-2.5 py-0.5 rounded-full text-sm font-black uppercase bg-rose-600 text-white">High Risk</span>
                            </td>
                            <td class="py-3 px-3 font-mono text-[#6E6E73]">high_risk</td>
                            <td class="py-3 px-4 text-[#1D1D1F] font-medium">Significant hazards present that could compromise diver safety. No-Go recommendation for open water operations.</td>
                            <td class="py-3 px-3 text-rose-700 font-bold">High Risk No-Go</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-3">
                                <span class="px-2.5 py-0.5 rounded-full text-sm font-black uppercase bg-red-700 text-white">Critical Risk</span>
                            </td>
                            <td class="py-3 px-3 font-mono text-[#6E6E73]">critical_risk</td>
                            <td class="py-3 px-4 text-[#1D1D1F] font-medium">Severe weather or sea state breach. Immediate cancellation and 100% force majeure refund automatically initiated.</td>
                            <td class="py-3 px-3 text-red-700 font-bold">Mandatory Safety Limit</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Philippine Coast Guard & Mandatory Safety Limits -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 space-y-4">
            <h3 class="text-base font-extrabold text-[#1D1D1F]">Anilao Marine Mandatory Safety Limits</h3>
            <p class="text-sm text-[#6E6E73]">
                Any single physical parameter breach immediately forces <strong>Critical Risk</strong>, overriding model predictions.
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
                <div class="p-3.5 rounded-xl border border-[#E5E5EA] bg-[#F2F2F7] space-y-1">
                    <span class="text-[#6E6E73] font-bold block uppercase text-sm">Sustained Wind Speed</span>
                    <strong class="text-base font-black text-[#1D1D1F]">&ge; 42.0 km/h</strong>
                    <span class="text-sm text-[#8E8E93] block">PCG Banca / Small Craft Limit</span>
                </div>
                <div class="p-3.5 rounded-xl border border-[#E5E5EA] bg-[#F2F2F7] space-y-1">
                    <span class="text-[#6E6E73] font-bold block uppercase text-sm">Squall Wind Gusts</span>
                    <strong class="text-base font-black text-[#1D1D1F]">&ge; 48.0 km/h</strong>
                    <span class="text-sm text-[#8E8E93] block">Instantaneous squall threshold</span>
                </div>
                <div class="p-3.5 rounded-xl border border-[#E5E5EA] bg-[#F2F2F7] space-y-1">
                    <span class="text-[#6E6E73] font-bold block uppercase text-sm">Significant Wave Height ($H_s$)</span>
                    <strong class="text-base font-black text-[#1D1D1F]">&ge; 1.80 m</strong>
                    <span class="text-sm text-[#8E8E93] block">30-min rolling mean limit</span>
                </div>
                <div class="p-3.5 rounded-xl border border-[#E5E5EA] bg-[#F2F2F7] space-y-1">
                    <span class="text-[#6E6E73] font-bold block uppercase text-sm">Ocean Current Velocity</span>
                    <strong class="text-base font-black text-[#1D1D1F]">&ge; 0.80 m/s</strong>
                    <span class="text-sm text-[#8E8E93] block">Line drift hazard threshold</span>
                </div>
            </div>
        </div>

        <!-- Marine Data Sources & Attribution -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 space-y-4">
            <div class="flex items-center justify-between pb-3">
                <div>
                    <h3 class="text-base font-extrabold text-[#1D1D1F]">Meteorological Data Sources & Attribution</h3>
                    <p class="text-sm text-[#6E6E73]">Multi-agency numerical weather predictions and marine assimilation feeds for Anilao / Mabini.</p>
                </div>
                <span class="px-2.5 py-1 rounded-md text-sm font-black uppercase bg-[#F2F2F7] text-[#1D1D1F] border border-[#E5E5EA]">
                    Open-Meteo High-Resolution Ensemble
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 text-sm">
                <div class="p-3.5 rounded-xl border border-[#E5E5EA] bg-[#F2F2F7] space-y-1.5">
                    <div class="flex items-center justify-between">
                        <strong class="font-bold text-[#1D1D1F]">ECMWF IFS / AIFS</strong>
                        <span class="text-sm px-1.5 py-0.2 rounded bg-blue-100 text-blue-800 font-extrabold uppercase">Europe</span>
                    </div>
                    <p class="text-sm text-[#6E6E73]">0.25° European Centre global atmospheric model. Gold standard for wind shear & pressure fields.</p>
                </div>

                <div class="p-3.5 rounded-xl border border-[#E5E5EA] bg-[#F2F2F7] space-y-1.5">
                    <div class="flex items-center justify-between">
                        <strong class="font-bold text-[#1D1D1F]">NOAA GFS & WaveWatch III</strong>
                        <span class="text-sm px-1.5 py-0.2 rounded bg-indigo-100 text-indigo-800 font-extrabold uppercase">USA</span>
                    </div>
                    <p class="text-sm text-[#6E6E73]">Global Forecast System 13km atmospheric model + global ocean wave dynamics and swell spectra.</p>
                </div>

                <div class="p-3.5 rounded-xl border border-[#E5E5EA] bg-[#F2F2F7] space-y-1.5">
                    <div class="flex items-center justify-between">
                        <strong class="font-bold text-[#1D1D1F]">Copernicus Marine (CMEMS)</strong>
                        <span class="text-sm px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800 font-extrabold uppercase">Mercator Ocean</span>
                    </div>
                    <p class="text-sm text-[#6E6E73]">0.083° global ocean current analysis ($U/V$ drift vectors), sea surface temperature, and tides.</p>
                </div>

                <div class="p-3.5 rounded-xl border border-[#E5E5EA] bg-[#F2F2F7] space-y-1.5">
                    <div class="flex items-center justify-between">
                        <strong class="font-bold text-[#1D1D1F]">PAGASA & JMA Himawari-9</strong>
                        <span class="text-sm px-1.5 py-0.2 rounded bg-amber-100 text-amber-800 font-extrabold uppercase">PH / Japan</span>
                    </div>
                    <p class="text-sm text-[#6E6E73]">Tropical Cyclone Wind Signals (TCWS), gale warnings, and geostationary satellite nowcasting.</p>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
