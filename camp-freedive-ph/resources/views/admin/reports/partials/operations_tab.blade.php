@php
    $op = $data['operations'] ?? [];
    $co = $data['coaches'] ?? [];
@endphp

<div class="space-y-6">

    <!-- Top Operational KPI Summary: Separator matching Payments Module -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] p-4 sm:p-5 shadow-2xs">
        <div class="grid grid-cols-2 lg:grid-cols-4 items-center gap-y-3">
            
            <!-- Average Occupancy -->
            <div class="px-4 py-1">
                <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Average Occupancy</span>
                <div class="text-2xl font-extrabold text-[#1D1D1F] mt-0.5">
                    {{ $op['avg_occupancy'] ?? 0 }}%
                </div>
                <div class="text-xs text-[#8E8E93] flex items-center justify-between mt-0.5">
                    <span>{{ $op['total_booked_pax'] ?? 0 }} of {{ $op['total_capacity_slots'] ?? 0 }} slots</span>
                    <span class="font-bold text-[#780000]">{{ $op['total_batches'] ?? 0 }} batches</span>
                </div>
            </div>

            <!-- Weekend vs Weekday Utilization -->
            <div class="relative px-4 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Weekend vs Weekday</span>
                <div class="text-2xl font-extrabold text-[#780000] mt-0.5">
                    {{ $op['weekend_occupancy'] ?? 0 }}% <span class="text-xs font-semibold text-[#8E8E93]">Weekend</span>
                </div>
                <div class="text-xs text-[#8E8E93] flex items-center justify-between mt-0.5">
                    <span>Weekday Avg: {{ $op['weekday_occupancy'] ?? 0 }}%</span>
                    <span class="font-semibold text-emerald-700">Peak Demand</span>
                </div>
            </div>

            <!-- Safety Ratio Compliance -->
            <div class="relative px-4 py-1">
                <div class="hidden lg:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Safety Ratio Adherence</span>
                <div class="text-2xl font-extrabold mt-0.5 {{ ($op['safety_compliance_rate'] ?? 100) >= 95 ? 'text-emerald-700' : 'text-amber-600' }}">
                    {{ $op['safety_compliance_rate'] ?? 100 }}%
                </div>
                <div class="text-xs text-[#8E8E93] flex items-center justify-between mt-0.5">
                    <span>Max 1 Coach : 4 Students</span>
                    <span class="font-semibold text-emerald-700">Standard</span>
                </div>
            </div>

            <!-- Coach Roster Output -->
            <div class="relative px-4 py-1">
                <div class="hidden sm:block absolute left-0 top-2 bottom-2 w-px bg-[#E5E5EA]"></div>
                <span class="text-xs font-bold text-[#6E6E73] uppercase tracking-wider block">Coach Roster Output</span>
                <div class="text-2xl font-extrabold text-[#00C3D0] mt-0.5">
                    {{ $co['total_assignments_period'] ?? 0 }} <span class="text-xs font-semibold text-[#8E8E93]">shifts</span>
                </div>
                <div class="text-xs text-[#8E8E93] flex items-center justify-between mt-0.5">
                    <span>{{ $co['total_active_coaches'] ?? 0 }} active coaches</span>
                    <span>{{ ($co['total_assignments_period'] ?? 0) * 2 }} dive days</span>
                </div>
            </div>

        </div>
    </div>

    <!-- Middle: Coach Workload Leaderboard & Operational Highlights -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Coach Assignments Distribution (Styled like Booking List Table) -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-[#E5E5EA] p-5 sm:p-6 shadow-2xs space-y-4">
            <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                <div>
                    <h3 class="text-sm sm:text-base font-extrabold text-[#1D1D1F]">Coach Workload & Shift Allocation</h3>
                    <p class="text-xs text-[#6E6E73]">Total trips and student coverage assigned to each coach in the selected period.</p>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-[#E5E5EA]">
                <table class="w-full text-left min-w-[550px]">
                    <thead class="bg-[#FAFAFC] border-b border-[#E5E5EA] text-xs uppercase font-bold text-[#6E6E73]">
                        <tr>
                            <th class="p-3.5 pl-5">Coach Name</th>
                            <th class="p-3.5 text-center">Status</th>
                            <th class="p-3.5 text-center">Batches Assigned</th>
                            <th class="p-3.5 text-center">Est. Dive Days</th>
                            <th class="p-3.5 pr-5 text-center">Release Requests</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E5EA]">
                        @forelse($co['coaches'] ?? [] as $coach)
                            <tr class="hover:bg-[#FAFAFC] transition-colors text-xs sm:text-sm">
                                <td class="p-3.5 pl-5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-full bg-[#F8EAEA] text-[#780000] font-bold flex items-center justify-center shrink-0 border border-[#F1D5D5] text-xs">
                                            {{ substr($coach['name'], 0, 1) }}
                                        </div>
                                        <div>
                                            <span class="font-bold text-[#1D1D1F] block">{{ $coach['name'] }}</span>
                                            <span class="text-xs text-[#8E8E93]">{{ $coach['email'] }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3.5 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $coach['status'] === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-600' }}">
                                        {{ ucfirst($coach['status']) }}
                                    </span>
                                </td>
                                <td class="p-3.5 text-center font-bold text-[#1D1D1F]">{{ $coach['assignments_count'] }} trips</td>
                                <td class="p-3.5 text-center font-medium text-[#6E6E73]">{{ $coach['estimated_dive_days'] }} days</td>
                                <td class="p-3.5 pr-5 text-center">
                                    <span class="font-bold {{ $coach['releases_count'] > 0 ? 'text-rose-600' : 'text-[#8E8E93]' }}">
                                        {{ $coach['releases_count'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-xs text-[#8E8E93]">No coaches found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Capacity Bottlenecks & Highlights -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-5 sm:p-6 shadow-2xs space-y-4">
            <h3 class="text-sm sm:text-base font-extrabold text-[#1D1D1F] border-b border-[#E5E5EA] pb-3">Operational Highlights</h3>
            
            <div class="space-y-3 text-xs">
                <div class="p-3.5 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] flex items-center justify-between">
                    <div>
                        <span class="font-bold text-[#1D1D1F] block">Batches at Full Capacity (≥90%)</span>
                        <span class="text-[11px] text-[#6E6E73]">High demand sell-outs</span>
                    </div>
                    <span class="text-base font-extrabold text-[#780000]">{{ $op['full_capacity_batches'] ?? 0 }}</span>
                </div>

                <div class="p-3.5 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] flex items-center justify-between">
                    <div>
                        <span class="font-bold text-[#1D1D1F] block">Completed Trips</span>
                        <span class="text-[11px] text-[#6E6E73]">Successfully concluded</span>
                    </div>
                    <span class="text-base font-extrabold text-emerald-700">{{ $op['completed_batches'] ?? 0 }}</span>
                </div>

                <div class="p-3.5 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] flex items-center justify-between">
                    <div>
                        <span class="font-bold text-[#1D1D1F] block">Active / Upcoming</span>
                        <span class="text-[11px] text-[#6E6E73]">Currently open for booking</span>
                    </div>
                    <span class="text-base font-extrabold text-[#00C3D0]">{{ $op['active_batches'] ?? 0 }}</span>
                </div>

                <div class="p-3.5 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] flex items-center justify-between">
                    <div>
                        <span class="font-bold text-[#1D1D1F] block">Cancelled Trips</span>
                        <span class="text-[11px] text-[#6E6E73]">Weather / Admin cancelled</span>
                    </div>
                    <span class="text-base font-extrabold text-rose-700">{{ $op['cancelled_batches'] ?? 0 }}</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Batch Runways & Performance List (Styled like Booking List Table) -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] p-5 sm:p-6 shadow-2xs space-y-4">
        <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
            <div>
                <h3 class="text-sm sm:text-base font-extrabold text-[#1D1D1F]">Batch Runway & Roster Log</h3>
                <p class="text-xs text-[#6E6E73]">Trip-by-trip occupancy, distinct coach allocation, and safety ratio tracking.</p>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-[#E5E5EA]">
            <table class="w-full text-left min-w-[900px]">
                <thead class="bg-[#FAFAFC] border-b border-[#E5E5EA] text-xs uppercase font-bold text-[#6E6E73]">
                    <tr>
                        <th class="p-4 pl-6">Batch / Dates</th>
                        <th class="p-4">Class Package</th>
                        <th class="p-4 text-center">Divers Booked</th>
                        <th class="p-4 min-w-[150px]">Occupancy Rate</th>
                        <th class="p-4 min-w-[200px]">Assigned Coaches</th>
                        <th class="p-4 text-center">Safety Ratio</th>
                        <th class="p-4 pr-6 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E5EA]">
                    @forelse($op['batches_list'] ?? [] as $batch)
                        @php
                            $pax = $batch->total_participants_count;
                            $cap = $batch->max_capacity ?: 20;
                            $occ = $cap > 0 ? round(($pax / $cap) * 100) : 0;
                            $distinctCoaches = $batch->coachAssignments->pluck('coach')->unique('id')->filter();
                            $coachesCnt = $distinctCoaches->count();
                            $required = (int) ceil($pax / 4);
                            $isRatioGood = ($pax === 0 || $coachesCnt >= $required);
                        @endphp
                        <tr class="hover:bg-[#FAFAFC] transition-colors text-xs sm:text-sm">
                            <td class="p-4 pl-6 font-mono">
                                <span class="font-bold text-[#780000] block text-sm">Batch #{{ $batch->id }}</span>
                                <span class="text-xs text-[#8E8E93]">
                                    {{ \Carbon\Carbon::parse($batch->start_date)->format('M d') }} – {{ \Carbon\Carbon::parse($batch->end_date)->format('M d, Y') }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="font-semibold text-[#1D1D1F] block">{{ ucfirst($batch->class_type ?? 'Discovery') }}</span>
                            </td>
                            <td class="p-4 text-center font-bold text-[#1D1D1F]">{{ $pax }} / {{ $cap }}</td>
                            <td class="p-4 min-w-[150px]">
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold {{ $occ >= 90 ? 'text-[#780000]' : 'text-[#3A3A3C]' }}">{{ $occ }}%</span>
                                    </div>
                                    <div class="w-full h-1.5 rounded-full bg-[#E5E5EA] overflow-hidden">
                                        <div class="h-full {{ $occ >= 90 ? 'bg-[#780000]' : ($occ >= 50 ? 'bg-[#00C3D0]' : 'bg-gray-400') }}" style="width: {{ min(100, $occ) }}%;"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 min-w-[200px]">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    @forelse($distinctCoaches as $coach)
                                        <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-[#F2F2F7] text-[#1D1D1F] border border-[#E5E5EA]">
                                            {{ $coach->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-rose-600 font-semibold">No coach assigned</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="p-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $isRatioGood ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                    {{ $coachesCnt > 0 ? round($pax / $coachesCnt, 1) . ' : 1' : ($pax > 0 ? 'Exceeded' : 'OK') }}
                                </span>
                            </td>
                            <td class="p-4 pr-6 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $batch->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : ($batch->status === 'confirmed' ? 'bg-sky-50 text-sky-700' : ($batch->status === 'rescheduled' ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-700')) }}">
                                    {{ ucfirst($batch->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-6 text-center text-xs text-[#8E8E93]">No batches found for the selected reporting period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
