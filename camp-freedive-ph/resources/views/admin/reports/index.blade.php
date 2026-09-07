@extends('layouts.admin')

@section('title', 'Reports & Analytics - Camp FreedivePH')

@section('content')
<div class="space-y-6" x-data="{ activeTab: '{{ $activeTab }}' }">

    <!-- Page Header with Title, Description, and Actions on Right -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-5">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-[#1D1D1F] tracking-tight">Reports & Analytics</h1>
            <p class="text-xs sm:text-sm text-[#6E6E73] mt-0.5">
                Comprehensive performance metrics, financial yield, operational capacity, and diver insights.
            </p>
        </div>

        <!-- Right Side: Export CSV & Print Summary Buttons -->
        <div class="flex items-center gap-2.5 flex-wrap self-start lg:self-auto" x-data="{ exportOpen: false }">
            
            <!-- Export CSV Dropdown -->
            <div class="relative">
                <button type="button" 
                        @click="exportOpen = !exportOpen" 
                        class="btn-secondary px-3.5 py-2 text-xs font-bold flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-[#6E6E73]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    <span>Export CSV</span>
                    <svg class="w-3 h-3 text-[#8E8E93]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>

                <div x-show="exportOpen" 
                     @click.away="exportOpen = false" 
                     x-cloak 
                     class="absolute right-0 mt-2 w-52 bg-white rounded-xl border border-[#E5E5EA] p-1.5 shadow-xl z-30 space-y-1">
                    @if($isOwner)
                        <a href="{{ (auth()->user()->isOwner() ? route('owner.reports.export') : route('admin.reports.export')) . '?' . http_build_query(['type' => 'financials', 'preset' => $range['preset'], 'start_date' => $range['start']->format('Y-m-d'), 'end_date' => $range['end']->format('Y-m-d')]) }}" 
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-[#1D1D1F] hover:bg-[#F8EAEA] hover:text-[#780000] transition-colors">
                            <span>Financial Transactions</span>
                        </a>
                    @endif

                    <a href="{{ (auth()->user()->isOwner() ? route('owner.reports.export') : route('admin.reports.export')) . '?' . http_build_query(['type' => 'batches', 'preset' => $range['preset'], 'start_date' => $range['start']->format('Y-m-d'), 'end_date' => $range['end']->format('Y-m-d')]) }}" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-[#1D1D1F] hover:bg-[#F2F2F7] transition-colors">
                        <span>Batch Performance</span>
                    </a>

                    <a href="{{ (auth()->user()->isOwner() ? route('owner.reports.export') : route('admin.reports.export')) . '?' . http_build_query(['type' => 'divers', 'preset' => $range['preset'], 'start_date' => $range['start']->format('Y-m-d'), 'end_date' => $range['end']->format('Y-m-d')]) }}" 
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-[#1D1D1F] hover:bg-[#F2F2F7] transition-colors">
                        <span>Diver Roster</span>
                    </a>
                </div>
            </div>

            <!-- Print / PDF Summary Button -->
            <a href="{{ (auth()->user()->isOwner() ? route('owner.reports.print') : route('admin.reports.print')) . '?' . http_build_query(['preset' => $range['preset'], 'start_date' => $range['start']->format('Y-m-d'), 'end_date' => $range['end']->format('Y-m-d')]) }}" 
               target="_blank" 
               class="btn-secondary px-3.5 py-2 text-xs font-bold flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-[#6E6E73]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                <span>Print Summary</span>
            </a>

        </div>
    </div>

    <!-- Global Date Range & Filter Bar -->
    @include('admin.reports.partials.filter_bar')

    <!-- Interactive Analytics Tab Navigation (Underline #780000 on active, No Icons) -->
    <div class="border-b border-[#E5E5EA] flex items-center gap-6 overflow-x-auto no-scrollbar">
        @if($isOwner)
            <button type="button" 
                    @click="activeTab = 'financial'"
                    class="pb-3 text-xs sm:text-sm transition-all border-b-2 whitespace-nowrap"
                    :class="activeTab === 'financial' ? 'border-[#780000] text-[#780000] font-bold' : 'border-transparent text-[#6E6E73] hover:text-[#1D1D1F] hover:border-[#D1D1D6] font-semibold'">
                Financial & Revenue
            </button>
        @endif

        <button type="button" 
                @click="activeTab = 'bookings'"
                class="pb-3 text-xs sm:text-sm transition-all border-b-2 whitespace-nowrap"
                :class="activeTab === 'bookings' ? 'border-[#780000] text-[#780000] font-bold' : 'border-transparent text-[#6E6E73] hover:text-[#1D1D1F] hover:border-[#D1D1D6] font-semibold'">
            Bookings & Demographics
        </button>

        <button type="button" 
                @click="activeTab = 'operations'"
                class="pb-3 text-xs sm:text-sm transition-all border-b-2 whitespace-nowrap"
                :class="activeTab === 'operations' ? 'border-[#780000] text-[#780000] font-bold' : 'border-transparent text-[#6E6E73] hover:text-[#1D1D1F] hover:border-[#D1D1D6] font-semibold'">
            Batch Capacity & Coaches
        </button>
    </div>

    <!-- Tab Content Panes (Consistent Container Structure) -->
    <div class="w-full">
        <!-- Tab 1: Financial Analytics (Owner Exclusive) -->
        @if($isOwner)
            <div x-show="activeTab === 'financial'" x-cloak class="w-full transition-all">
                @include('admin.reports.partials.financial_tab')
            </div>
        @endif

        <!-- Tab 2: Bookings & Demographics -->
        <div x-show="activeTab === 'bookings'" x-cloak class="w-full transition-all">
            @include('admin.reports.partials.bookings_tab')
        </div>

        <!-- Tab 3: Batch Capacity & Coaches -->
        <div x-show="activeTab === 'operations'" x-cloak class="w-full transition-all">
            @include('admin.reports.partials.operations_tab')
        </div>
    </div>

</div>
@endsection
