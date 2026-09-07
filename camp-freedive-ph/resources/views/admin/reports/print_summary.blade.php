<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Executive Report Summary - Camp FreedivePH</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            @page {
                margin: 1.5cm;
                size: portrait;
            }
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body class="bg-[#FAFAFC] text-[#1D1D1F] p-6 sm:p-10">

    <!-- Action Bar (Hidden on Print) -->
    <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print bg-white p-4 rounded-xl border border-[#E5E5EA] shadow-sm">
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold text-[#6E6E73]">Print Preview:</span>
            <span class="text-xs font-semibold text-[#1D1D1F]">{{ $range['label'] }}</span>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.close()" class="px-3 py-1.5 rounded-lg border border-[#E5E5EA] text-xs font-semibold text-[#6E6E73] hover:bg-[#F2F2F7]">Close</button>
            <button onclick="window.print()" class="px-4 py-1.5 rounded-lg bg-[#780000] text-white text-xs font-bold hover:bg-[#5C0000] shadow-sm flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                <span>Print / Save as PDF</span>
            </button>
        </div>
    </div>

    <!-- Official Report Document -->
    <div class="max-w-4xl mx-auto bg-white rounded-2xl border border-[#E5E5EA] p-8 sm:p-10 shadow-sm space-y-8">
        
        <!-- Header -->
        <div class="flex items-start justify-between border-b border-[#E5E5EA] pb-6">
            <div class="flex items-center gap-3.5">
                <img src="{{ asset('images/logo.png') }}" alt="Camp FreedivePH" class="w-12 h-12 rounded-full border border-[#E5E5EA] object-contain shrink-0">
                <div>
                    <h1 class="text-xl font-black text-[#1D1D1F] tracking-tight">Camp Freedive<span class="text-[#780000]">PH</span></h1>
                    <p class="text-xs font-bold uppercase tracking-wider text-[#6E6E73]">Executive & Operational Summary</p>
                </div>
            </div>
            <div class="text-right text-xs">
                <div class="font-extrabold text-[#780000]">{{ $range['label'] }}</div>
                <div class="text-[#8E8E93] text-[11px]">{{ $range['start']->format('M d, Y') }} – {{ $range['end']->format('M d, Y') }}</div>
                <div class="text-[#8E8E93] text-[10px] mt-1">Generated: {{ \Carbon\Carbon::now('Asia/Manila')->format('M d, Y h:i A') }}</div>
            </div>
        </div>

        <!-- 1. Executive Summary KPIs -->
        <div class="space-y-3">
            <h2 class="text-xs font-extrabold uppercase tracking-wider text-[#6E6E73]">1. Key Performance Indicators</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                @if($isOwner)
                    <div class="p-3.5 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA]">
                        <span class="text-[10px] font-bold uppercase text-[#6E6E73] block">Net Revenue</span>
                        <span class="text-lg font-black text-[#780000] block mt-0.5">₱{{ number_format($data['financials']['net_revenue'] ?? 0, 2) }}</span>
                    </div>
                @endif
                <div class="p-3.5 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA]">
                    <span class="text-[10px] font-bold uppercase text-[#6E6E73] block">Total Divers (Pax)</span>
                    <span class="text-lg font-black text-[#1D1D1F] block mt-0.5">{{ $data['bookings']['total_participants'] ?? 0 }} pax</span>
                </div>
                <div class="p-3.5 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA]">
                    <span class="text-[10px] font-bold uppercase text-[#6E6E73] block">Average Occupancy</span>
                    <span class="text-lg font-black text-[#1D1D1F] block mt-0.5">{{ $data['operations']['avg_occupancy'] ?? 0 }}%</span>
                </div>
                <div class="p-3.5 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA]">
                    <span class="text-[10px] font-bold uppercase text-[#6E6E73] block">Safety Ratio Compliance</span>
                    <span class="text-lg font-black text-emerald-700 block mt-0.5">{{ $data['operations']['safety_compliance_rate'] ?? 100 }}%</span>
                </div>
            </div>
        </div>

        @if($isOwner)
            <!-- 2. Financial Breakdown (Owner Only) -->
            <div class="space-y-3">
                <h2 class="text-xs font-extrabold uppercase tracking-wider text-[#6E6E73]">2. Financial Performance & Course Mix</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border border-[#E5E5EA] rounded-xl overflow-hidden">
                        <thead class="bg-[#FAFAFC] border-b border-[#E5E5EA] text-[#6E6E73] font-bold">
                            <tr>
                                <th class="p-2.5">Course Package</th>
                                <th class="p-2.5 text-center">Bookings</th>
                                <th class="p-2.5 text-center">Divers</th>
                                <th class="p-2.5 text-right">Revenue (PHP)</th>
                                <th class="p-2.5 text-right">Revenue Share</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5E5EA]">
                            @foreach($data['financials']['packages'] ?? [] as $key => $pkg)
                                <tr>
                                    <td class="p-2.5 font-bold text-[#1D1D1F]">{{ $pkg['name'] }}</td>
                                    <td class="p-2.5 text-center">{{ $pkg['bookings_count'] }}</td>
                                    <td class="p-2.5 text-center">{{ $pkg['pax_count'] }} pax</td>
                                    <td class="p-2.5 text-right font-extrabold">₱{{ number_format($pkg['revenue'], 2) }}</td>
                                    <td class="p-2.5 text-right font-bold text-[#780000]">{{ $pkg['share'] }}%</td>
                                </tr>
                            @endforeach
                            <tr class="bg-[#FAFAFC] font-extrabold text-[#1D1D1F]">
                                <td class="p-2.5">Total Gross Collections</td>
                                <td class="p-2.5 text-center">{{ $data['bookings']['total_bookings'] ?? 0 }}</td>
                                <td class="p-2.5 text-center">{{ $data['bookings']['total_participants'] ?? 0 }} pax</td>
                                <td class="p-2.5 text-right text-[#780000]">₱{{ number_format($data['financials']['gross_revenue'] ?? 0, 2) }}</td>
                                <td class="p-2.5 text-right">100.0%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- 3. Operational Batches & Safety Summary -->
        <div class="space-y-3">
            <h2 class="text-xs font-extrabold uppercase tracking-wider text-[#6E6E73]">{{ $isOwner ? '3' : '2' }}. Operational Batches & Capacity Utilization</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border border-[#E5E5EA] rounded-xl overflow-hidden">
                    <thead class="bg-[#FAFAFC] border-b border-[#E5E5EA] text-[#6E6E73] font-bold">
                        <tr>
                            <th class="p-2.5">Batch ID</th>
                            <th class="p-2.5">Schedule</th>
                            <th class="p-2.5">Package</th>
                            <th class="p-2.5 text-center">Divers</th>
                            <th class="p-2.5 text-center">Occupancy</th>
                            <th class="p-2.5">Assigned Coaches</th>
                            <th class="p-2.5 text-center">Safety Ratio</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E5EA]">
                        @forelse($data['operations']['batches_list'] ?? [] as $batch)
                            @php
                                $pax = $batch->total_participants_count;
                                $cap = $batch->max_capacity ?: 20;
                                $occ = $cap > 0 ? round(($pax / $cap) * 100) : 0;
                                $coachesCnt = $batch->assigned_coaches_count;
                            @endphp
                            <tr>
                                <td class="p-2.5 font-bold">Batch #{{ $batch->id }}</td>
                                <td class="p-2.5 text-[#6E6E73]">{{ \Carbon\Carbon::parse($batch->start_date)->format('M d') }} – {{ \Carbon\Carbon::parse($batch->end_date)->format('M d, Y') }}</td>
                                <td class="p-2.5 font-semibold">{{ ucfirst($batch->class_type ?? 'Discovery') }}</td>
                                <td class="p-2.5 text-center">{{ $pax }} / {{ $cap }}</td>
                                <td class="p-2.5 text-center font-bold {{ $occ >= 90 ? 'text-[#780000]' : '' }}">{{ $occ }}%</td>
                                <td class="p-2.5 text-[11px]">{{ $batch->coachAssignments->map(fn($ca) => $ca->coach?->name)->filter()->implode(', ') ?: 'None' }}</td>
                                <td class="p-2.5 text-center font-bold {{ $coachesCnt > 0 && ($pax / $coachesCnt) <= 4 ? 'text-emerald-700' : 'text-rose-600' }}">
                                    {{ $coachesCnt > 0 ? round($pax / $coachesCnt, 1) . ' : 1' : 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-4 text-center text-[#8E8E93]">No batches scheduled during this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer Signoff -->
        <div class="pt-6 border-t border-[#E5E5EA] flex items-center justify-between text-[11px] text-[#8E8E93]">
            <span>Camp FreedivePH &copy; {{ date('Y') }} - Confidential Internal Report</span>
            <span>Generated by {{ $user->name }} ({{ ucfirst($user->role) }})</span>
        </div>

    </div>

</body>
</html>
