<!-- Date Range & Filter Bar -->
<div class="bg-white rounded-xl border border-[#E5E5EA] p-3.5 sm:p-4.5 shadow-2xs" x-data="{ customOpen: false }">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        
        <!-- Left: Reporting Period & Label/Dates (Next line, bigger) -->
        <div class="space-y-1">
            <span class="text-xs font-bold uppercase tracking-wider text-[#6E6E73]">Reporting Period</span>
            <div class="flex items-baseline gap-2 flex-wrap">
                <span class="text-base sm:text-xl font-black text-[#1D1D1F]">{{ $range['label'] }}</span>
                <span class="text-xs sm:text-sm font-semibold text-[#8E8E93]">({{ $range['start']->format('M d, Y') }} – {{ $range['end']->format('M d, Y') }})</span>
            </div>
        </div>

        <!-- Right: Custom Range & Date Presets Stacked on the Right -->
        <div class="flex flex-col items-start md:items-end gap-2.5">
            
            <!-- Custom Range Popover Button -->
            <div class="relative">
                <button type="button" 
                        @click="customOpen = !customOpen" 
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA] flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-[#6E6E73]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="1" x2="16" y2="6"></line><line x1="8" y1="1" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <span>Custom Range</span>
                </button>

                <div x-show="customOpen" 
                     @click.away="customOpen = false" 
                     x-cloak 
                     class="absolute right-0 mt-2 w-72 bg-white rounded-xl border border-[#E5E5EA] p-4 shadow-xl z-30 space-y-3">
                    <h4 class="text-xs font-bold text-[#1D1D1F]">Select Custom Date Range</h4>
                    <form method="GET" action="{{ auth()->user()->isOwner() ? route('owner.reports.index') : route('admin.reports.index') }}" class="space-y-2.5">
                        <input type="hidden" name="preset" value="custom">
                        @if(request('tab'))
                            <input type="hidden" name="tab" value="{{ request('tab') }}">
                        @endif

                        <div class="space-y-1">
                            <label class="block text-[11px] font-bold text-[#6E6E73]">Start Date</label>
                            <input type="date" 
                                   name="start_date" 
                                   value="{{ $range['start']->format('Y-m-d') }}" 
                                   class="w-full text-xs rounded-lg border border-[#D1D1D6] p-2 focus:border-[#780000]">
                        </div>

                        <div class="space-y-1">
                            <label class="block text-[11px] font-bold text-[#6E6E73]">End Date</label>
                            <input type="date" 
                                   name="end_date" 
                                   value="{{ $range['end']->format('Y-m-d') }}" 
                                   class="w-full text-xs rounded-lg border border-[#D1D1D6] p-2 focus:border-[#780000]">
                        </div>

                        <button type="submit" class="btn-primary w-full py-2 text-xs font-bold">
                            Apply Date Range
                        </button>
                    </form>
                </div>
            </div>

            <!-- Date Presets on the Right below Custom Range -->
            <div class="flex items-center gap-1.5 flex-wrap justify-start md:justify-end">
                @php
                    $presets = [
                        'this_month' => 'This Month',
                        'last_month' => 'Last Month',
                        'this_quarter' => 'This Quarter',
                        'year_to_date' => 'Year to Date',
                        'last_year' => 'Last Year',
                        'all_time' => 'All Time',
                    ];
                    $currentPreset = $range['preset'] ?? 'this_month';
                @endphp

                @foreach($presets as $key => $title)
                    <a href="{{ (auth()->user()->isOwner() ? route('owner.reports.index') : route('admin.reports.index')) . '?' . http_build_query(['preset' => $key, 'tab' => request('tab', $activeTab ?? 'bookings')]) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 {{ $currentPreset === $key ? 'bg-[#780000] text-white border border-[#780000] shadow-2xs' : 'bg-white text-[#6E6E73] hover:text-[#1D1D1F] hover:bg-[#F2F2F7] border border-[#E5E5EA]' }}">
                        {{ $title }}
                    </a>
                @endforeach
            </div>

        </div>

    </div>
</div>
