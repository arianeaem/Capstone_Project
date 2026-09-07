@php
    $b = $data['bookings'] ?? [];
    $totalBookings = max(1, $b['total_bookings'] ?? 1);
    $totalDiscoveryPax = max(1, $b['swimmer_ability']['total'] ?? 1);
@endphp

<div class="space-y-6">

    <!-- Top Bookings KPI Summary -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-5 shadow-2xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 items-stretch gap-4 sm:gap-6 divide-y sm:divide-y-0 divide-[#E5E5EA]">
            
            <!-- Total Bookings -->
            <div class="px-2 sm:px-4 py-1">
                <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Total Bookings</span>
                <div class="text-xl sm:text-2xl font-extrabold text-[#1D1D1F] mt-0.5 break-words">
                    {{ number_format($b['total_bookings'] ?? 0) }}
                </div>
                <div class="text-xs text-[#8E8E93] flex items-center justify-between gap-2 mt-1">
                    <span>Confirmed: {{ $b['confirmed_bookings'] ?? 0 }}</span>
                    <span class="px-1.5 py-0.5 rounded text-[11px] font-bold shrink-0 {{ ($b['booking_delta'] ?? 0) >= 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                        {{ ($b['booking_delta'] ?? 0) >= 0 ? '+' : '' }}{{ $b['booking_delta'] ?? 0 }}%
                    </span>
                </div>
            </div>

            <!-- Total Divers (Headcount) -->
            <div class="relative px-2 sm:px-4 pt-3 sm:pt-1 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Total Divers (Headcount)</span>
                <div class="text-xl sm:text-2xl font-extrabold text-[#780000] mt-0.5 break-words">
                    {{ number_format($b['total_participants'] ?? 0) }} pax
                </div>
                <div class="text-xs text-[#8E8E93] flex items-center justify-between gap-2 mt-1">
                    <span>Confirmed: {{ $b['confirmed_participants'] ?? 0 }}</span>
                    <span>Avg {{ $totalBookings > 0 ? round(($b['total_participants'] ?? 0) / $totalBookings, 1) : 0 }}/bk</span>
                </div>
            </div>

            <!-- Conversion Rate -->
            <div class="relative px-2 sm:px-4 pt-3 sm:pt-1 py-1">
                <div class="hidden lg:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Deposit Conversion</span>
                <div class="text-xl sm:text-2xl font-extrabold text-emerald-700 mt-0.5 break-words">
                    {{ $b['conversion_rate'] ?? 0 }}%
                </div>
                <div class="text-xs text-[#8E8E93] flex items-center justify-between gap-2 mt-1">
                    <span>{{ $b['confirmed_bookings'] ?? 0 }} of {{ $b['total_bookings'] ?? 0 }} paid</span>
                    <span class="px-1.5 py-0.5 rounded text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 shrink-0">Healthy</span>
                </div>
            </div>

            <!-- Churn Rate -->
            <div class="relative px-2 sm:px-4 pt-3 sm:pt-1 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Drop-off & Churn</span>
                <div class="text-xl sm:text-2xl font-extrabold text-rose-700 mt-0.5 break-words">
                    {{ $b['cancellation_rate'] ?? 0 }}%
                </div>
                <div class="text-xs text-[#8E8E93] flex items-center justify-between gap-2 mt-1">
                    <span>{{ $b['cancellation_count'] ?? 0 }} Cancelled</span>
                    <span>{{ $b['reschedule_count'] ?? 0 }} Resched</span>
                </div>
            </div>

        </div>
    </div>

    <!-- Middle: Cohort Behaviors & Guest Demographics -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Cols: Group Sizes & Swimmer Comfort Distribution -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-[#E5E5EA] p-3.5 sm:p-5 shadow-2xs space-y-4">
            <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                <div>
                    <h3 class="text-sm sm:text-base font-extrabold text-[#1D1D1F]">Guest Cohort & Skill Demographics</h3>
                    <p class="text-xs text-[#6E6E73]">Breakdown of party size compositions and swimmer comfort levels.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Group Size Breakdown -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-[#6E6E73]">Party & Group Sizes</h4>
                    <div class="space-y-3 text-xs">
                        @php
                            $groups = [
                                ['label' => 'Solo Joiners (1 Diver)', 'count' => $b['group_sizes']['solo'] ?? 0, 'color' => 'bg-[#780000]'],
                                ['label' => 'Pairs & Duos (2 Divers)', 'count' => $b['group_sizes']['duo'] ?? 0, 'color' => 'bg-[#00C3D0]'],
                                ['label' => 'Small Groups (3-4 Divers)', 'count' => $b['group_sizes']['small_group'] ?? 0, 'color' => 'bg-[#D45D5D]'],
                                ['label' => 'Large Groups (5+ Divers)', 'count' => $b['group_sizes']['large_group'] ?? 0, 'color' => 'bg-[#2C2C2E]'],
                            ];
                        @endphp

                        @foreach($groups as $g)
                            @php $pct = round(($g['count'] / $totalBookings) * 100, 1); @endphp
                            <div class="space-y-1">
                                <div class="flex items-center justify-between font-semibold">
                                    <span class="text-[#1D1D1F]">{{ $g['label'] }}</span>
                                    <span class="text-[#6E6E73]">{{ $g['count'] }} ({{ $pct }}%)</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-[#E5E5EA] overflow-hidden">
                                    <div class="h-full {{ $g['color'] }} transition-all" style="width: {{ $pct }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Swimmer Ability in Discovery Class -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-[#6E6E73]">Discovery Water Comfort</h4>
                    <div class="space-y-3 text-xs">
                        @php
                            $skills = [
                                ['label' => 'Non-Swimmers (High coach ratio)', 'count' => $b['swimmer_ability']['non_swimmer'] ?? 0, 'color' => 'bg-[#780000]'],
                                ['label' => 'Casual Swimmers (Pool/Beach)', 'count' => $b['swimmer_ability']['casual_swimmer'] ?? 0, 'color' => 'bg-[#00C3D0]'],
                                ['label' => 'Confident Swimmers (Open Water)', 'count' => $b['swimmer_ability']['confident_swimmer'] ?? 0, 'color' => 'bg-emerald-600'],
                            ];
                        @endphp

                        @foreach($skills as $s)
                            @php $pct = round(($s['count'] / $totalDiscoveryPax) * 100, 1); @endphp
                            <div class="space-y-1">
                                <div class="flex items-center justify-between font-semibold">
                                    <span class="text-[#1D1D1F]">{{ $s['label'] }}</span>
                                    <span class="text-[#6E6E73]">{{ $s['count'] }} ({{ $pct }}%)</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-[#E5E5EA] overflow-hidden">
                                    <div class="h-full {{ $s['color'] }} transition-all" style="width: {{ $pct }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Right 1 Col: Booking Lead Time Distribution -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-3.5 sm:p-5 shadow-2xs space-y-4">
            <div class="border-b border-[#E5E5EA] pb-3">
                <h3 class="text-sm sm:text-base font-extrabold text-[#1D1D1F]">Booking Lead Times</h3>
                <p class="text-xs text-[#6E6E73]">How far in advance guests book reservations.</p>
            </div>

            <div class="space-y-3 text-xs">
                @php
                    $leads = [
                        ['label' => 'Last-minute (< 3 days)', 'count' => $b['lead_times']['under_3_days'] ?? 0, 'color' => 'bg-rose-600'],
                        ['label' => '4 – 7 Days before trip', 'count' => $b['lead_times']['4_to_7_days'] ?? 0, 'color' => 'bg-[#780000]'],
                        ['label' => '8 – 14 Days before trip', 'count' => $b['lead_times']['8_to_14_days'] ?? 0, 'color' => 'bg-[#00C3D0]'],
                        ['label' => '15 – 30 Days in advance', 'count' => $b['lead_times']['15_to_30_days'] ?? 0, 'color' => 'bg-indigo-600'],
                        ['label' => 'Over 30 Days in advance', 'count' => $b['lead_times']['over_30_days'] ?? 0, 'color' => 'bg-emerald-600'],
                    ];
                @endphp

                @foreach($leads as $l)
                    @php $pct = round(($l['count'] / $totalBookings) * 100, 1); @endphp
                    <div class="space-y-1">
                        <div class="flex items-center justify-between font-semibold">
                            <span class="text-[#1D1D1F]">{{ $l['label'] }}</span>
                            <span class="text-[#6E6E73]">{{ $l['count'] }} ({{ $pct }}%)</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-[#E5E5EA] overflow-hidden">
                            <div class="h-full {{ $l['color'] }} transition-all" style="width: {{ $pct }}%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    <!-- Bottom: Bookings & Diver Demographic Log -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] p-3.5 sm:p-5 shadow-2xs space-y-4">
        <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
            <div>
                <h3 class="text-sm sm:text-base font-extrabold text-[#1D1D1F]">Recent Bookings & Guest Cohort Log</h3>
                <p class="text-xs text-[#6E6E73]">Guest party sizes, assigned batch dates, and lead times in the selected period.</p>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-[#E5E5EA]">
            <table class="w-full text-left min-w-[900px]">
                <thead class="bg-[#FAFAFC] border-b border-[#E5E5EA] text-xs uppercase font-bold text-[#6E6E73]">
                    <tr>
                        <th class="p-4 pl-6">Booking # / Date</th>
                        <th class="p-4">Customer Name</th>
                        <th class="p-4">Class Package</th>
                        <th class="p-4 text-center">Party Size</th>
                        <th class="p-4">Batch Assignment</th>
                        <th class="p-4 pr-6 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E5EA]">
                    @forelse($b['bookings_list'] ?? [] as $bk)
                        <tr class="hover:bg-[#FAFAFC] transition-colors text-xs sm:text-sm">
                            <td class="p-4 pl-6 font-mono">
                                <span class="font-bold text-[#780000] block text-sm">{{ $bk->booking_number }}</span>
                                <span class="text-xs text-[#8E8E93]">{{ $bk->created_at?->format('M d, Y') }}</span>
                            </td>
                            <td class="p-4">
                                <div class="font-bold text-[#1D1D1F]">{{ $bk->contact_name }}</div>
                                <div class="text-xs text-[#6E6E73]">{{ $bk->contact_email }}</div>
                            </td>
                            <td class="p-4">
                                <span class="font-semibold text-[#1D1D1F] block">{{ ucfirst($bk->class_type) }}</span>
                                <span class="text-xs text-[#6E6E73]">{{ $bk->pickup_option === 'carpool' ? 'Carpool' : 'Own Transpo' }}</span>
                            </td>
                            <td class="p-4 text-center">
                                <span class="font-bold text-[#780000]">{{ $bk->participants->count() }} pax</span>
                            </td>
                            <td class="p-4">
                                @if($bk->batch)
                                    <span class="font-bold text-[#1D1D1F] block">Batch #{{ $bk->batch->id }}</span>
                                    <span class="text-xs text-[#8E8E93]">
                                        {{ \Carbon\Carbon::parse($bk->batch->start_date)->format('M d') }} – {{ \Carbon\Carbon::parse($bk->batch->end_date)->format('M d') }}
                                    </span>
                                @else
                                    <span class="text-xs text-[#8E8E93] italic">Unassigned</span>
                                @endif
                            </td>
                            <td class="p-4 pr-6 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $bk->status === 'confirmed' ? 'bg-emerald-50 text-emerald-700' : ($bk->status === 'completed' ? 'bg-sky-50 text-sky-700' : ($bk->status === 'pending_downpayment' ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700')) }}">
                                    {{ ucfirst(str_replace('_', ' ', $bk->status)) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-xs text-[#8E8E93]">No bookings found for the selected reporting period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
