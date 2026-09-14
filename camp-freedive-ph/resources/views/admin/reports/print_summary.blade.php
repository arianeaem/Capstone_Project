<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Executive Report Summary - Camp FreedivePH</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Outfit', 'Plus Jakarta Sans', sans-serif;
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
<body class="bg-[#F2F2F7] text-[#1D1D1F] p-6 sm:p-10" onload="window.print()">

    <!-- Official Report Document -->
    <div class="max-w-4xl mx-auto bg-white rounded-2xl border border-[#E5E5EA] p-8 sm:p-10 space-y-8">
        
        <!-- Header -->
        <div class="flex items-start justify-between border-b border-[#E5E5EA] pb-6">
            <div class="flex items-center gap-3.5">
                <img src="{{ asset('images/logo.png') }}" alt="Camp FreedivePH" class="w-12 h-12 rounded-full border border-[#E5E5EA] object-contain shrink-0">
                <div>
                    <h1 class="text-xl font-black text-[#1D1D1F] tracking-tight">Camp Freedive<span class="text-[#780000]">PH</span></h1>
                    <p class="text-sm font-bold uppercase tracking-wider text-[#6E6E73]">Executive & Operational Summary</p>
                </div>
            </div>
            <div class="text-right text-sm">
                <div class="font-extrabold text-[#780000]">{{ $range['label'] }}</div>
                <div class="text-[#8E8E93] text-sm">{{ $range['start']->format('M d, Y') }} to {{ $range['end']->format('M d, Y') }}</div>
                <div class="text-[#8E8E93] text-sm mt-1">Generated: {{ \Carbon\Carbon::now('Asia/Manila')->format('M d, Y h:i A') }}</div>
            </div>
        </div>

        <!-- 1. Executive Summary KPIs -->
        <div class="space-y-3">
            <h2 class="text-sm font-extrabold uppercase tracking-wider text-[#6E6E73]">1. Key Performance Indicators</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                @if($isOwner)
                    <div class="p-3.5 rounded-xl bg-[#F2F2F7] border border-[#E5E5EA]">
                        <span class="text-sm font-bold uppercase text-[#6E6E73] block">Net Revenue</span>
                        <span class="text-lg font-black text-[#780000] block mt-0.5">₱{{ number_format($data['financials']['net_revenue'] ?? 0, 2) }}</span>
                    </div>
                @endif
                <div class="p-3.5 rounded-xl bg-[#F2F2F7] border border-[#E5E5EA]">
                    <span class="text-sm font-bold uppercase text-[#6E6E73] block">Total Divers (Pax)</span>
                    <span class="text-lg font-black text-[#1D1D1F] block mt-0.5">{{ $data['bookings']['total_participants'] ?? 0 }} pax</span>
                </div>
                <div class="p-3.5 rounded-xl bg-[#F2F2F7] border border-[#E5E5EA]">
                    <span class="text-sm font-bold uppercase text-[#6E6E73] block">Average Occupancy</span>
                    <span class="text-lg font-black text-[#1D1D1F] block mt-0.5">{{ $data['operations']['avg_occupancy'] ?? 0 }}%</span>
                </div>
                <div class="p-3.5 rounded-xl bg-[#F2F2F7] border border-[#E5E5EA]">
                    <span class="text-sm font-bold uppercase text-[#6E6E73] block">Staffing Fulfillment</span>
                    <span class="text-lg font-black text-emerald-700 block mt-0.5">{{ $data['operations']['safety_compliance_rate'] ?? 100 }}%</span>
                </div>
            </div>
        </div>

        @if($isOwner)
            <!-- 2. Financial Breakdown (Owner Only) -->
            <div class="space-y-3">
                <h2 class="text-sm font-extrabold uppercase tracking-wider text-[#6E6E73]">2. Financial Performance & Course Mix</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm border border-[#E5E5EA] rounded-xl overflow-hidden">
                        <thead class="bg-[#F2F2F7] border-b border-[#E5E5EA] text-[#6E6E73] font-bold">
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
                            <tr class="bg-[#F2F2F7] font-extrabold text-[#1D1D1F]">
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

        <!-- 3. Operational Batches & Coach Staffing Summary -->
        <div class="space-y-3">
            <h2 class="text-sm font-extrabold uppercase tracking-wider text-[#6E6E73]">{{ $isOwner ? '3' : '2' }}. Operational Batches & Coach Staffing</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border border-[#E5E5EA] rounded-xl overflow-hidden">
                    <thead class="bg-[#F2F2F7] border-b border-[#E5E5EA] text-[#6E6E73] font-bold">
                        <tr>
                            <th class="p-2.5">Batch ID</th>
                            <th class="p-2.5">Schedule</th>
                            <th class="p-2.5">Package</th>
                            <th class="p-2.5 text-center">Divers</th>
                            <th class="p-2.5 text-center">Occupancy</th>
                            <th class="p-2.5">Assigned Coaches</th>
                            <th class="p-2.5 text-center">Coach Staffing</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E5EA]">
                        @forelse($data['operations']['batches_list'] ?? [] as $batch)
                            @php
                                $pax = $batch->total_participants_count;
                                $cap = $batch->max_capacity ?: 20;
                                $occ = $cap > 0 ? round(($pax / $cap) * 100) : 0;
                                $coachesCnt = $batch->assigned_coaches_count;
                                $recCoaches = $pax > 0 ? (int) ceil($pax / 4) : 0;
                            @endphp
                            <tr>
                                <td class="p-2.5 font-bold">{{ $batch->display_name }}</td>
                                <td class="p-2.5 text-[#6E6E73]">{{ \Carbon\Carbon::parse($batch->start_date)->format('M d') }} to {{ \Carbon\Carbon::parse($batch->end_date)->format('M d, Y') }}</td>
                                <td class="p-2.5 font-semibold">{{ ucfirst($batch->class_type ?? 'Discovery') }}</td>
                                <td class="p-2.5 text-center">{{ $pax }} / {{ $cap }}</td>
                                <td class="p-2.5 text-center font-bold {{ $occ >= 90 ? 'text-[#780000]' : '' }}">{{ $occ }}%</td>
                                <td class="p-2.5 text-sm">{{ $batch->coachAssignments->map(fn($ca) => $ca->coach?->name)->filter()->implode(', ') ?: 'None' }}</td>
                                <td class="p-2.5 text-center font-bold {{ $coachesCnt >= $recCoaches ? 'text-emerald-700' : 'text-amber-600' }}">
                                    {{ $coachesCnt }} Assigned (Rec: {{ $recCoaches }})
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
        <div class="pt-6 border-t border-[#E5E5EA] flex items-center justify-between text-sm text-[#8E8E93]">
            <span>Camp FreedivePH &copy; {{ date('Y') }} - Confidential Internal Report</span>
            <span>Generated by {{ $user->name }} ({{ ucfirst($user->role) }})</span>
        </div>

    </div>

</body>
</html>
