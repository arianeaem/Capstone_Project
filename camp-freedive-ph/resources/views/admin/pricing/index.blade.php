@extends('layouts.admin')

@section('title', 'Dynamic Pricing Rules | Camp FreedivePH')

@section('content')
<div class="space-y-6 text-sm" x-data="{ deleteModal: false, deleteUrl: '', ruleName: '', triggeredCount: 0 }">
    
    <!-- Top Header & Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-5">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight">Dynamic Pricing Management</h1>
            <p class="text-xs sm:text-sm text-[#6E6E73] mt-1">
                Configure demand, seasonality, and lead-time pricing rules to optimize camp utilization and drive off-peak bookings.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.pricing.create') }}" 
               class="btn-primary px-5 py-2.5 text-xs sm:text-sm font-bold flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Add Pricing Rule</span>
            </a>
        </div>
    </div>

    <!-- Metrics Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
        <div class="p-4 rounded-2xl bg-white border border-[#E5E5EA] shadow-2xs space-y-1">
            <div class="text-xs font-semibold text-[#6E6E73] uppercase tracking-wider">Total Rules</div>
            <div class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F]">{{ number_format($totalRules) }}</div>
            <div class="text-[11px] text-[#6E6E73]">Configured pricing rules</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-[#E5E5EA] shadow-2xs space-y-1">
            <div class="text-xs font-semibold text-[#6E6E73] uppercase tracking-wider">Active Rules</div>
            <div class="text-2xl sm:text-3xl font-extrabold text-emerald-700">{{ number_format($activeRules) }}</div>
            <div class="text-[11px] text-emerald-600 font-medium">Affecting live booking engine</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-[#E5E5EA] shadow-2xs space-y-1">
            <div class="text-xs font-semibold text-[#6E6E73] uppercase tracking-wider">Bookings Triggered</div>
            <div class="text-2xl sm:text-3xl font-extrabold text-[#780000]">{{ number_format($totalTriggered) }}</div>
            <div class="text-[11px] text-[#6E6E73]">Historical reservations affected</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-[#E5E5EA] shadow-2xs space-y-1">
            <div class="text-xs font-semibold text-[#6E6E73] uppercase tracking-wider">Net Price Delta</div>
            <div class="text-2xl sm:text-3xl font-extrabold {{ $netRevenueImpact >= 0 ? 'text-[#1D1D1F]' : 'text-rose-700' }}">
                {{ $netRevenueImpact >= 0 ? '+' : '−' }}₱{{ number_format(abs($netRevenueImpact), 2) }}
            </div>
            <div class="text-[11px] text-[#6E6E73]">Cumulative discount/surge volume</div>
        </div>
    </div>

    <!-- Filters & Sort Bar -->
    <div class="bg-white rounded-2xl border border-[#E5E5EA] p-4 shadow-2xs">
        <form method="GET" action="{{ route('admin.pricing.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 items-end">
            <!-- Rule Type Filter -->
            <div>
                <label class="block text-xs font-bold text-[#1D1D1F] mb-1">Rule Type</label>
                <select name="rule_type" onchange="this.form.submit()" class="w-full text-xs rounded-xl border border-[#D1D1D6] px-3 py-2 bg-[#FAFAFC] focus:bg-white focus:border-[#780000] focus:ring-1 focus:ring-[#780000]">
                    <option value="all" {{ request('rule_type') === 'all' || !request('rule_type') ? 'selected' : '' }}>All Rule Types</option>
                    <option value="demand" {{ request('rule_type') === 'demand' ? 'selected' : '' }}>Demand Level</option>
                    <option value="seasonality" {{ request('rule_type') === 'seasonality' ? 'selected' : '' }}>Seasonality</option>
                    <option value="lead_time" {{ request('rule_type') === 'lead_time' ? 'selected' : '' }}>Lead Time</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-xs font-bold text-[#1D1D1F] mb-1">Status</label>
                <select name="status" onchange="this.form.submit()" class="w-full text-xs rounded-xl border border-[#D1D1D6] px-3 py-2 bg-[#FAFAFC] focus:bg-white focus:border-[#780000] focus:ring-1 focus:ring-[#780000]">
                    <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                </select>
            </div>

            <!-- Applies To Filter -->
            <div>
                <label class="block text-xs font-bold text-[#1D1D1F] mb-1">Applies To</label>
                <select name="applies_to" onchange="this.form.submit()" class="w-full text-xs rounded-xl border border-[#D1D1D6] px-3 py-2 bg-[#FAFAFC] focus:bg-white focus:border-[#780000] focus:ring-1 focus:ring-[#780000]">
                    <option value="all" {{ request('applies_to') === 'all' || !request('applies_to') ? 'selected' : '' }}>All Classes</option>
                    <option value="discovery" {{ request('applies_to') === 'discovery' ? 'selected' : '' }}>Discovery Class</option>
                    <option value="fundive" {{ request('applies_to') === 'fundive' ? 'selected' : '' }}>Fundive</option>
                    <option value="refinement" {{ request('applies_to') === 'refinement' ? 'selected' : '' }}>Refinement</option>
                </select>
            </div>

            <!-- Sort By -->
            <div>
                <label class="block text-xs font-bold text-[#1D1D1F] mb-1">Sort Order</label>
                <select name="sort" onchange="this.form.submit()" class="w-full text-xs rounded-xl border border-[#D1D1D6] px-3 py-2 bg-[#FAFAFC] focus:bg-white focus:border-[#780000] focus:ring-1 focus:ring-[#780000]">
                    <option value="priority" {{ request('sort', 'priority') === 'priority' ? 'selected' : '' }}>By Priority (Execution Order)</option>
                    <option value="triggered" {{ request('sort') === 'triggered' ? 'selected' : '' }}>Most Triggered Bookings</option>
                    <option value="recent" {{ request('sort') === 'recent' ? 'selected' : '' }}>Most Recently Created</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Pricing Rules Table -->
    <div class="bg-white rounded-2xl border border-[#E5E5EA] overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-[#E5E5EA] bg-[#FAFAFC] text-[11px] font-bold text-[#6E6E73] uppercase tracking-wider">
                        <th class="py-3 px-4">Priority</th>
                        <th class="py-3 px-4">Rule Name</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Condition</th>
                        <th class="py-3 px-4">Adjustment</th>
                        <th class="py-3 px-4">Applies To</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center">Bookings Triggered</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E5EA]">
                    @forelse($rules as $rule)
                    <tr class="hover:bg-[#F2F2F7]/50 transition-colors {{ $rule->status === 'inactive' ? 'opacity-65' : '' }}">
                        <!-- Priority -->
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#F2F2F7] text-xs font-bold text-[#1D1D1F]">
                                {{ $rule->priority }}
                            </span>
                        </td>

                        <!-- Rule Name & Description -->
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-[#1D1D1F]">{{ $rule->name }}</div>
                            @if($rule->description)
                                <div class="text-xs text-[#6E6E73] truncate max-w-xs">{{ $rule->description }}</div>
                            @endif
                        </td>

                        <!-- Rule Type Badge -->
                        <td class="py-3.5 px-4">
                            @if($rule->rule_type === 'demand')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                    Demand
                                </span>
                            @elseif($rule->rule_type === 'seasonality')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                    Seasonality
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                                    Lead Time
                                </span>
                            @endif
                        </td>

                        <!-- Condition Summary -->
                        <td class="py-3.5 px-4 font-medium text-[#1D1D1F]">
                            {{ $rule->condition_summary }}
                        </td>

                        <!-- Adjustment -->
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold {{ $rule->adjustment_type === 'increase' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                {{ $rule->formatted_adjustment }}
                            </span>
                        </td>

                        <!-- Applies To -->
                        <td class="py-3.5 px-4 text-xs font-medium text-[#6E6E73]">
                            {{ $rule->formatted_applies_to }}
                        </td>

                        <!-- Inline Status Toggle -->
                        <td class="py-3.5 px-4">
                            <form action="{{ route('admin.pricing.toggle_status', $rule) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold cursor-pointer transition-all {{ $rule->status === 'active' ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $rule->status === 'active' ? 'bg-emerald-600' : 'bg-gray-400' }}"></span>
                                    <span>{{ ucfirst($rule->status) }}</span>
                                </button>
                            </form>
                        </td>

                        <!-- Bookings Triggered Count -->
                        <td class="py-3.5 px-4 text-center">
                            <a href="{{ route('admin.pricing.triggered', $rule) }}" 
                               class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-[#F2F2F7] hover:bg-[#E5E5EA] text-[#780000] font-extrabold text-xs transition-colors"
                               title="Click to view triggered bookings">
                                <span>{{ number_format($rule->adjustments_count) }}</span>
                                <svg class="w-3.5 h-3.5 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                            </a>
                        </td>

                        <!-- Actions -->
                        <td class="py-3.5 px-4 text-right space-x-2">
                            <a href="{{ route('admin.pricing.edit', $rule) }}" 
                               class="inline-flex items-center px-2.5 py-1 rounded-lg bg-white border border-[#D1D1D6] hover:bg-[#F2F2F7] text-xs font-bold text-[#1D1D1F] transition-colors">
                                Edit
                            </a>

                            <button type="button"
                                    @click="deleteModal = true; deleteUrl = '{{ route('admin.pricing.destroy', $rule) }}'; ruleName = '{{ addslashes($rule->name) }}'; triggeredCount = {{ $rule->adjustments_count }};"
                                    class="inline-flex items-center px-2.5 py-1 rounded-lg bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-bold transition-colors">
                                Delete
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-12 text-center text-[#6E6E73]">
                            <div class="max-w-sm mx-auto space-y-3">
                                <div class="w-12 h-12 rounded-full bg-[#F2F2F7] flex items-center justify-center mx-auto text-[#6E6E73]">
                                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                </div>
                                <div class="font-bold text-[#1D1D1F]">No Pricing Rules Found</div>
                                <p class="text-xs">Create dynamic pricing rules to adjust prices automatically based on demand, seasons, or early/late bookings.</p>
                                <a href="{{ route('admin.pricing.create') }}" class="btn-primary inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold">
                                    + Add Your First Rule
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rules->hasPages())
        <div class="p-4 border-t border-[#E5E5EA]">
            {{ $rules->links() }}
        </div>
        @endif
    </div>

    <!-- Soft Delete Confirmation Modal -->
    <div x-show="deleteModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
         @keydown.escape.window="deleteModal = false">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-[#E5E5EA]"
             @click.away="deleteModal = false">
            <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
            </div>

            <div class="space-y-1.5">
                <h3 class="text-lg font-extrabold text-[#1D1D1F]">Delete Pricing Rule?</h3>
                <p class="text-xs text-[#6E6E73]">
                    Are you sure you want to remove rule <strong class="text-[#1D1D1F]" x-text="ruleName"></strong>?
                </p>
                <template x-if="triggeredCount > 0">
                    <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs mt-2">
                        <strong>Notice:</strong> This rule has affected <span x-text="triggeredCount"></span> booking(s). It will be soft-deleted to preserve all past customer receipts and audit histories.
                    </div>
                </template>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" @click="deleteModal = false" class="btn-secondary px-4 py-2 text-xs font-bold">
                    Cancel
                </button>

                <form :action="deleteUrl" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-colors">
                        Yes, Delete Rule
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
