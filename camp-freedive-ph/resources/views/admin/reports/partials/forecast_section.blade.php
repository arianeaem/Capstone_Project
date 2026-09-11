@php
    $forecast = $data['forecast'] ?? [];
    $horizons = $forecast['horizon_summaries'] ?? [];
    $monthlyHorizons = $forecast['monthly_horizons'] ?? [];
    $trend = $data['forecast_trend'] ?? [];
    $syncedAt = $forecast['synced_at'] ?? null;
    $source = $forecast['source'] ?? 'ML Model';
@endphp

<!-- AI Demand & Revenue Horizon Forecasting Component -->
<div class="space-y-6"
     x-data="{
         selectedHorizon: '30',
         monthlyData: @js($monthlyHorizons),
         trendData: @js($trend),

         get currentCards() {
             return this.monthlyData[this.selectedHorizon] || this.monthlyData[this.selectedHorizon + '_day'] || [];
         },

         get chartItems() {
             const actuals = (this.trendData || []).filter(item => item.type === 'actual');
             const forecastItems = (this.currentCards || []).map(card => ({
                 month_key: card.month_key,
                 type: 'forecast',
                 label: card.short_name + ' (Est)',
                 participants: card.diver_volume,
                 bookings: card.estimated_bookings,
                 revenue_php: card.projected_revenue,
                 coaches: card.coaches_needed,
                 batches_count: card.batches_count,
                 demand_level: card.demand_classification,
                 season_period: card.peak_classification
             }));
             return [...actuals, ...forecastItems];
         },

         getMax(metric) {
             const items = this.chartItems;
             if (!items || items.length === 0) return 1;
             let max = 0;
             for (const it of items) {
                 let val = 0;
                 if (metric === 'revenue') val = it.revenue_php || 0;
                 else if (metric === 'bookings') val = it.bookings || 0;
                 else if (metric === 'coaches') val = it.coaches || 0;
                 else val = it.participants || 0;
                 if (val > max) max = val;
             }
             if (metric === 'revenue') return Math.max(max, 10000);
             if (metric === 'coaches') return Math.max(max, 4);
             if (metric === 'bookings') return Math.max(max, 5);
             return Math.max(max, 10);
         },

         getBarHeight(item, metric) {
             let val = 0;
             if (metric === 'revenue') val = item.revenue_php || 0;
             else if (metric === 'bookings') val = item.bookings || 0;
             else if (metric === 'coaches') val = item.coaches || 0;
             else val = item.participants || 0;

             const max = this.getMax(metric);
             const pct = Math.round((val / max) * 100);
             return Math.max(pct, 10);
         },

         getMetricValue(item, metric) {
             if (!item) return 0;
             if (metric === 'revenue') return item.revenue_php || 0;
             if (metric === 'bookings') return item.bookings || 0;
             if (metric === 'coaches') return item.coaches || 0;
             return item.participants || 0;
         },

         formatValue(val, metric) {
             if (metric === 'revenue') {
                 if (val >= 1000000) return '₱' + (val / 1000000).toFixed(1) + 'M';
                 if (val >= 1000) return '₱' + (val / 1000).toFixed(0) + 'k';
                 return '₱' + Math.round(val).toLocaleString();
             }
             return Math.round(val).toLocaleString();
         },

         formatCurrency(val) {
             return '₱' + Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
         }
     }">
    
    <!-- Top Section: Horizon Projection Period Selector & Month Cards -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-6 shadow-2xs space-y-5">
        
        <!-- Top Row: Title, Sync Badge & Projection Period Selector -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-base sm:text-lg font-extrabold text-[#1D1D1F]">Demand & Revenue Forecasting</h2>
                </div>
                <p class="text-xs text-[#6E6E73] mt-1">
                    Machine learning rolling predictions dynamically grouped by month across operating horizons.
                </p>
            </div>

            <!-- Controls: Period Selector & Sync Indicator -->
            <div class="flex flex-wrap items-center gap-3">
                @if($syncedAt)
                    <div class="flex items-center gap-1.5 text-[11px] font-semibold text-[#6E6E73] bg-[#FAFAFC] px-2.5 py-1.5 rounded-lg border border-[#E5E5EA]">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Synced {{ \Carbon\Carbon::parse($syncedAt)->diffForHumans() }}</span>
                    </div>
                @endif

                <!-- Projection Period Selector Tabs (7, 30, 60, 90 Days) -->
                <div class="inline-flex items-center p-1 bg-[#F2F2F7] rounded-xl border border-[#E5E5EA]">
                    <button type="button"
                            @click="selectedHorizon = '7'"
                            :class="selectedHorizon === '7' ? 'bg-white text-[#780000] font-black shadow-2xs' : 'text-[#6E6E73] font-semibold hover:text-[#1D1D1F]'"
                            class="px-3 py-1.5 text-xs rounded-lg transition-all duration-150 cursor-pointer">
                        7 Days
                    </button>
                    <button type="button"
                            @click="selectedHorizon = '30'"
                            :class="selectedHorizon === '30' ? 'bg-white text-[#780000] font-black shadow-2xs' : 'text-[#6E6E73] font-semibold hover:text-[#1D1D1F]'"
                            class="px-3 py-1.5 text-xs rounded-lg transition-all duration-150 cursor-pointer">
                        30 Days
                    </button>
                    <button type="button"
                            @click="selectedHorizon = '60'"
                            :class="selectedHorizon === '60' ? 'bg-white text-[#780000] font-black shadow-2xs' : 'text-[#6E6E73] font-semibold hover:text-[#1D1D1F]'"
                            class="px-3 py-1.5 text-xs rounded-lg transition-all duration-150 cursor-pointer">
                        60 Days
                    </button>
                    <button type="button"
                            @click="selectedHorizon = '90'"
                            :class="selectedHorizon === '90' ? 'bg-white text-[#780000] font-black shadow-2xs' : 'text-[#6E6E73] font-semibold hover:text-[#1D1D1F]'"
                            class="px-3 py-1.5 text-xs rounded-lg transition-all duration-150 cursor-pointer">
                        90 Days
                    </button>
                </div>
            </div>
        </div>

        <!-- Dynamic Month Cards Grid (Based on Selected Projection Period) -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-black uppercase tracking-wider text-[#6E6E73]">
                    Projected Months (<span x-text="currentCards.length"></span>)
                </span>
                <span class="text-[11px] font-semibold text-[#8E8E93]" x-text="'Showing ' + selectedHorizon + '-Day Operational Horizon'"></span>
            </div>

            <!-- Month Cards Container -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                
                <template x-for="(card, index) in currentCards" :key="card.month_key || index">
                    <div class="p-4 rounded-xl bg-gradient-to-br from-white via-white to-[#FAFAFC] border border-[#E5E5EA] hover:border-[#780000]/30 transition-all duration-200 shadow-2xs space-y-3 relative overflow-hidden group">
                        
                        <!-- Top Header: Month Name & Demand Badge -->
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h4 class="text-sm font-black text-[#1D1D1F] group-hover:text-[#780000] transition-colors" x-text="card.month_name"></h4>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <!-- Peak Season Badge -->
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md border"
                                          :class="{
                                              'bg-rose-50 text-[#780000] border-rose-200': card.peak_classification.includes('Peak') && !card.peak_classification.includes('Off'),
                                              'bg-teal-50 text-teal-800 border-teal-200': card.peak_classification.includes('Shoulder'),
                                              'bg-slate-50 text-slate-700 border-slate-200': card.peak_classification.includes('Off')
                                          }"
                                          x-text="card.peak_classification">
                                    </span>
                                </div>
                            </div>

                            <!-- Demand Classification Badge -->
                            <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full border shrink-0"
                                  :class="{
                                      'bg-amber-50 text-amber-800 border-amber-200': card.demand_classification === 'High',
                                      'bg-emerald-50 text-emerald-800 border-emerald-200': card.demand_classification === 'Medium',
                                      'bg-slate-100 text-slate-700 border-slate-200': card.demand_classification === 'Low'
                                  }"
                                  x-text="card.demand_classification + ' Demand'">
                            </span>
                        </div>

                        <!-- Projected Revenue (Primary Metric) -->
                        <div class="pt-1">
                            <span class="text-[10px] font-bold text-[#6E6E73] uppercase tracking-wider block">Projected Revenue</span>
                            <div class="text-xl font-black text-[#780000] tracking-tight mt-0.5" x-text="formatCurrency(card.projected_revenue)"></div>
                        </div>

                        <!-- Secondary Metrics Grid (No Emojis) -->
                        <div class="pt-2 border-t border-[#F2F2F7] space-y-1.5 text-xs text-[#6E6E73]">
                            
                            <!-- Diver Volume -->
                            <div class="flex items-center justify-between">
                                <span>Diver Volume:</span>
                                <strong class="text-[#1D1D1F] font-extrabold" x-text="card.diver_volume + ' Divers'"></strong>
                            </div>

                            <!-- Estimated Bookings -->
                            <div class="flex items-center justify-between">
                                <span>Est. Bookings:</span>
                                <strong class="text-[#1D1D1F] font-extrabold" x-text="card.estimated_bookings + ' Bookings'"></strong>
                            </div>

                            <!-- Batches Count -->
                            <div class="flex items-center justify-between">
                                <span>Dive Batches:</span>
                                <span class="font-bold text-[#1D1D1F]" x-text="card.batches_count + ' ' + (card.batches_count === 1 ? 'Batch' : 'Batches')"></span>
                            </div>

                            <!-- Peak Staffing Needed -->
                            <div class="flex items-center justify-between pt-1 border-t border-dashed border-[#E5E5EA] text-[#780000] font-black text-[11px]">
                                <span>Peak Coaches:</span>
                                <span class="bg-[#780000]/10 px-2 py-0.5 rounded-md" x-text="card.coaches_needed + ' ' + (card.coaches_needed === 1 ? 'Coach' : 'Coaches')"></span>
                            </div>

                        </div>

                    </div>
                </template>

                <!-- Empty State if no cards for selected period -->
                <div x-show="currentCards.length === 0" class="col-span-full p-8 text-center bg-[#FAFAFC] rounded-xl border border-dashed border-[#E5E5EA] text-xs text-[#8E8E93]">
                    No projected months available for the selected horizon period.
                </div>

            </div>
        </div>

    </div>

    <!-- 4 Separate Visual Cards: Divers, Bookings, Revenue, Coaches (2x2 Grid) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Visual Card 1: Diver Volume Trend -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-5 shadow-2xs space-y-4 flex flex-col justify-between">
            <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                <div>
                    <h3 class="text-sm font-extrabold text-[#1D1D1F]">Diver Volume Trend</h3>
                    <p class="text-xs text-[#6E6E73] mt-0.5">Historical turnout vs. AI projected diver count by month</p>
                </div>
                <div class="flex items-center gap-3 text-xs shrink-0">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-[#780000]"></span>
                        <span class="font-bold text-[#1D1D1F] text-[11px]">Actuals</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-indigo-600"></span>
                        <span class="font-bold text-[#1D1D1F] text-[11px]">Forecast</span>
                    </div>
                </div>
            </div>

            <!-- Chart Container -->
            <div class="pt-2">
                <div class="flex items-end justify-around gap-2 sm:gap-3 h-48 pt-6 pb-2 px-2 border-b border-[#E5E5EA] bg-[#FAFAFC]/60 rounded-t-xl">
                    <template x-for="(item, idx) in chartItems" :key="'divers_' + item.month_key + '_' + item.type">
                        <div class="flex-1 flex flex-col items-center h-full justify-end group relative max-w-[64px]">
                            
                            <!-- Hover Tooltip -->
                            <div class="absolute bottom-full mb-2 w-44 p-2.5 rounded-xl bg-[#1D1D1F] text-white text-[11px] opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-30 pointer-events-none shadow-xl space-y-1">
                                <div class="flex items-center justify-between font-bold" :class="item.type === 'forecast' ? 'text-indigo-300' : 'text-[#F1D5D5]'">
                                    <span x-text="item.label"></span>
                                    <span class="text-[9px] uppercase px-1.5 py-0.2 rounded font-black" :class="item.type === 'forecast' ? 'bg-indigo-900 text-indigo-200' : 'bg-[#780000] text-white'" x-text="item.type === 'forecast' ? 'Forecast' : 'Actual'"></span>
                                </div>
                                <div class="text-white font-black text-xs" x-text="item.participants + ' Divers'"></div>
                                <div class="text-gray-300 text-[10px] flex items-center justify-between">
                                    <span>Demand:</span>
                                    <span class="font-bold text-white" x-text="item.demand_level"></span>
                                </div>
                            </div>

                            <!-- Value Label Above Bar -->
                            <span class="text-[10px] sm:text-[11px] font-black mb-1"
                                  :class="item.type === 'forecast' ? 'text-indigo-600' : 'text-[#780000]'"
                                  x-text="item.participants">
                            </span>

                            <!-- Bar -->
                            <div class="w-full max-w-[38px] rounded-t-md transition-all duration-300 group-hover:scale-y-105 origin-bottom relative cursor-pointer"
                                 :class="item.type === 'forecast' ? 'bg-gradient-to-t from-indigo-700 to-indigo-500 border-t border-indigo-300' : 'bg-gradient-to-t from-[#780000] to-[#A82020]'"
                                 :style="'height: ' + getBarHeight(item, 'divers') + '%;'">
                            </div>

                        </div>
                    </template>
                </div>

                <!-- Labels -->
                <div class="flex items-center justify-around gap-2 sm:gap-3 px-2 text-center pt-2">
                    <template x-for="(item, idx) in chartItems" :key="'label_divers_' + item.month_key + '_' + item.type">
                        <div class="flex-1 max-w-[64px] truncate">
                            <span class="text-[10px] sm:text-[11px] font-bold block truncate" :class="item.type === 'forecast' ? 'text-indigo-700' : 'text-[#1D1D1F]'" x-text="item.label"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Visual Card 2: Bookings Trend -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-5 shadow-2xs space-y-4 flex flex-col justify-between">
            <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                <div>
                    <h3 class="text-sm font-extrabold text-[#1D1D1F]">Bookings Volume Trend</h3>
                    <p class="text-xs text-[#6E6E73] mt-0.5">Historical realized vs. projected booking groups by month</p>
                </div>
                <div class="flex items-center gap-3 text-xs shrink-0">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-[#780000]"></span>
                        <span class="font-bold text-[#1D1D1F] text-[11px]">Actuals</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-indigo-600"></span>
                        <span class="font-bold text-[#1D1D1F] text-[11px]">Forecast</span>
                    </div>
                </div>
            </div>

            <!-- Chart Container -->
            <div class="pt-2">
                <div class="flex items-end justify-around gap-2 sm:gap-3 h-48 pt-6 pb-2 px-2 border-b border-[#E5E5EA] bg-[#FAFAFC]/60 rounded-t-xl">
                    <template x-for="(item, idx) in chartItems" :key="'bookings_' + item.month_key + '_' + item.type">
                        <div class="flex-1 flex flex-col items-center h-full justify-end group relative max-w-[64px]">
                            
                            <!-- Hover Tooltip -->
                            <div class="absolute bottom-full mb-2 w-44 p-2.5 rounded-xl bg-[#1D1D1F] text-white text-[11px] opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-30 pointer-events-none shadow-xl space-y-1">
                                <div class="flex items-center justify-between font-bold" :class="item.type === 'forecast' ? 'text-indigo-300' : 'text-[#F1D5D5]'">
                                    <span x-text="item.label"></span>
                                    <span class="text-[9px] uppercase px-1.5 py-0.2 rounded font-black" :class="item.type === 'forecast' ? 'bg-indigo-900 text-indigo-200' : 'bg-[#780000] text-white'" x-text="item.type === 'forecast' ? 'Forecast' : 'Actual'"></span>
                                </div>
                                <div class="text-white font-black text-xs" x-text="item.bookings + ' Bookings'"></div>
                                <div class="text-gray-300 text-[10px] flex items-center justify-between">
                                    <span>Batches:</span>
                                    <span class="font-bold text-white" x-text="item.batches_count"></span>
                                </div>
                            </div>

                            <!-- Value Label Above Bar -->
                            <span class="text-[10px] sm:text-[11px] font-black mb-1"
                                  :class="item.type === 'forecast' ? 'text-indigo-600' : 'text-[#780000]'"
                                  x-text="item.bookings">
                            </span>

                            <!-- Bar -->
                            <div class="w-full max-w-[38px] rounded-t-md transition-all duration-300 group-hover:scale-y-105 origin-bottom relative cursor-pointer"
                                 :class="item.type === 'forecast' ? 'bg-gradient-to-t from-indigo-700 to-indigo-500 border-t border-indigo-300' : 'bg-gradient-to-t from-[#780000] to-[#A82020]'"
                                 :style="'height: ' + getBarHeight(item, 'bookings') + '%;'">
                            </div>

                        </div>
                    </template>
                </div>

                <!-- Labels -->
                <div class="flex items-center justify-around gap-2 sm:gap-3 px-2 text-center pt-2">
                    <template x-for="(item, idx) in chartItems" :key="'label_bookings_' + item.month_key + '_' + item.type">
                        <div class="flex-1 max-w-[64px] truncate">
                            <span class="text-[10px] sm:text-[11px] font-bold block truncate" :class="item.type === 'forecast' ? 'text-indigo-700' : 'text-[#1D1D1F]'" x-text="item.label"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Visual Card 3: Revenue Trend -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-5 shadow-2xs space-y-4 flex flex-col justify-between">
            <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                <div>
                    <h3 class="text-sm font-extrabold text-[#1D1D1F]">Revenue Projections & Actuals</h3>
                    <p class="text-xs text-[#6E6E73] mt-0.5">Realized trip payments vs. forecasted gross revenue (PHP)</p>
                </div>
                <div class="flex items-center gap-3 text-xs shrink-0">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-[#780000]"></span>
                        <span class="font-bold text-[#1D1D1F] text-[11px]">Actuals</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-indigo-600"></span>
                        <span class="font-bold text-[#1D1D1F] text-[11px]">Forecast</span>
                    </div>
                </div>
            </div>

            <!-- Chart Container -->
            <div class="pt-2">
                <div class="flex items-end justify-around gap-2 sm:gap-3 h-48 pt-6 pb-2 px-2 border-b border-[#E5E5EA] bg-[#FAFAFC]/60 rounded-t-xl">
                    <template x-for="(item, idx) in chartItems" :key="'revenue_' + item.month_key + '_' + item.type">
                        <div class="flex-1 flex flex-col items-center h-full justify-end group relative max-w-[64px]">
                            
                            <!-- Hover Tooltip -->
                            <div class="absolute bottom-full mb-2 w-48 p-2.5 rounded-xl bg-[#1D1D1F] text-white text-[11px] opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-30 pointer-events-none shadow-xl space-y-1">
                                <div class="flex items-center justify-between font-bold" :class="item.type === 'forecast' ? 'text-indigo-300' : 'text-[#F1D5D5]'">
                                    <span x-text="item.label"></span>
                                    <span class="text-[9px] uppercase px-1.5 py-0.2 rounded font-black" :class="item.type === 'forecast' ? 'bg-indigo-900 text-indigo-200' : 'bg-[#780000] text-white'" x-text="item.type === 'forecast' ? 'Forecast' : 'Actual'"></span>
                                </div>
                                <div class="text-white font-black text-xs" x-text="formatCurrency(item.revenue_php)"></div>
                                <div class="text-gray-300 text-[10px] flex items-center justify-between">
                                    <span>Diver Pax:</span>
                                    <span class="font-bold text-white" x-text="item.participants"></span>
                                </div>
                            </div>

                            <!-- Value Label Above Bar -->
                            <span class="text-[10px] sm:text-[11px] font-black mb-1"
                                  :class="item.type === 'forecast' ? 'text-indigo-600' : 'text-[#780000]'"
                                  x-text="formatValue(item.revenue_php, 'revenue')">
                            </span>

                            <!-- Bar -->
                            <div class="w-full max-w-[38px] rounded-t-md transition-all duration-300 group-hover:scale-y-105 origin-bottom relative cursor-pointer"
                                 :class="item.type === 'forecast' ? 'bg-gradient-to-t from-indigo-700 to-indigo-500 border-t border-indigo-300' : 'bg-gradient-to-t from-[#780000] to-[#A82020]'"
                                 :style="'height: ' + getBarHeight(item, 'revenue') + '%;'">
                            </div>

                        </div>
                    </template>
                </div>

                <!-- Labels -->
                <div class="flex items-center justify-around gap-2 sm:gap-3 px-2 text-center pt-2">
                    <template x-for="(item, idx) in chartItems" :key="'label_revenue_' + item.month_key + '_' + item.type">
                        <div class="flex-1 max-w-[64px] truncate">
                            <span class="text-[10px] sm:text-[11px] font-bold block truncate" :class="item.type === 'forecast' ? 'text-indigo-700' : 'text-[#1D1D1F]'" x-text="item.label"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Visual Card 4: Coach Staffing Trend -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-5 shadow-2xs space-y-4 flex flex-col justify-between">
            <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                <div>
                    <h3 class="text-sm font-extrabold text-[#1D1D1F]">Coach Staffing Requirements</h3>
                    <p class="text-xs text-[#6E6E73] mt-0.5">Historical assigned coaches vs. model recommended peak capacity</p>
                </div>
                <div class="flex items-center gap-3 text-xs shrink-0">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-[#780000]"></span>
                        <span class="font-bold text-[#1D1D1F] text-[11px]">Actuals</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm bg-indigo-600"></span>
                        <span class="font-bold text-[#1D1D1F] text-[11px]">Forecast</span>
                    </div>
                </div>
            </div>

            <!-- Chart Container -->
            <div class="pt-2">
                <div class="flex items-end justify-around gap-2 sm:gap-3 h-48 pt-6 pb-2 px-2 border-b border-[#E5E5EA] bg-[#FAFAFC]/60 rounded-t-xl">
                    <template x-for="(item, idx) in chartItems" :key="'coaches_' + item.month_key + '_' + item.type">
                        <div class="flex-1 flex flex-col items-center h-full justify-end group relative max-w-[64px]">
                            
                            <!-- Hover Tooltip -->
                            <div class="absolute bottom-full mb-2 w-44 p-2.5 rounded-xl bg-[#1D1D1F] text-white text-[11px] opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-30 pointer-events-none shadow-xl space-y-1">
                                <div class="flex items-center justify-between font-bold" :class="item.type === 'forecast' ? 'text-indigo-300' : 'text-[#F1D5D5]'">
                                    <span x-text="item.label"></span>
                                    <span class="text-[9px] uppercase px-1.5 py-0.2 rounded font-black" :class="item.type === 'forecast' ? 'bg-indigo-900 text-indigo-200' : 'bg-[#780000] text-white'" x-text="item.type === 'forecast' ? 'Forecast' : 'Actual'"></span>
                                </div>
                                <div class="text-white font-black text-xs" x-text="item.coaches + ' Coaches'"></div>
                                <div class="text-gray-300 text-[10px] flex items-center justify-between">
                                    <span>Ratio:</span>
                                    <span class="font-bold text-white">~1 coach / 4 pax</span>
                                </div>
                            </div>

                            <!-- Value Label Above Bar -->
                            <span class="text-[10px] sm:text-[11px] font-black mb-1"
                                  :class="item.type === 'forecast' ? 'text-indigo-600' : 'text-[#780000]'"
                                  x-text="item.coaches">
                            </span>

                            <!-- Bar -->
                            <div class="w-full max-w-[38px] rounded-t-md transition-all duration-300 group-hover:scale-y-105 origin-bottom relative cursor-pointer"
                                 :class="item.type === 'forecast' ? 'bg-gradient-to-t from-indigo-700 to-indigo-500 border-t border-indigo-300' : 'bg-gradient-to-t from-[#780000] to-[#A82020]'"
                                 :style="'height: ' + getBarHeight(item, 'coaches') + '%;'">
                            </div>

                        </div>
                    </template>
                </div>

                <!-- Labels -->
                <div class="flex items-center justify-around gap-2 sm:gap-3 px-2 text-center pt-2">
                    <template x-for="(item, idx) in chartItems" :key="'label_coaches_' + item.month_key + '_' + item.type">
                        <div class="flex-1 max-w-[64px] truncate">
                            <span class="text-[10px] sm:text-[11px] font-bold block truncate" :class="item.type === 'forecast' ? 'text-indigo-700' : 'text-[#1D1D1F]'" x-text="item.label"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </div>

</div>


