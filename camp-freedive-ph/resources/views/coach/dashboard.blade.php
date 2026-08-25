@extends('layouts.admin')

@section('title', 'Coach Portal | Camp FreedivePH')

@section('content')
<div class="space-y-8">
    
    <!-- Top Greeting Banner -->
    <div class="bg-gradient-to-r from-[#008E98] to-[#004D54] rounded-xl p-6 sm:p-8 text-white shadow-md flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-[#E0F9FB]">Instructor Dashboard</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold mt-1">Hello, {{ $coach->name }}!</h1>
            <div class="text-sm text-[#E0F9FB]/90 mt-1">
                <div>Certified Freediving Instructor</div>
                <div class="text-xs text-[#E0F9FB]/75">Anilao, Mabini Base</div>
            </div>
        </div>

        <div class="bg-white/10 px-4 py-3 rounded-xl border border-white/20 text-xs sm:text-sm">
            <span class="block text-[#E0F9FB] text-xs">Assigned Email:</span>
            <span class="font-bold text-white">{{ $coach->email }}</span>
        </div>
    </div>

    <!-- Coach Notice on Profile Editing -->
    <div class="p-4 bg-[#F2F2F7] rounded-xl border border-[#E5E5EA] text-xs text-[#6E6E73] flex items-center gap-3">
        <svg class="w-5 h-5 text-[#6E6E73] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
        <span>
            <strong>Account Note:</strong> Profile and schedule changes are managed directly by Camp Administrators and Owner. If your contact details or availability change, please notify camp staff.
        </span>
    </div>

    <!-- Upcoming Batches & Assigned Students -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 sm:p-8 shadow-sm space-y-6">
        <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-4">
            <div>
                <h3 class="text-lg font-bold text-[#1D1D1F]">Upcoming Camp Batches & Students</h3>
                <p class="text-xs text-[#6E6E73]">2D1N dive batches scheduled at Mabini camp.</p>
            </div>
            <span class="text-xs font-bold px-3 py-1 rounded-full bg-[#ECFDF5] text-[#065F46] border border-[#A7F3D0]">
                Active Instructor
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($upcomingBookings as $b)
            <div class="p-5 rounded-xl border border-[#E5E5EA] bg-[#FAFAFC] space-y-3">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-sm text-[#780000]">{{ $b->formatted_class_type }}</span>
                    <span class="text-xs font-mono font-bold text-[#6E6E73]">{{ $b->booking_number }}</span>
                </div>

                <div class="text-xs text-[#1D1D1F] space-y-1">
                    <div><strong>Trip Dates:</strong> {{ $b->start_date->format('M d, Y') }} - {{ $b->end_date->format('M d, Y') }}</div>
                    <div><strong>Lead Booker:</strong> {{ $b->contact_name }} ({{ $b->contact_phone }})</div>
                    <div><strong>Boat Dive:</strong> {{ $b->boat_dive ? 'Yes (Sanctuary boat dive)' : 'Shore entry dive' }}</div>
                </div>

                <!-- Students in this batch -->
                <div class="pt-2 border-t border-[#E5E5EA]">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#6E6E73] block mb-1.5">Students in Session:</span>
                    <div class="space-y-1 text-xs">
                        @foreach($b->participants as $p)
                        <div class="p-2 bg-white rounded-lg border border-[#E5E5EA] flex items-center justify-between">
                            <div>
                                <span class="font-bold text-[#1D1D1F]">{{ $p->name }}</span>
                                <span class="text-[#6E6E73]">({{ $p->age }} y/o)</span>
                                @if($p->health_condition && $p->health_condition !== 'None' && $p->health_condition !== 'None declared')
                                    <div class="text-xs text-[#92400E] font-semibold">Health: {{ $p->health_condition }}</div>
                                @endif
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded font-semibold bg-[#F2F2F7] text-[#1D1D1F]">
                                {{ ucfirst(str_replace('_', ' ', $p->swimmer_status ?: 'Swimmer')) }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-2 text-center py-10 text-[#6E6E73]">
                No upcoming batches scheduled.
            </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
