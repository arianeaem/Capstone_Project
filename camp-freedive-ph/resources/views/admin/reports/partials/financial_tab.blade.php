@php
    $fin = $data['financials'] ?? [];
@endphp

<div class="space-y-6">

    <!-- Top Financial KPI Summary -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-5 shadow-2xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 items-stretch gap-4 sm:gap-6 divide-y sm:divide-y-0 divide-[#E5E5EA]">
            
            <!-- Net Collections -->
            <div class="px-2 sm:px-4 py-1">
                <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Net Collections</span>
                <div class="text-xl sm:text-2xl font-extrabold text-[#780000] mt-0.5 break-words">
                    ₱{{ number_format($fin['net_revenue'] ?? 0, 2) }}
                </div>
                <div class="text-xs text-[#8E8E93] flex items-center justify-between gap-2 mt-1">
                    <span>Gross: ₱{{ number_format($fin['gross_revenue'] ?? 0, 2) }}</span>
                    <span class="px-1.5 py-0.5 rounded text-[11px] font-bold shrink-0 {{ ($fin['revenue_delta'] ?? 0) >= 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                        {{ ($fin['revenue_delta'] ?? 0) >= 0 ? '+' : '' }}{{ $fin['revenue_delta'] ?? 0 }}%
                    </span>
                </div>
            </div>

            <!-- Downpayments vs Balance -->
            <div class="relative px-2 sm:px-4 pt-3 sm:pt-1 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Payment Stage Breakdown</span>
                <div class="text-xl sm:text-2xl font-extrabold text-[#1D1D1F] mt-0.5 break-words">
                    ₱{{ number_format($fin['downpayment_revenue'] ?? 0, 2) }}
                </div>
                <div class="text-xs text-[#8E8E93] flex items-center justify-between gap-2 mt-1">
                    <span>Downpayments: {{ ($fin['gross_revenue'] ?? 0) > 0 ? round((($fin['downpayment_revenue'] ?? 0) / $fin['gross_revenue']) * 100, 1) : 0 }}%</span>
                    <span>Settlements: ₱{{ number_format($fin['balance_revenue'] ?? 0, 2) }}</span>
                </div>
            </div>

            <!-- Outstanding Receivables -->
            <div class="relative px-2 sm:px-4 pt-3 sm:pt-1 py-1">
                <div class="hidden lg:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Unsettled Receivables</span>
                <div class="text-xl sm:text-2xl font-extrabold text-[#92400E] mt-0.5 break-words">
                    ₱{{ number_format($fin['outstanding_receivables'] ?? 0, 2) }}
                </div>
                <div class="text-xs text-[#8E8E93] flex items-center justify-between gap-2 mt-1">
                    <span>Pending check-in settlement</span>
                    <span class="px-1.5 py-0.5 rounded text-[11px] font-bold text-[#92400E] bg-amber-50 border border-amber-200 shrink-0">Confirmed</span>
                </div>
            </div>

            <!-- Average Revenue Per Diver (ARPD) -->
            <div class="relative px-2 sm:px-4 pt-3 sm:pt-1 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Avg Revenue / Diver (ARPD)</span>
                <div class="text-xl sm:text-2xl font-extrabold text-[#00C3D0] mt-0.5 break-words">
                    ₱{{ number_format($fin['arpd'] ?? 0, 2) }}
                </div>
                <div class="text-xs text-[#8E8E93] flex items-center justify-between gap-2 mt-1">
                    <span>Avg / Booking: ₱{{ number_format($fin['arpb'] ?? 0, 2) }}</span>
                    <span class="text-[11px] font-semibold text-[#6E6E73] shrink-0">Realized Yield</span>
                </div>
            </div>

        </div>
    </div>

    <!-- Middle: Class Package Mix & Revenue Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Cols: Package Revenue Table & Progress Bars -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-[#E5E5EA] p-3.5 sm:p-5 shadow-2xs space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm sm:text-base font-extrabold text-[#1D1D1F]">Revenue by Class Package</h3>
                </div>
                <span class="text-xs font-bold text-[#780000]">Total: ₱{{ number_format($fin['gross_revenue'] ?? 0, 2) }}</span>
            </div>

            <!-- Visual Progress Breakdown Bar -->
            <div class="w-full h-3 rounded-full bg-[#E5E5EA] overflow-hidden flex shadow-inner">
                @foreach($fin['packages'] ?? [] as $key => $pkg)
                    <div class="h-full transition-all" 
                         style="width: {{ $pkg['share'] ?? 0 }}%; background-color: {{ $pkg['color'] }};"
                         title="{{ $pkg['name'] }}: {{ $pkg['share'] }}%"></div>
                @endforeach
            </div>

            <!-- Tabular Breakdown -->
            <div class="overflow-x-auto rounded-xl border border-[#E5E5EA]">
                <table class="w-full text-left min-w-[550px]">
                    <thead class="bg-[#FAFAFC] border-b border-[#E5E5EA] text-xs uppercase font-bold text-[#6E6E73]">
                        <tr>
                            <th class="p-3.5 pl-5">Package</th>
                            <th class="p-3.5 text-center">Bookings</th>
                            <th class="p-3.5 text-center">Divers</th>
                            <th class="p-3.5 text-right">Revenue</th>
                            <th class="p-3.5 pr-5 text-right">Revenue Share</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E5EA]">
                        @foreach($fin['packages'] ?? [] as $key => $pkg)
                            <tr class="hover:bg-[#FAFAFC] transition-colors text-xs sm:text-sm">
                                <td class="p-3.5 pl-5 flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $pkg['color'] }};"></span>
                                    <span class="font-bold text-[#1D1D1F]">{{ $pkg['name'] }}</span>
                                </td>
                                <td class="p-3.5 text-center font-medium text-[#3A3A3C]">{{ $pkg['bookings_count'] }}</td>
                                <td class="p-3.5 text-center font-medium text-[#3A3A3C]">{{ $pkg['pax_count'] }} pax</td>
                                <td class="p-3.5 text-right font-extrabold text-[#1D1D1F]">₱{{ number_format($pkg['revenue'], 2) }}</td>
                                <td class="p-3.5 pr-5 text-right font-bold text-[#780000]">{{ $pkg['share'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right 1 Col: Add-ons & Ancillary Revenue -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-3.5 sm:p-5 shadow-2xs space-y-4">
            <h3 class="text-sm sm:text-base font-extrabold text-[#1D1D1F] border-b border-[#E5E5EA] pb-3">Add-ons & Logistics</h3>
            
            <!-- Manila Carpool Van -->
            <div class="p-3.5 rounded-xl bg-[#FAFAFC] space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-xs text-[#1D1D1F]">Manila Carpool Van</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-[#780000] text-white">₱1,200 / pax</span>
                </div>
                <div class="text-xl font-black text-[#780000]">
                    ₱{{ number_format($fin['carpool']['estimated_revenue'] ?? 0, 2) }}
                </div>
                <div class="text-[11px] text-[#6E6E73] flex items-center justify-between">
                    <span>{{ $fin['carpool']['pax_count'] ?? 0 }} divers transported</span>
                    <span>{{ $fin['carpool']['bookings_count'] ?? 0 }} bookings</span>
                </div>
            </div>

            <!-- Boat Dive Optional -->
            <div class="p-3.5 rounded-xl bg-[#FAFAFC] space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-xs text-[#1D1D1F]">Boat Dive Add-on</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-[#00C3D0] text-white">₱600 / pax</span>
                </div>
                <div class="text-xl font-black text-[#00C3D0]">
                    ₱{{ number_format($fin['boat_dive']['estimated_revenue'] ?? 0, 2) }}
                </div>
                <div class="text-[11px] text-[#6E6E73] flex items-center justify-between">
                    <span>{{ $fin['boat_dive']['pax_count'] ?? 0 }} divers enrolled</span>
                    <span>{{ $fin['boat_dive']['bookings_count'] ?? 0 }} bookings</span>
                </div>
            </div>

            <!-- Dynamic Pricing Lift -->
            <div class="p-3.5 rounded-xl bg-[#F8EAEA] space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-xs text-[#780000]">Dynamic Pricing Net Lift</span>
                    <span class="text-[10px] font-bold text-[#780000]">{{ $fin['dynamic_pricing']['adjustments_count'] ?? 0 }} rules</span>
                </div>
                <div class="text-xl font-black text-[#780000]">
                    ₱{{ number_format($fin['dynamic_pricing']['net_lift'] ?? 0, 2) }}
                </div>
                <div class="text-[11px] text-[#8E8E93] flex items-center justify-between">
                    <span>Yield: +₱{{ number_format($fin['dynamic_pricing']['positive_yield'] ?? 0, 2) }}</span>
                    <span>Discount: -₱{{ number_format($fin['dynamic_pricing']['discounts_given'] ?? 0, 2) }}</span>
                </div>
            </div>
        </div>

    </div>

</div>
