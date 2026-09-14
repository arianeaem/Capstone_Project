@php
    $fin = $data['financials'] ?? [];
@endphp

<div class="space-y-6">

    <!-- Top Financial KPI Summary -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-5 shadow-2xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 items-stretch gap-4 sm:gap-6 divide-y sm:divide-y-0 divide-[#E5E5EA]">
            
            <!-- Net Collections -->
            <div class="px-2 sm:px-4 py-1">
                <span class="text-sm font-bold text-[#6E6E73] uppercase tracking-wider block">Net Collections</span>
                <div class="text-xl sm:text-2xl font-extrabold text-[#780000] mt-0.5 break-words">
                    ₱{{ number_format($fin['net_revenue'] ?? 0, 2) }}
                </div>
                <div class="text-sm text-[#8E8E93] flex items-center justify-between gap-2 mt-1">
                    <span>Gross: ₱{{ number_format($fin['gross_revenue'] ?? 0, 2) }}</span>
                    <span class="px-1.5 py-0.5 rounded text-sm font-bold shrink-0 {{ ($fin['revenue_delta'] ?? 0) >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                        {{ ($fin['revenue_delta'] ?? 0) >= 0 ? '+' : '' }}{{ $fin['revenue_delta'] ?? 0 }}%
                    </span>
                </div>
            </div>

            <!-- Downpayments vs Balance -->
            <div class="relative px-2 sm:px-4 pt-3 sm:pt-1 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-sm font-bold text-[#6E6E73] uppercase tracking-wider block">Payment Stage Breakdown</span>
                <div class="text-xl sm:text-2xl font-extrabold text-[#1D1D1F] mt-0.5 break-words">
                    ₱{{ number_format($fin['downpayment_revenue'] ?? 0, 2) }}
                </div>
                <div class="text-sm text-[#8E8E93] flex items-center justify-between gap-2 mt-1">
                    <span>Downpayments: {{ ($fin['gross_revenue'] ?? 0) > 0 ? round((($fin['downpayment_revenue'] ?? 0) / $fin['gross_revenue']) * 100, 1) : 0 }}%</span>
                    <span>Settlements: ₱{{ number_format($fin['balance_revenue'] ?? 0, 2) }}</span>
                </div>
            </div>

            <!-- Outstanding Receivables -->
            <div class="relative px-2 sm:px-4 pt-3 sm:pt-1 py-1">
                <div class="hidden lg:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-sm font-bold text-[#6E6E73] uppercase tracking-wider block">Unsettled Receivables</span>
                <div class="text-xl sm:text-2xl font-extrabold text-[#92400E] mt-0.5 break-words">
                    ₱{{ number_format($fin['outstanding_receivables'] ?? 0, 2) }}
                </div>
                <div class="text-sm text-[#8E8E93] flex items-center justify-between gap-2 mt-1">
                    <span>Pending check-in settlement</span>
                    <span class="px-1.5 py-0.5 rounded text-sm font-bold text-[#92400E] bg-amber-50 border border-amber-200 shrink-0">Confirmed</span>
                </div>
            </div>

            <!-- Average Revenue Per Diver (ARPD) -->
            <div class="relative px-2 sm:px-4 pt-3 sm:pt-1 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-sm font-bold text-[#6E6E73] uppercase tracking-wider block">Avg Revenue / Diver (ARPD)</span>
                <div class="text-xl sm:text-2xl font-extrabold text-[#00C3D0] mt-0.5 break-words">
                    ₱{{ number_format($fin['arpd'] ?? 0, 2) }}
                </div>
                <div class="text-sm text-[#8E8E93] flex items-center justify-between gap-2 mt-1">
                    <span>Avg / Booking: ₱{{ number_format($fin['arpb'] ?? 0, 2) }}</span>
                    <span class="text-sm font-semibold text-[#6E6E73] shrink-0">Realized Yield</span>
                </div>
            </div>

        </div>
    </div>

    <!-- Middle: Class Package Mix & Revenue Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Cols: Package Revenue Breakdown (Pie Chart + Details) -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-[#E5E5EA] p-3.5 sm:p-6 shadow-2xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 sm:gap-4">
                <div>
                    <h3 class="text-sm sm:text-base font-extrabold text-[#1D1D1F]">Revenue by Class Package</h3>
                    <p class="text-sm text-[#6E6E73]">Revenue share, bookings, and diver distribution per course.</p>
                </div>
                <span class="text-sm font-bold text-[#780000] self-start sm:self-auto">Total: ₱{{ number_format($fin['gross_revenue'] ?? 0, 2) }}</span>
            </div>

            @php
                $gradientStops = [];
                $currentPercent = 0;
                $hasRevenue = false;

                foreach($fin['packages'] ?? [] as $pKey => $pData) {
                    $share = (float)($pData['share_percentage'] ?? $pData['share'] ?? 0);
                    $color = $pData['color'] ?? match($pKey) {
                        'discovery' => '#780000',
                        'fundive' => '#A82020',
                        'refinement' => '#D45D5D',
                        default => '#780000'
                    };

                    if ($share > 0) {
                        $hasRevenue = true;
                        $nextPercent = $currentPercent + $share;
                        $gradientStops[] = "{$color} {$currentPercent}% {$nextPercent}%";
                        $currentPercent = $nextPercent;
                    }
                }

                if ($currentPercent < 100 && $hasRevenue) {
                    $gradientStops[] = ($color ?? '#780000') . " {$currentPercent}% 100%";
                }

                $conicBg = $hasRevenue 
                    ? 'conic-gradient(' . implode(', ', $gradientStops) . ')' 
                    : '#E5E5EA';
            @endphp

            <div class="flex flex-col lg:flex-row items-center gap-6 sm:gap-8 lg:gap-12 pt-2">
                
                <!-- Left: Pie / Donut Chart (Extra Large & Mobile Responsive) -->
                <div class="relative shrink-0 flex items-center justify-center py-2 sm:py-3">
                    <!-- Outer Conic Circle -->
                    <div class="w-56 h-56 sm:w-72 sm:h-72 lg:w-80 lg:h-80 rounded-full shadow-inner flex items-center justify-center transition-all"
                         style="background: {{ $conicBg }};">
                        
                        <!-- Inner Hole for Donut Style -->
                        <div class="w-34 h-34 sm:w-44 sm:h-44 lg:w-48 lg:h-48 bg-white rounded-full shadow-sm flex flex-col items-center justify-center text-center p-3 sm:p-4">
                            <span class="text-sm sm:text-sm font-bold text-[#8E8E93] uppercase tracking-wider">Gross Total</span>
                            <span class="text-sm sm:text-xl lg:text-2xl font-black text-[#1D1D1F] tracking-tight truncate max-w-full px-1 mt-0.5 sm:mt-1">
                                ₱{{ number_format($fin['gross_revenue'] ?? 0) }}
                            </span>
                            <span class="text-sm sm:text-sm font-bold text-[#780000] mt-0.5 sm:mt-1">
                                100%
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right: Package Details Breakdown -->
                <div class="flex-1 w-full space-y-2.5 sm:space-y-3">
                    @foreach($fin['packages'] ?? [] as $pKey => $pData)
                        @php
                            $share = $pData['share_percentage'] ?? $pData['share'] ?? 0;
                            $dotClass = $pData['dot_class'] ?? match($pKey) {
                                'discovery' => 'bg-[#780000]',
                                'fundive' => 'bg-[#A82020]',
                                'refinement' => 'bg-[#D45D5D]',
                                default => 'bg-[#780000]'
                            };
                            $textColor = $pData['text_color'] ?? match($pKey) {
                                'discovery' => 'text-[#780000]',
                                'fundive' => 'text-[#A82020]',
                                'refinement' => 'text-[#D45D5D]',
                                default => 'text-[#780000]'
                            };
                        @endphp
                        <div class="p-3 sm:p-4 rounded-xl flex items-center justify-between gap-3 sm:gap-4  transition-all">
                            <div class="space-y-0.5 min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $dotClass }} shrink-0"></span>
                                    <span class="font-extrabold text-sm sm:text-base text-[#1D1D1F] truncate">{{ $pData['name'] }}</span>
                                </div>
                                <div class="text-sm sm:text-sm text-[#6E6E73] flex flex-col sm:flex-row sm:items-center gap-0.5 sm:gap-2 pl-4.5">
                                    <span class="font-semibold text-[#1D1D1F] whitespace-nowrap">{{ $pData['bookings_count'] }} {{ Str::plural('booking', $pData['bookings_count']) }}</span>
                                    <span class="whitespace-nowrap">{{ $pData['pax_count'] }} {{ Str::plural('diver', $pData['pax_count']) }}</span>
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <div class="text-sm sm:text-base font-black text-[#1D1D1F] whitespace-nowrap">
                                    ₱{{ number_format($pData['revenue'], 2) }}
                                </div>
                                <div class="text-sm sm:text-sm font-bold {{ $textColor }} mt-0.5 whitespace-nowrap">
                                    {{ $share }}% share
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>

        <!-- Right 1 Col: Add-ons & Ancillary Revenue -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-3.5 sm:p-5 shadow-2xs space-y-4">
            <h3 class="text-sm sm:text-base font-extrabold text-[#1D1D1F]">Add-ons & Logistics</h3>
            
            <!-- Manila Carpool Van -->
            <div class="p-3.5 rounded-xl bg-[#F2F2F7] space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-sm text-[#1D1D1F]">Manila Carpool Van</span>
                    <span class="px-2 py-0.5 rounded text-sm font-extrabold bg-[#780000] text-white">₱1,200 / pax</span>
                </div>
                <div class="text-xl font-black text-[#780000]">
                    ₱{{ number_format($fin['carpool']['estimated_revenue'] ?? 0, 2) }}
                </div>
                <div class="text-sm text-[#6E6E73] flex items-center justify-between">
                    <span>{{ $fin['carpool']['pax_count'] ?? 0 }} divers transported</span>
                    <span>{{ $fin['carpool']['bookings_count'] ?? 0 }} bookings</span>
                </div>
            </div>

            <!-- Boat Dive Optional -->
            <div class="p-3.5 rounded-xl bg-[#F2F2F7] space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-sm text-[#1D1D1F]">Boat Dive Add-on</span>
                    <span class="px-2 py-0.5 rounded text-sm font-extrabold bg-[#00C3D0] text-white">₱600 / pax</span>
                </div>
                <div class="text-xl font-black text-[#00C3D0]">
                    ₱{{ number_format($fin['boat_dive']['estimated_revenue'] ?? 0, 2) }}
                </div>
                <div class="text-sm text-[#6E6E73] flex items-center justify-between">
                    <span>{{ $fin['boat_dive']['pax_count'] ?? 0 }} divers enrolled</span>
                    <span>{{ $fin['boat_dive']['bookings_count'] ?? 0 }} bookings</span>
                </div>
            </div>

            <!-- Dynamic Pricing Lift -->
            <div class="p-3.5 rounded-xl bg-[#F8EAEA] space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-sm text-[#780000]">Dynamic Pricing Net Lift</span>
                    <span class="text-sm font-bold text-[#780000]">{{ $fin['dynamic_pricing']['adjustments_count'] ?? 0 }} rules</span>
                </div>
                <div class="text-xl font-black text-[#780000]">
                    ₱{{ number_format($fin['dynamic_pricing']['net_lift'] ?? 0, 2) }}
                </div>
                <div class="text-sm text-[#8E8E93] flex items-center justify-between">
                    <span>Yield: +₱{{ number_format($fin['dynamic_pricing']['positive_yield'] ?? 0, 2) }}</span>
                    <span>Discount: -₱{{ number_format($fin['dynamic_pricing']['discounts_given'] ?? 0, 2) }}</span>
                </div>
            </div>
        </div>

    </div>

</div>
