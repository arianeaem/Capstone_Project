@extends('layouts.admin')

@section('title', 'Add Pricing Rule | Camp FreedivePH')

@section('content')
<div class="max-w-3xl mx-auto space-y-6 text-sm"
     x-data="{
         name: '{{ old('name', '') }}',
         rule_type: '{{ old('rule_type', 'seasonality') }}',
         condition_operator: '{{ old('condition_operator', '<=') }}',
         condition_value: '{{ old('condition_value', 'peak') }}',
         applies_to: '{{ old('applies_to', 'all') }}',
         adjustment_type: '{{ old('adjustment_type', 'increase') }}',
         adjustment_method: '{{ old('adjustment_method', 'percentage') }}',
         adjustment_value: '{{ old('adjustment_value', '10') }}',
         priority: '{{ old('priority', '1') }}',
         status: '{{ old('status', 'active') }}',

         get previewText() {
             let condText = '';
             if (this.rule_type === 'demand') {
                 condText = 'Demand Level is ' + (this.condition_value ? this.condition_value.toUpperCase() : 'HIGH');
             } else if (this.rule_type === 'seasonality') {
                 let s = this.condition_value || 'peak';
                 condText = 'Season is ' + s.replace('_', '-').toUpperCase() + ' SEASON';
             } else {
                 let op = this.condition_operator === '<=' ? '3 days or less' : (this.condition_operator === '>=' ? 'at least ' + (this.condition_value || '30') + ' days' : this.condition_operator + ' ' + (this.condition_value || '0') + ' days');
                 condText = 'Lead time is ' + op + ' before dive date';
             }

             let scopeText = this.applies_to === 'all' ? 'All Freediving Classes' : this.applies_to.toUpperCase() + ' Class';
             let adjText = (this.adjustment_type === 'increase' ? 'increase by ' : 'decrease by ') + 
                           (this.adjustment_method === 'percentage' ? (this.adjustment_value || '0') + '%' : '₱' + (this.adjustment_value || '0'));

             return 'When ' + condText + ' for ' + scopeText + ', the price per person will ' + adjText + '.';
         }
     }">

    <!-- Top Navigation Header -->
    <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-5">
        <div>
            <div class="flex items-center gap-2 text-xs text-[#6E6E73] mb-1">
                <a href="{{ route('admin.pricing.index') }}" class="hover:text-[#780000] font-semibold transition-colors">Dynamic Pricing</a>
                <span>/</span>
                <span>Rule Builder</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight">Create Pricing Rule</h1>
        </div>

        <a href="{{ route('admin.pricing.index') }}" class="btn-secondary px-4 py-2 text-xs font-bold">
            ← Cancel
        </a>
    </div>

    @if ($errors->any())
    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
        <div class="font-bold">Please correct the following errors:</div>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Main Rule Builder Form -->
    <form action="{{ route('admin.pricing.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Card 1: Rule Identification -->
        <div class="bg-white rounded-2xl border border-[#E5E5EA] p-5 sm:p-6 shadow-2xs space-y-4">
            <h2 class="text-base font-extrabold text-[#1D1D1F] border-b border-[#E5E5EA] pb-3">1. Rule Identification & Scope</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Rule Name -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-[#1D1D1F] mb-1">Rule Name <span class="text-rose-600">*</span></label>
                    <input type="text" name="name" x-model="name" required placeholder="e.g. Peak Season Discovery Bump"
                           class="w-full text-xs sm:text-sm rounded-xl border border-[#D1D1D6] px-3.5 py-2.5 bg-[#FAFAFC] focus:bg-white focus:border-[#780000] focus:ring-1 focus:ring-[#780000]">
                </div>

                <!-- Description -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-[#1D1D1F] mb-1">Description (Optional)</label>
                    <textarea name="description" rows="2" placeholder="e.g. Applies during November to April high season to smooth capacity"
                              class="w-full text-xs sm:text-sm rounded-xl border border-[#D1D1D6] px-3.5 py-2.5 bg-[#FAFAFC] focus:bg-white focus:border-[#780000] focus:ring-1 focus:ring-[#780000]">{{ old('description') }}</textarea>
                </div>

                <!-- Applies To -->
                <div>
                    <label class="block text-xs font-bold text-[#1D1D1F] mb-1">Applies To <span class="text-rose-600">*</span></label>
                    <select name="applies_to" x-model="applies_to" required
                            class="w-full text-xs sm:text-sm rounded-xl border border-[#D1D1D6] px-3.5 py-2.5 bg-[#FAFAFC] focus:bg-white focus:border-[#780000] focus:ring-1 focus:ring-[#780000]">
                        <option value="all">All Classes (Discovery, Fundive, Refinement)</option>
                        <option value="discovery">Discovery Class Only (₱4,250 base)</option>
                        <option value="fundive">Fundive Only (₱2,500 - ₱3,300 base)</option>
                        <option value="refinement">Refinement Class Only (₱4,100 base)</option>
                    </select>
                </div>

                <!-- Execution Priority -->
                <div>
                    <label class="block text-xs font-bold text-[#1D1D1F] mb-1">Execution Priority (1 = Highest)</label>
                    <input type="number" name="priority" x-model="priority" min="1" max="999"
                           class="w-full text-xs sm:text-sm rounded-xl border border-[#D1D1D6] px-3.5 py-2.5 bg-[#FAFAFC] focus:bg-white focus:border-[#780000] focus:ring-1 focus:ring-[#780000]">
                    <span class="text-[11px] text-[#6E6E73]">Determines stacking sequence when multiple rules trigger.</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Rule Condition Trigger -->
        <div class="bg-white rounded-2xl border border-[#E5E5EA] p-5 sm:p-6 shadow-2xs space-y-4">
            <h2 class="text-base font-extrabold text-[#1D1D1F] border-b border-[#E5E5EA] pb-3">2. Rule Trigger & Condition</h2>

            <!-- Select Rule Type Tabs -->
            <div>
                <label class="block text-xs font-bold text-[#1D1D1F] mb-2">Rule Type <span class="text-rose-600">*</span></label>
                <div class="grid grid-cols-3 gap-3">
                    <label class="p-3.5 rounded-xl border-2 text-center cursor-pointer transition-all flex flex-col items-center gap-1.5"
                           :class="rule_type === 'seasonality' ? 'border-[#780000] bg-[#F8EAEA]/50 text-[#780000]' : 'border-[#E5E5EA] bg-white text-[#1D1D1F] hover:bg-[#FAFAFC]'">
                        <input type="radio" name="rule_type" value="seasonality" x-model="rule_type" @change="condition_value = 'peak'" class="sr-only">
                        <span class="font-extrabold text-xs sm:text-sm">Seasonality</span>
                        <span class="text-[11px] opacity-75">Peak, Shoulder, Off-Peak</span>
                    </label>

                    <label class="p-3.5 rounded-xl border-2 text-center cursor-pointer transition-all flex flex-col items-center gap-1.5"
                           :class="rule_type === 'demand' ? 'border-[#780000] bg-[#F8EAEA]/50 text-[#780000]' : 'border-[#E5E5EA] bg-white text-[#1D1D1F] hover:bg-[#FAFAFC]'">
                        <input type="radio" name="rule_type" value="demand" x-model="rule_type" @change="condition_value = 'high'" class="sr-only">
                        <span class="font-extrabold text-xs sm:text-sm">Demand Level</span>
                        <span class="text-[11px] opacity-75">Live Booking Occupancy</span>
                    </label>

                    <label class="p-3.5 rounded-xl border-2 text-center cursor-pointer transition-all flex flex-col items-center gap-1.5"
                           :class="rule_type === 'lead_time' ? 'border-[#780000] bg-[#F8EAEA]/50 text-[#780000]' : 'border-[#E5E5EA] bg-white text-[#1D1D1F] hover:bg-[#FAFAFC]'">
                        <input type="radio" name="rule_type" value="lead_time" x-model="rule_type" @change="condition_value = '3'; condition_operator = '<='" class="sr-only">
                        <span class="font-extrabold text-xs sm:text-sm">Lead Time</span>
                        <span class="text-[11px] opacity-75">Days Before Dive Date</span>
                    </label>
                </div>
            </div>

            <!-- Dynamic Condition Fields -->
            <div class="pt-2">
                <!-- Condition: Seasonality -->
                <div x-show="rule_type === 'seasonality'" class="space-y-2">
                    <label class="block text-xs font-bold text-[#1D1D1F]">When Season is:</label>
                    <select name="condition_value" x-model="condition_value" :disabled="rule_type !== 'seasonality'"
                            class="w-full text-xs sm:text-sm rounded-xl border border-[#D1D1D6] px-3.5 py-2.5 bg-[#FAFAFC] focus:bg-white focus:border-[#780000] focus:ring-1 focus:ring-[#780000]">
                        <option value="peak">Peak Season (Nov - Apr: Amihan, Holy Week & Summer Holidays)</option>
                        <option value="shoulder">Shoulder Season (May & Oct: Transitional Monsoon)</option>
                        <option value="off_peak">Off-Peak Season (Jun - Sep: Habagat & Low Utilization)</option>
                    </select>
                </div>

                <!-- Condition: Demand -->
                <div x-show="rule_type === 'demand'" class="space-y-2">
                    <label class="block text-xs font-bold text-[#1D1D1F]">When Demand Level is:</label>
                    <select name="condition_value" x-model="condition_value" :disabled="rule_type !== 'demand'"
                            class="w-full text-xs sm:text-sm rounded-xl border border-[#D1D1D6] px-3.5 py-2.5 bg-[#FAFAFC] focus:bg-white focus:border-[#780000] focus:ring-1 focus:ring-[#780000]">
                        <option value="high">High Demand (> 60% Batch Capacity Booked)</option>
                        <option value="medium">Medium Demand (25% - 60% Capacity Booked)</option>
                        <option value="low">Low Demand (< 25% Capacity Booked)</option>
                    </select>
                </div>

                <!-- Condition: Lead Time -->
                <div x-show="rule_type === 'lead_time'" class="space-y-2">
                    <label class="block text-xs font-bold text-[#1D1D1F]">When Days Before Dive Date is:</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <select name="condition_operator" x-model="condition_operator" :disabled="rule_type !== 'lead_time'"
                                class="w-full text-xs sm:text-sm rounded-xl border border-[#D1D1D6] px-3.5 py-2.5 bg-[#FAFAFC] focus:bg-white focus:border-[#780000] focus:ring-1 focus:ring-[#780000]">
                            <option value="<=">Less than or equal to (≤) — e.g. Last-minute</option>
                            <option value=">=">More than or equal to (≥) — e.g. Early-bird</option>
                            <option value="<">Strictly less than (<)</option>
                            <option value=">">Strictly more than (>)</option>
                            <option value="==">Exactly equal to (==)</option>
                        </select>

                        <div class="relative">
                            <input type="number" name="condition_value" x-model="condition_value" :disabled="rule_type !== 'lead_time'" min="0" max="365" placeholder="e.g. 3"
                                   class="w-full text-xs sm:text-sm rounded-xl border border-[#D1D1D6] px-3.5 py-2.5 bg-[#FAFAFC] focus:bg-white focus:border-[#780000] focus:ring-1 focus:ring-[#780000] pr-14">
                            <span class="absolute right-3.5 top-2.5 text-xs text-[#6E6E73] font-semibold">days</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Price Adjustment -->
        <div class="bg-white rounded-2xl border border-[#E5E5EA] p-5 sm:p-6 shadow-2xs space-y-4">
            <h2 class="text-base font-extrabold text-[#1D1D1F] border-b border-[#E5E5EA] pb-3">3. Price Adjustment Calculation</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Adjustment Type -->
                <div>
                    <label class="block text-xs font-bold text-[#1D1D1F] mb-1">Direction <span class="text-rose-600">*</span></label>
                    <select name="adjustment_type" x-model="adjustment_type" required
                            class="w-full text-xs sm:text-sm rounded-xl border border-[#D1D1D6] px-3.5 py-2.5 bg-[#FAFAFC] focus:bg-white focus:border-[#780000] focus:ring-1 focus:ring-[#780000]">
                        <option value="increase">Increase (+ Surcharge / Peak Bump)</option>
                        <option value="decrease">Decrease (− Discount / Off-peak Incentive)</option>
                    </select>
                </div>

                <!-- Adjustment Method -->
                <div>
                    <label class="block text-xs font-bold text-[#1D1D1F] mb-1">Calculation Method <span class="text-rose-600">*</span></label>
                    <select name="adjustment_method" x-model="adjustment_method" required
                            class="w-full text-xs sm:text-sm rounded-xl border border-[#D1D1D6] px-3.5 py-2.5 bg-[#FAFAFC] focus:bg-white focus:border-[#780000] focus:ring-1 focus:ring-[#780000]">
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed">Fixed Peso Amount (₱)</option>
                    </select>
                </div>

                <!-- Adjustment Value -->
                <div>
                    <label class="block text-xs font-bold text-[#1D1D1F] mb-1">Adjustment Value <span class="text-rose-600">*</span></label>
                    <div class="relative">
                        <input type="number" step="0.01" min="0.01" name="adjustment_value" x-model="adjustment_value" required placeholder="e.g. 15"
                               class="w-full text-xs sm:text-sm rounded-xl border border-[#D1D1D6] px-3.5 py-2.5 bg-[#FAFAFC] focus:bg-white focus:border-[#780000] focus:ring-1 focus:ring-[#780000] pr-10">
                        <span class="absolute right-3.5 top-2.5 text-xs text-[#6E6E73] font-bold" x-text="adjustment_method === 'percentage' ? '%' : '₱'"></span>
                    </div>
                </div>
            </div>

            <!-- Initial Status -->
            <div class="pt-2 border-t border-[#E5E5EA] flex items-center justify-between">
                <div>
                    <div class="font-bold text-xs text-[#1D1D1F]">Initial Rule Status</div>
                    <div class="text-[11px] text-[#6E6E73]">Inactive rules can be prepared ahead of time without affecting live pricing.</div>
                </div>

                <div class="flex items-center gap-2">
                    <label class="inline-flex items-center gap-2 text-xs font-bold cursor-pointer">
                        <input type="radio" name="status" value="active" x-model="status" class="text-[#780000] focus:ring-[#780000]">
                        <span class="text-emerald-700">Active</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-xs font-bold cursor-pointer ml-3">
                        <input type="radio" name="status" value="inactive" x-model="status" class="text-[#780000] focus:ring-[#780000]">
                        <span class="text-gray-600">Inactive</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Card 4: Plain-Language Live Preview Card (§7.3) -->
        <div class="rounded-2xl p-5 border border-[#780000]/20 bg-[#F8EAEA] space-y-2">
            <div class="flex items-center gap-2 text-[#780000] font-extrabold text-xs uppercase tracking-wider">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                <span>Live Rule Plain-Language Preview</span>
            </div>
            <p class="text-xs sm:text-sm font-semibold text-[#1D1D1F] leading-relaxed" x-text="previewText"></p>
            <div class="text-[11px] text-[#6E6E73]">
                * Stacking safety cap: Total price adjustments across all active rules are automatically clamped within ±30% of base class rate.
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.pricing.index') }}" class="btn-secondary px-5 py-2.5 text-xs font-bold">
                Cancel
            </a>
            <button type="submit" class="btn-primary px-7 py-2.5 text-xs font-bold shadow-sm">
                Save & Create Rule
            </button>
        </div>
    </form>

</div>
@endsection
