@extends('layouts.admin')

@section('title', 'Edit Pricing Rule | Camp FreedivePH')

@section('content')
<div class="max-w-3xl mx-auto space-y-6 text-sm"
     x-data="{
         name: '{{ old('name', addslashes($rule->name)) }}',
         rule_type: '{{ old('rule_type', $rule->rule_type) }}',
         condition_operator: '{{ old('condition_operator', $rule->condition_operator ?: '<=') }}',
         condition_value: '{{ old('condition_value', $rule->condition_value) }}',
         applies_to: '{{ old('applies_to', $rule->applies_to) }}',
         adjustment_type: '{{ old('adjustment_type', $rule->adjustment_type) }}',
         adjustment_method: '{{ old('adjustment_method', $rule->adjustment_method) }}',
         adjustment_value: '{{ old('adjustment_value', (float)$rule->adjustment_value) }}',
         priority: '{{ old('priority', $rule->priority) }}',
         status: '{{ old('status', $rule->status) }}',

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
            <div class="flex items-center gap-2 mb-1.5">
                <a href="{{ route('admin.pricing.index') }}" class="text-sm font-semibold text-[#6E6E73] hover:text-[#780000] transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    <span>Dynamic Pricing</span>
                </a>
                <span class="text-[#D1D1D6]">/</span>
                <span class="font-bold text-[#780000] text-sm">Edit Rule</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight">Edit Pricing Rule</h1>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.pricing.triggered', $rule) }}" class="btn-secondary px-3 py-1.5 text-sm font-semibold">
                <span>View Triggered ({{ $rule->adjustments()->count() }})</span>
            </a>
            <a href="{{ route('admin.pricing.index') }}" class="btn-secondary px-3.5 py-1.5 text-sm font-semibold flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                <span>Cancel</span>
            </a>
        </div>
    </div>

    @if ($errors->any())
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm space-y-1">
        <div class="font-bold">Please correct the following errors:</div>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Main Rule Builder Form -->
    <form action="{{ route('admin.pricing.update', $rule) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Rule Identification & Scope -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-5 sm:p-6 shadow-2xs space-y-4">
            <h2 class="text-base font-extrabold text-[#1D1D1F] pb-3">1. Rule Identification & Scope</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Rule Name -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-bold text-[#1D1D1F] mb-1.5">Rule Name <span class="text-[#780000]">*</span></label>
                    <input type="text" name="name" x-model="name" required placeholder="e.g. Peak Season Discovery Bump"
                           class="w-full text-sm rounded-lg border border-[#D1D1D6] px-3 py-2 bg-[#F2F2F7] focus:bg-white focus:border-[#780000]">
                </div>

                <!-- Description -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-bold text-[#1D1D1F] mb-1.5">Description (Optional)</label>
                    <textarea name="description" rows="2" placeholder="e.g. Applies during November to April high season to smooth capacity"
                              class="w-full text-sm font-medium rounded-xl border border-[#D1D1D6] px-3.5 py-2.5 bg-white focus:border-[#780000]">{{ old('description', $rule->description) }}</textarea>
                </div>

                <!-- Applies To -->
                <div>
                    <label class="block text-sm font-bold text-[#1D1D1F] mb-1.5">Applies To <span class="text-[#780000]">*</span></label>
                    <select name="applies_to" x-model="applies_to" required
                            class="w-full text-sm font-medium rounded-xl border border-[#D1D1D6] px-3 py-2.5 bg-white focus:border-[#780000]">
                        <option value="all">All Classes (Discovery, Fundive, Refinement)</option>
                        <option value="discovery">Discovery Class Only (₱4,250 base)</option>
                        <option value="fundive">Fundive Only (₱2,500 - ₱3,300 base)</option>
                        <option value="refinement">Refinement Class Only (₱4,100 base)</option>
                    </select>
                </div>

                <!-- Execution Priority -->
                <div>
                    <label class="block text-sm font-bold text-[#1D1D1F] mb-1.5">Execution Priority (1 = Highest)</label>
                    <input type="number" name="priority" x-model="priority" min="1" max="999"
                           class="w-full text-sm font-medium rounded-xl border border-[#D1D1D6] px-3.5 py-2.5 bg-white focus:border-[#780000]">
                    <span class="text-sm text-[#6E6E73] mt-0.5 block">Determines stacking sequence when multiple rules trigger.</span>
                </div>
            </div>
        </div>

        <!-- Rule Trigger & Condition -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-5 sm:p-6 shadow-2xs space-y-4">
            <h2 class="text-base font-extrabold text-[#1D1D1F] pb-3">2. Rule Trigger & Condition</h2>

            <!-- Select Rule Type Tabs -->
            <div>
                <label class="block text-sm font-bold text-[#1D1D1F] mb-2">Rule Type <span class="text-[#780000]">*</span></label>
                <div class="grid grid-cols-3 gap-3">
                    <label class="p-3 rounded-xl border text-center cursor-pointer transition-all flex flex-col items-center gap-1"
                           :class="rule_type === 'seasonality' ? 'border-[#780000] bg-[#F8EAEA]/50 text-[#780000]' : 'border-[#E5E5EA] bg-white text-[#1D1D1F] hover:bg-[#F2F2F7]'">
                        <input type="radio" name="rule_type" value="seasonality" x-model="rule_type" class="sr-only">
                        <span class="font-extrabold text-sm">Seasonality</span>
                        <span class="text-sm opacity-75">Peak, Shoulder, Off-Peak</span>
                    </label>

                    <label class="p-3 rounded-xl border text-center cursor-pointer transition-all flex flex-col items-center gap-1"
                           :class="rule_type === 'demand' ? 'border-[#780000] bg-[#F8EAEA]/50 text-[#780000]' : 'border-[#E5E5EA] bg-white text-[#1D1D1F] hover:bg-[#F2F2F7]'">
                        <input type="radio" name="rule_type" value="demand" x-model="rule_type" class="sr-only">
                        <span class="font-extrabold text-sm">Demand Level</span>
                        <span class="text-sm opacity-75">Live Booking Occupancy</span>
                    </label>

                    <label class="p-3 rounded-xl border text-center cursor-pointer transition-all flex flex-col items-center gap-1"
                           :class="rule_type === 'lead_time' ? 'border-[#780000] bg-[#F8EAEA]/50 text-[#780000]' : 'border-[#E5E5EA] bg-white text-[#1D1D1F] hover:bg-[#F2F2F7]'">
                        <input type="radio" name="rule_type" value="lead_time" x-model="rule_type" class="sr-only">
                        <span class="font-extrabold text-sm">Lead Time</span>
                        <span class="text-sm opacity-75">Days Before Dive Date</span>
                    </label>
                </div>
            </div>

            <!-- Dynamic Condition Fields -->
            <div class="pt-2">
                <!-- Condition: Seasonality -->
                <div x-show="rule_type === 'seasonality'" class="space-y-2">
                    <label class="block text-sm font-bold text-[#1D1D1F]">When Season is:</label>
                    <select name="condition_value" x-model="condition_value" :disabled="rule_type !== 'seasonality'"
                            class="w-full text-sm font-medium rounded-xl border border-[#D1D1D6] px-3 py-2.5 bg-white focus:border-[#780000]">
                        <option value="peak">Peak Season (Nov - Apr: Amihan, Holy Week & Summer Holidays)</option>
                        <option value="shoulder">Shoulder Season (May & Oct: Transitional Monsoon)</option>
                        <option value="off_peak">Off-Peak Season (Jun - Sep: Habagat & Low Utilization)</option>
                    </select>
                </div>

                <!-- Condition: Demand -->
                <div x-show="rule_type === 'demand'" class="space-y-2">
                    <label class="block text-sm font-bold text-[#1D1D1F]">When Demand Level is:</label>
                    <select name="condition_value" x-model="condition_value" :disabled="rule_type !== 'demand'"
                            class="w-full text-sm font-medium rounded-xl border border-[#D1D1D6] px-3 py-2.5 bg-white focus:border-[#780000]">
                        <option value="high">High Demand (Over 60% Batch Capacity Booked)</option>
                        <option value="medium">Medium Demand (25% to 60% Capacity Booked)</option>
                        <option value="low">Low Demand (Under 25% Capacity Booked)</option>
                    </select>
                </div>

                <!-- Condition: Lead Time -->
                <div x-show="rule_type === 'lead_time'" class="space-y-2">
                    <label class="block text-sm font-bold text-[#1D1D1F]">When Days Before Dive Date is:</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <select name="condition_operator" x-model="condition_operator" :disabled="rule_type !== 'lead_time'"
                                class="w-full text-sm font-medium rounded-xl border border-[#D1D1D6] px-3 py-2.5 bg-white focus:border-[#780000]">
                            <option value="<=">Less than or equal to (e.g. Last-minute)</option>
                            <option value=">=">More than or equal to (e.g. Early-bird)</option>
                            <option value="<">Less than</option>
                            <option value=">">More than</option>
                            <option value="==">Exactly equal to</option>
                        </select>

                        <div class="relative">
                            <input type="number" name="condition_value" x-model="condition_value" :disabled="rule_type !== 'lead_time'" min="0" max="365" placeholder="e.g. 3"
                                   class="w-full text-sm font-medium rounded-xl border border-[#D1D1D6] px-3.5 py-2.5 bg-white focus:border-[#780000] pr-14">
                            <span class="absolute right-3.5 top-2.5 text-sm text-[#6E6E73] font-semibold">days</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Price Adjustment Calculation -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-5 sm:p-6 shadow-2xs space-y-4">
            <h2 class="text-base font-extrabold text-[#1D1D1F] pb-3">3. Price Adjustment Calculation</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Adjustment Type -->
                <div>
                    <label class="block text-sm font-bold text-[#1D1D1F] mb-1.5">Direction <span class="text-[#780000]">*</span></label>
                    <select name="adjustment_type" x-model="adjustment_type" required
                            class="w-full text-sm font-medium rounded-xl border border-[#D1D1D6] px-3 py-2.5 bg-white focus:border-[#780000]">
                        <option value="increase">Increase (+ Surcharge / Peak Bump)</option>
                        <option value="decrease">Decrease (− Discount / Off-peak Incentive)</option>
                    </select>
                </div>

                <!-- Adjustment Method -->
                <div>
                    <label class="block text-sm font-bold text-[#1D1D1F] mb-1.5">Calculation Method <span class="text-[#780000]">*</span></label>
                    <select name="adjustment_method" x-model="adjustment_method" required
                            class="w-full text-sm font-medium rounded-xl border border-[#D1D1D6] px-3 py-2.5 bg-white focus:border-[#780000]">
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed">Fixed Peso Amount (₱)</option>
                    </select>
                </div>

                <!-- Adjustment Value -->
                <div>
                    <label class="block text-sm font-bold text-[#1D1D1F] mb-1.5">Adjustment Value <span class="text-[#780000]">*</span></label>
                    <div class="relative">
                        <input type="number" step="0.01" min="0.01" name="adjustment_value" x-model="adjustment_value" required placeholder="e.g. 15"
                               class="w-full text-sm font-medium rounded-xl border border-[#D1D1D6] px-3.5 py-2.5 bg-white focus:border-[#780000] pr-10">
                        <span class="absolute right-3.5 top-2.5 text-sm text-[#6E6E73] font-bold" x-text="adjustment_method === 'percentage' ? '%' : '₱'"></span>
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="pt-2 border-t border-[#E5E5EA] flex items-center justify-between">
                <div>
                    <div class="font-bold text-sm text-[#1D1D1F]">Rule Status</div>
                    <div class="text-sm text-[#6E6E73]">Only active rules affect customer quotes and booking totals.</div>
                </div>

                <div class="flex items-center gap-2">
                    <label class="inline-flex items-center gap-2 text-sm font-bold cursor-pointer">
                        <input type="radio" name="status" value="active" x-model="status" class="text-[#780000] focus:ring-[#780000]">
                        <span class="text-emerald-700">Active</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm font-bold cursor-pointer ml-3">
                        <input type="radio" name="status" value="inactive" x-model="status" class="text-[#780000] focus:ring-[#780000]">
                        <span class="text-[#6E6E73]">Inactive</span>
                    </label>
                </div>
            </div>
        </div>


        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-2 pt-2">
            <a href="{{ route('admin.pricing.index') }}" class="btn-secondary px-3.5 py-2 text-sm font-semibold">
                Cancel
            </a>
            <button type="submit" class="btn-primary px-5 py-2 text-sm font-bold shadow-2xs">
                Save Changes
            </button>
        </div>
    </form>

</div>
@endsection
