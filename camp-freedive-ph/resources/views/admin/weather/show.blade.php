@extends('layouts.admin')

@section('title', $batch->display_name . ' - Weather Risk Assessment | Camp FreedivePH')

@section('content')
<div class="space-y-6 text-sm" x-data="{
    openOverrideModal: false,
    openCancelModal: false,
    cancelReason: '{{ $overallClassification === 'Critical Risk' ? 'Critical Risk' : 'Elevated Marine Conditions (Moderate/High Risk)' }}'
}">
    
    <!-- Top Header Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-5">
        <div>
            <!-- Breadcrumbs -->
            <div class="flex items-center gap-2 mb-1.5">
                <a href="{{ route('admin.weather.index') }}" class="text-xs font-semibold text-[#6E6E73] hover:text-[#780000] transition-colors flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                    <span>Weather & Safety Roster</span>
                </a>
                @if($batch->status === 'cancelled_by_camp')
                    <span class="text-[#D1D1D6]">/</span>
                    <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-[#FEF2F2] text-[#DC2626] border border-[#FECACA]">
                        Cancelled by Camp
                    </span>
                @endif
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight">
                {{ $batch->batch_number }}
            </h1>
            
            <div class="mt-1.5 text-xs text-[#6E6E73] space-y-1">
                <div class="flex items-center gap-1 font-medium text-[#1D1D1F]">
                    <span>{{ $batch->start_date->format('F d, Y (l)') }} to {{ $batch->end_date->format('F d, Y (l)') }}</span>
                </div>
            </div>
        </div>

        <!-- Action Controls -->
        <div class="flex items-center gap-2.5 flex-wrap">
            
            <!-- Refresh / Run Live Assessment -->
            <form action="{{ route('admin.weather.assess', $batch) }}" method="POST">
                @csrf
                <button type="submit" class="btn-secondary px-3.5 py-2 text-xs sm:text-sm font-semibold flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-4 h-4 text-[#780000]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                    <span>Run Live Assessment</span>
                </button>
            </form>

            <!-- Manual Override Modal Trigger -->
            <button type="button" 
                    @click="openOverrideModal = true"
                    class="btn-secondary px-3.5 py-2 text-xs sm:text-sm font-semibold flex items-center gap-1.5">
                <span>Manual Override</span>
            </button>

            <!-- Risk-Based / Override Cancellation Trigger -->
            @if($batch->status !== 'cancelled_by_camp')
                <button type="button" 
                        @click="openCancelModal = true"
                        class="btn-danger px-3.5 py-2 text-xs sm:text-sm font-bold flex items-center gap-1.5">
                    <span>Cancel Batch</span>
                </button>
            @endif

        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 1. OVERALL BATCH HERO SUMMARY CARD -->
    <!-- ========================================================================= -->
    @php
        $overallConfig = match ($overallClassification) {
            'Very Safe' => [
                'border' => 'border-emerald-200',
                'bg' => 'bg-emerald-50/70',
                'text' => 'text-emerald-900',
                'pill' => 'bg-emerald-600 text-white',
                'dot' => 'bg-emerald-400',
            ],
            'Safe' => [
                'border' => 'border-emerald-200',
                'bg' => 'bg-emerald-50/70',
                'text' => 'text-emerald-900',
                'pill' => 'bg-emerald-600 text-white',
                'dot' => 'bg-emerald-400',
            ],
            'Moderate' => [
                'border' => 'border-amber-200',
                'bg' => 'bg-amber-50/70',
                'text' => 'text-amber-900',
                'pill' => 'bg-amber-500 text-white',
                'dot' => 'bg-amber-300',
            ],
            'High Risk' => [
                'border' => 'border-rose-200',
                'bg' => 'bg-rose-50/70',
                'text' => 'text-rose-900',
                'pill' => 'bg-rose-600 text-white',
                'dot' => 'bg-rose-400',
            ],
            'Critical Risk' => [
                'border' => 'border-red-300',
                'bg' => 'bg-red-50/80',
                'text' => 'text-red-900',
                'pill' => 'bg-red-700 text-white',
                'dot' => 'bg-red-400',
            ],
            default => [
                'border' => 'border-gray-200',
                'bg' => 'bg-gray-50',
                'text' => 'text-gray-900',
                'pill' => 'bg-gray-600 text-white',
                'dot' => 'bg-gray-400',
            ],
        };
    @endphp

    <div class="bg-white rounded-xl border {{ $overallConfig['border'] }} p-6 sm:p-7 shadow-sm space-y-4">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-5">
            <div>
                <span class="text-xs font-extrabold uppercase tracking-widest text-[#6E6E73] block mb-1">
                    Overall Batch Assessment
                </span>
                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-base font-black tracking-wide uppercase shadow-sm {{ $overallConfig['pill'] }}">
                        <span class="w-2.5 h-2.5 rounded-full {{ $overallConfig['dot'] }} animate-pulse"></span>
                        <span>{{ $overallClassification }}</span>
                    </span>
                </div>
            </div>

            <!-- Override Status Badge -->
            <div class="md:text-right">
                <span class="text-xs uppercase font-bold text-[#6E6E73] block">Override Advisory Status</span>
                @if($latestOverride && count($latestOverride->active_advisories) > 0)
                    <span class="text-xs font-bold text-[#991B1B] bg-[#FEF2F2] px-3 py-1.5 rounded-xl border border-[#FECACA] inline-flex items-center gap-1.5 mt-1">
                        <span>Active: {{ implode(', ', $latestOverride->active_advisories) }}</span>
                    </span>
                @else
                    <span class="text-xs font-bold text-[#065F46] bg-[#ECFDF5] px-3 py-1.5 rounded-xl border border-[#A7F3D0] inline-flex items-center gap-1.5 mt-1">
                        <span>✓</span>
                        <span>NOT OVERRIDDEN</span>
                    </span>
                @endif
            </div>
        </div>

        <!-- Action / Recommendation Text -->
        <div class="space-y-1.5">
            <div class="flex items-start gap-2">
                <div>
                    <h3 class="text-sm font-black text-[#1D1D1F]">
                        {{ \App\Services\WeatherForecastService::MEANING_MAP[$overallClassification] ?? 'Proceed with caution.' }}
                    </h3>
                </div>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- 2. DAY 1 & DAY 2 COMPARATIVE DASHBOARD (SIDE-BY-SIDE) -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- ===================================================================== -->
        <!-- DAY 1 SECTION -->
        <!-- ===================================================================== -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-5 sm:p-6 shadow-sm space-y-5">
            
            <!-- Day 1 Header -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="px-2.5 py-1 rounded-lg text-xs font-black bg-[#F8EAEA] text-[#780000] border border-[#F1D5D5]">
                            DAY 1
                        </span>
                        <h2 class="text-lg font-black text-[#1D1D1F]">
                            {{ $day1Assessment->dive_date->format('F d, Y (l)') }}
                        </h2>
                    </div>

                    <span class="px-3 py-1 rounded-full text-xs font-black uppercase border {{ $day1Assessment->classification_badge['class'] }}">
                        {{ $day1Assessment->overall_classification }}
                    </span>
                </div>

                <!-- Recommended Action after Day 1 Header -->
                <div class="p-3.5 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] text-xs text-[#1D1D1F] space-y-1">
                    <span class="font-bold text-[#6E6E73] block uppercase text-[11px] tracking-wider">Recommended Action:</span>
                    <p class="font-semibold text-[#1D1D1F] leading-snug">{{ $day1Assessment->recommended_action }}</p>
                </div>

                <!-- Day 1 Quick Stat Chips -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div class="p-3 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] flex items-center justify-between">
                        <div>
                            <span class="text-[#6E6E73] block text-xs uppercase font-bold">Worst Hour</span>
                            <strong class="text-sm font-extrabold text-[#1D1D1F]">
                                {{ $day1Assessment->worst_hour ? $day1Assessment->worst_hour->format('g:i A') : 'N/A' }}
                            </strong>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] flex items-center justify-between">
                        <div>
                            <span class="text-[#6E6E73] block text-xs uppercase font-bold">Forecast Horizon</span>
                            <strong class="text-sm font-extrabold text-[#1D1D1F]">
                                {{ round($day1Assessment->lead_time_hours) }}h before dive
                            </strong>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] space-y-1.5">
                        <div>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-extrabold border {{ $day1Assessment->reliability['badge_class'] }}">
                                    {{ $day1Assessment->reliability['label'] }}
                                </span>
                            </div>
                        </div>
                        <div class="text-[11px] text-[#6E6E73] font-medium leading-tight pt-1">
                            {{ $day1Assessment->reliability['description'] }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Day 1 Whole-Day 24-Hour Continuous Timeline with AM/PM Default & 24h Expansion -->
            @if(!empty($day1Continuous24h['hourly']))
            <div x-data="{ showAllHours: false }" class="pt-3 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">

                    <!-- Toggle Button -->
                    <button type="button" 
                            @click="showAllHours = !showAllHours" 
                            class="px-3.5 py-1.5 rounded-xl border border-[#D1D1D6] hover:border-[#00c3d0] bg-white hover:bg-[#FAFAFC] text-xs font-bold text-[#1D1D1F] flex items-center gap-1.5 transition-all shadow-2xs cursor-pointer">
                        <span x-text="showAllHours ? 'Collapse to AM & PM Windows' : 'Expand to All 24 Hours'"></span>
                        <svg class="w-3.5 h-3.5 transition-transform" :class="showAllHours ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                </div>

                <!-- 8-Variable Comprehensive Telemetry Table -->
                <div class="overflow-x-auto rounded-xl border border-[#E5E5EA] shadow-2xs">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#FAFAFC] border-b border-[#E5E5EA] text-[11px] uppercase font-extrabold text-[#6E6E73]">
                            <tr>
                                <th class="py-3 px-3 whitespace-nowrap">Forecast Time</th>
                                <th class="py-3 px-2 whitespace-nowrap">Risk Rating</th>
                                <th class="py-3 px-2 whitespace-nowrap" title="Significant Wave Height (m)">Wave Height (Hs)</th>
                                <th class="py-3 px-2 whitespace-nowrap" title="Peak Wave Period (sec)">Wave Period (Tp)</th>
                                <th class="py-3 px-2 whitespace-nowrap" title="Swell Wave Height (m)">Swell Height</th>
                                <th class="py-3 px-2 whitespace-nowrap" title="Ocean Current Velocity (m/s)">Ocean Current</th>
                                <th class="py-3 px-2 whitespace-nowrap" title="Wind Wave Height (m)">Wind Wave</th>
                                <th class="py-3 px-2 whitespace-nowrap" title="Precipitation / Rain (mm)">Rain (mm)</th>
                                <th class="py-3 px-2 whitespace-nowrap" title="Mean Sea Level Pressure (hPa)">Pressure (hPa)</th>
                                <th class="py-3 px-3 whitespace-nowrap" title="10m Wind Speed and Direction">Wind Speed & Dir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5E5EA] bg-white">
                            @foreach($day1Continuous24h['hourly'] as $h)
                            @php
                                $isAmHour = in_array($h['hour'], [10, 11, 12]);
                                $isPmHour = in_array($h['hour'], [16, 17]);
                                $badgeClass = match($h['classification'] ?? 'Safe') {
                                    'Very Safe' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'Safe' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'Moderate' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'High Risk' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'Critical Risk' => 'bg-red-50 text-red-700 border-red-200',
                                    default => 'bg-gray-50 text-gray-700 border-gray-200',
                                };
                            @endphp
                            <tr x-show="showAllHours || {{ ($isAmHour || $isPmHour) ? 'true' : 'false' }}" 
                                class="hover:bg-[#FAFAFC] transition-colors {{ ($isAmHour || $isPmHour) ? 'bg-[#F8EAEA]/25 font-semibold' : '' }}">
                                <td class="py-2.5 px-3 whitespace-nowrap font-mono text-[#1D1D1F]">
                                    <div class="flex items-center gap-1.5">
                                        <span>{{ sprintf('%02d:00', $h['hour']) }}</span>
                                        @if($isAmHour)
                                            <span class="text-[9px] px-1.5 py-0.2 rounded bg-[#780000] text-white font-extrabold uppercase">AM Window</span>
                                        @elseif($isPmHour)
                                            <span class="text-[9px] px-1.5 py-0.2 rounded bg-[#780000] text-white font-extrabold uppercase">PM Window</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-2.5 px-2 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $badgeClass }}">
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
            @else
            <div class="pt-3">
                <div class="p-4 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] text-center text-xs text-[#6E6E73] space-y-1">
                    <span class="font-bold text-[#1D1D1F] block">Hourly Telemetry Not Yet Available</span>
                    <span>High-resolution marine model telemetry unlocks 16 days prior to the dive date (unlocks on {{ $batch->start_date->copy()->subDays(16)->format('M d, Y') }}).</span>
                </div>
            </div>
            @endif

        </div>

        <!-- ===================================================================== -->
        <!-- DAY 2 SECTION -->
        <!-- ===================================================================== -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-5 sm:p-6 shadow-sm space-y-5">
            
            <!-- Day 2 Header -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="px-2.5 py-1 rounded-lg text-xs font-black bg-[#F8EAEA] text-[#780000] border border-[#F1D5D5]">
                            DAY 2
                        </span>
                        <h2 class="text-lg font-black text-[#1D1D1F]">
                            {{ $day2Assessment->dive_date->format('F d, Y (l)') }}
                        </h2>
                    </div>

                    <span class="px-3 py-1 rounded-full text-xs font-black uppercase border {{ $day2Assessment->classification_badge['class'] }}">
                        {{ $day2Assessment->overall_classification }}
                    </span>
                </div>

                <!-- Recommended Action after Day 2 Header -->
                <div class="p-3.5 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] text-xs text-[#1D1D1F] space-y-1">
                    <span class="font-bold text-[#6E6E73] block uppercase text-[11px] tracking-wider">Recommended Action:</span>
                    <p class="font-semibold text-[#1D1D1F] leading-snug">{{ $day2Assessment->recommended_action }}</p>
                </div>

                <!-- Day 2 Quick Stat Chips -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div class="p-3 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] flex items-center justify-between">
                        <div>
                            <span class="text-[#6E6E73] block text-xs uppercase font-bold">Worst Hour</span>
                            <strong class="text-sm font-extrabold text-[#1D1D1F]">
                                {{ $day2Assessment->worst_hour ? $day2Assessment->worst_hour->format('g:i A') : 'N/A' }}
                            </strong>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] flex items-center justify-between">
                        <div>
                            <span class="text-[#6E6E73] block text-xs uppercase font-bold">Forecast Horizon</span>
                            <strong class="text-sm font-extrabold text-[#1D1D1F]">
                                {{ round($day2Assessment->lead_time_hours) }}h before dive
                            </strong>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] space-y-1.5">
                        <div>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-extrabold border {{ $day2Assessment->reliability['badge_class'] }}">
                                    {{ $day2Assessment->reliability['label'] }}
                                </span>
                            </div>
                        </div>
                        <div class="text-[11px] text-[#6E6E73] font-medium leading-tight pt-1">
                            {{ $day2Assessment->reliability['description'] }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Day 2 Whole-Day 24-Hour Continuous Timeline with AM/PM Default & 24h Expansion -->
            @if(!empty($day2Continuous24h['hourly']))
            <div x-data="{ showAllHours: false }" class="pt-3 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">

                    <!-- Toggle Button -->
                    <button type="button" 
                            @click="showAllHours = !showAllHours" 
                            class="px-3.5 py-1.5 rounded-xl border border-[#D1D1D6] hover:border-[#00c3d0] bg-white hover:bg-[#FAFAFC] text-xs font-bold text-[#1D1D1F] flex items-center gap-1.5 transition-all shadow-2xs cursor-pointer">
                        <span x-text="showAllHours ? 'Collapse to AM & PM Windows' : 'Expand to All 24 Hours'"></span>
                        <svg class="w-3.5 h-3.5 transition-transform" :class="showAllHours ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                </div>

                <!-- 8-Variable Comprehensive Telemetry Table -->
                <div class="overflow-x-auto rounded-xl border border-[#E5E5EA] shadow-2xs">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[#FAFAFC] border-b border-[#E5E5EA] text-[11px] uppercase font-extrabold text-[#6E6E73]">
                            <tr>
                                <th class="py-3 px-3 whitespace-nowrap">Forecast Time</th>
                                <th class="py-3 px-2 whitespace-nowrap">Risk Rating</th>
                                <th class="py-3 px-2 whitespace-nowrap" title="Significant Wave Height (m)">Wave Height (Hs)</th>
                                <th class="py-3 px-2 whitespace-nowrap" title="Peak Wave Period (sec)">Wave Period (Tp)</th>
                                <th class="py-3 px-2 whitespace-nowrap" title="Swell Wave Height (m)">Swell Height</th>
                                <th class="py-3 px-2 whitespace-nowrap" title="Ocean Current Velocity (m/s)">Ocean Current</th>
                                <th class="py-3 px-2 whitespace-nowrap" title="Wind Wave Height (m)">Wind Wave</th>
                                <th class="py-3 px-2 whitespace-nowrap" title="Precipitation / Rain (mm)">Rain (mm)</th>
                                <th class="py-3 px-2 whitespace-nowrap" title="Mean Sea Level Pressure (hPa)">Pressure (hPa)</th>
                                <th class="py-3 px-3 whitespace-nowrap" title="10m Wind Speed and Direction">Wind Speed & Dir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5E5EA] bg-white">
                            @foreach($day2Continuous24h['hourly'] as $h)
                            @php
                                $isAmHour = in_array($h['hour'], [10, 11, 12]);
                                $isPmHour = in_array($h['hour'], [16, 17]);
                                $badgeClass = match($h['classification'] ?? 'Safe') {
                                    'Very Safe' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'Safe' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'Moderate' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'High Risk' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'Critical Risk' => 'bg-red-50 text-red-700 border-red-200',
                                    default => 'bg-gray-50 text-gray-700 border-gray-200',
                                };
                            @endphp
                            <tr x-show="showAllHours || {{ ($isAmHour || $isPmHour) ? 'true' : 'false' }}" 
                                class="hover:bg-[#FAFAFC] transition-colors {{ ($isAmHour || $isPmHour) ? 'bg-[#F8EAEA]/25 font-semibold' : '' }}">
                                <td class="py-2.5 px-3 whitespace-nowrap font-mono text-[#1D1D1F]">
                                    <div class="flex items-center gap-1.5">
                                        <span>{{ sprintf('%02d:00', $h['hour']) }}</span>
                                        @if($isAmHour)
                                            <span class="text-[9px] px-1.5 py-0.2 rounded bg-[#780000] text-white font-extrabold uppercase">AM Window</span>
                                        @elseif($isPmHour)
                                            <span class="text-[9px] px-1.5 py-0.2 rounded bg-[#780000] text-white font-extrabold uppercase">PM Window</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-2.5 px-2 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $badgeClass }}">
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
            @else
            <div class="pt-3">
                <div class="p-4 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] text-center text-xs text-[#6E6E73] space-y-1">
                    <span class="font-bold text-[#1D1D1F] block">Hourly Telemetry Not Yet Available</span>
                    <span>High-resolution marine model telemetry unlocks 16 days prior to the dive date (unlocks on {{ ($batch->end_date ?? $batch->start_date->copy()->addDay())->copy()->subDays(16)->format('M d, Y') }}).</span>
                </div>
            </div>
            @endif

        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- 3. PAST ASSESSMENT RUNS HISTORY TIMELINE (EXPANDABLE) -->
    <!-- ========================================================================= -->
    <div x-data="{ openAuditTrail: false }" class="mt-8 bg-white rounded-xl border border-[#E5E5EA] shadow-sm overflow-hidden transition-all">
        <button type="button" 
                @click="openAuditTrail = !openAuditTrail" 
                class="w-full p-5 sm:p-6 flex items-center justify-between text-left hover:bg-[#FAFAFC] transition-colors cursor-pointer select-none">
            <div class="flex items-center gap-3">
                <span class="text-base font-extrabold text-[#1D1D1F]">Assessment Audit Trail & History</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#F2F2F7] text-[#6E6E73] border border-[#E5E5EA]">
                    {{ count($assessmentRuns) }} run(s)
                </span>
            </div>
            <div class="flex items-center gap-2 text-xs font-bold text-[#780000]">
                <span x-text="openAuditTrail ? 'Hide History' : 'View Audit History'"></span>
                <svg class="w-4 h-4 transition-transform duration-200" :class="openAuditTrail ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>
        </button>

        <div x-show="openAuditTrail" x-cloak class="p-5 sm:p-6 pt-0 border-t border-[#E5E5EA] space-y-3">
            <div class="flex items-center justify-between text-xs text-[#6E6E73] pt-4 pb-1">
                <span>Chronological assessment history (Newest first)</span>
                <span>Auto-logged by Open-Meteo engine & staff</span>
            </div>

            @forelse($assessmentRuns as $timestamp => $records)
            @php
                $d1 = $records->firstWhere('day_number', 1);
                $d2 = $records->firstWhere('day_number', 2);
                $primary = $d1 ?: $d2;
                $runAssessor = ($d1 && $d1->assessor) ? $d1->assessor->name : (($d2 && $d2->assessor) ? $d2->assessor->name : 'Operator / Auto Engine');
                $runTime = ($primary && $primary->assessed_at) ? $primary->assessed_at->format('M d, Y, h:i A') : $timestamp;
            @endphp
            <div class="p-3.5 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-3">
                    <div class="w-2.5 h-2.5 rounded-full bg-[#780000] shrink-0"></div>
                    <div>
                        <strong class="text-[#1D1D1F] font-bold">
                            Run at {{ $runTime }}
                        </strong>
                        <span class="text-xs text-[#6E6E73] block mt-0.5">
                            Assessed by: <strong>{{ $runAssessor }}</strong>
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-4 self-end sm:self-center">
                    <div class="flex items-center gap-1.5">
                        <span class="text-[#6E6E73] font-semibold">Day 1:</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $d1 ? $d1->classification_badge['class'] : 'bg-gray-100 text-gray-600 border-gray-200' }}">
                            {{ $d1 ? $d1->overall_classification : 'N/A' }}
                        </span>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <span class="text-[#6E6E73] font-semibold">Day 2:</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $d2 ? $d2->classification_badge['class'] : 'bg-gray-100 text-gray-600 border-gray-200' }}">
                            {{ $d2 ? $d2->overall_classification : 'N/A' }}
                        </span>
                    </div>

                    @if(($d1 && $d1->override_triggered) || ($d2 && $d2->override_triggered))
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-[#FEF2F2] text-[#991B1B] border border-[#FECACA]">
                            Manual Override
                        </span>
                    @endif
                </div>
            </div>
            @empty
            <p class="text-xs text-[#6E6E73] py-2 text-center">No past assessment runs recorded yet.</p>
            @endforelse
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 1: MANUAL OVERRIDE (PAGASA-STYLE ADVISORIES) -->
    <!-- ========================================================================= -->
    <div x-show="openOverrideModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-lg w-full p-6 space-y-4 shadow-2xl border border-[#E5E5EA]" @click.outside="openOverrideModal = false">
            <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                <div class="flex items-center gap-2">
                    <h3 class="text-lg font-bold text-[#1D1D1F]">Apply Manual PAGASA Override</h3>
                </div>
                <button type="button" @click="openOverrideModal = false" class="text-lg font-bold text-[#8E8E93] hover:text-[#1D1D1F]">✕</button>
            </div>

            <p class="text-xs text-[#6E6E73]">
                Forces both <strong>Day 1</strong> and <strong>Day 2</strong> to <strong>Critical Risk</strong> due to official PAGASA gale warnings, tropical cyclones, or severe marine advisories.
            </p>

            <form action="{{ route('admin.weather.override', $batch) }}" method="POST" class="space-y-4 text-xs">
                @csrf

                <!-- TCWS Signal -->
                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-2">
                        Tropical Cyclone Wind Signal (TCWS)
                    </label>
                    <select name="tcws_signal" class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                        <option value="0">No Active TCWS Signal</option>
                        <option value="1">Signal No. 1</option>
                        <option value="2">Signal No. 2</option>
                        <option value="3" selected>Signal No. 3 (Forces Critical Risk)</option>
                        <option value="4">Signal No. 4 (Forces Critical Risk)</option>
                        <option value="5">Signal No. 5 (Forces Critical Risk)</option>
                    </select>
                </div>

                <!-- Boolean Advisory Checkboxes -->
                <div class="space-y-2 bg-[#FAFAFC] p-3.5 rounded-xl border border-[#E5E5EA]">
                    <span class="block font-bold text-[#1D1D1F] mb-2 text-xs uppercase tracking-wider">Active Severe Marine Advisories</span>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="gale_warning" value="1" class="rounded border-[#D1D1D6] text-[#780000]">
                        <span class="text-xs font-semibold text-[#1D1D1F]">PAGASA Marine Gale Warning</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="thunderstorm_advisory" value="1" class="rounded border-[#D1D1D6] text-[#780000]">
                        <span class="text-xs font-semibold text-[#1D1D1F]">Severe Thunderstorm / Lightning Advisory</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="typhoon_within_distance" value="1" class="rounded border-[#D1D1D6] text-[#780000]">
                        <span class="text-xs font-semibold text-[#1D1D1F]">Typhoon within Safety Distance (Batangas Coast)</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="tsunami_warning" value="1" class="rounded border-[#D1D1D6] text-[#780000]">
                        <span class="text-xs font-semibold text-[#1D1D1F]">Tsunami / Severe Marine Hazard Warning</span>
                    </label>
                </div>

                <!-- Reason / Description -->
                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-2">
                        Advisory Details / Source <span class="text-[#780000]">*</span>
                    </label>
                    <textarea name="reason" required rows="2" placeholder="e.g. PAGASA Severe Weather Bulletin #4 - Gale Warning in Southern Luzon coasts" class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white"></textarea>
                </div>

                <!-- Auto-Cancel Option -->
                <div class="p-3 bg-[#FEF2F2] rounded-xl border border-[#FECACA]">
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input type="checkbox" name="cancel_batch" value="1" class="rounded border-[#D1D1D6] text-[#780000] mt-0.5">
                        <span class="text-xs font-bold text-[#991B1B]">
                            Cancel batch immediately, trigger 100% force majeure refunds, and dispatch cancellation emails.
                        </span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#E5E5EA]">
                    <button type="button" @click="openOverrideModal = false" class="btn-secondary px-3.5 py-1.5 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary px-5 py-1.5 text-xs font-bold shadow-sm">
                        Apply Override
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 2: CANCEL BATCH WITH TEMPLATED EMAIL PREVIEW (PRD SECTION 9) -->
    <!-- ========================================================================= -->
    <div x-show="openCancelModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-lg w-full p-6 space-y-4 shadow-2xl border border-[#E5E5EA]" @click.outside="openCancelModal = false">
            <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                <div class="flex items-center gap-2">
                    <h3 class="text-lg font-bold text-[#FF3B3C]">Cancel Batch & Dispatch Customer Notifications</h3>
                </div>
                <button type="button" @click="openCancelModal = false" class="text-lg font-bold text-[#8E8E93] hover:text-[#1D1D1F]">✕</button>
            </div>

            <p class="text-xs text-[#6E6E73]">
                Confirming whole-batch cancellation will cascade to all connected bookings, trigger <strong>100% full refund eligibility</strong>, and send the official cancellation notice to all customers.
            </p>

            <form action="{{ route('admin.weather.cancel', $batch) }}" method="POST" class="space-y-4 text-xs">
                @csrf

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-2">
                        Cancellation Reasons / Marine Hazard Drivers <span class="text-[#780000]">*</span>
                    </label>
                    <input type="text" 
                           name="cancellation_reason" 
                           x-model="cancelReason" 
                           required 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white font-medium">
                </div>

                <!-- Template Preview Box (PRD Section 9) -->
                <div class="space-y-1.5">
                    <span class="block font-bold text-[#6E6E73] text-xs uppercase tracking-wider">Outbound Email Notification Preview</span>
                    <div class="p-4 bg-[#FAFAFC] rounded-xl border border-[#E5E5EA] font-sans text-xs text-[#1D1D1F] whitespace-pre-line leading-relaxed">
Good day, <strong class="text-[#780000]">[Customer Name]</strong>. Your scheduled date for <strong class="text-[#780000]">{{ $batch->start_date->format('M d') }} – {{ $batch->end_date->format('M d, Y') }}</strong> will be canceled due to:

- <span x-text="cancelReason" class="font-bold"></span>

There will be options for this cancelled schedule:
- Full refund
- Reschedule

You can select your preferred option by entering your booking number and PIN in Manage Booking.
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#E5E5EA]">
                    <button type="button" @click="openCancelModal = false" class="btn-secondary px-3.5 py-1.5 text-xs">Cancel</button>
                    <button type="submit" class="btn-danger px-4 py-2 text-xs font-bold shadow-2xs">
                        Confirm Cancellation & Send Emails
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
