@extends('layouts.admin')

@section('title', 'Batch Schedules & Coach Assignments | Camp FreedivePH')

@section('content')
<div class="space-y-6 text-sm" x-data="{ openBatchModal: false, selectedBatchId: null, openAssignModal: false, assignBatchCode: '', assignBatchDate: '' }">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-5">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.coaches.index') }}" class="text-xs text-[#6E6E73] hover:text-[#1D1D1F]">
                    ← Back to Coach Roster
                </a>
                <span class="text-[#D1D1D6]">/</span>
                <span class="font-bold text-[#780000]">Batch Schedules</span>
            </div>
            <h1 class="text-2xl font-extrabold text-[#1D1D1F] mt-1">2D1N Batch Schedules & Coach Assignments</h1>
            <p class="text-xs sm:text-sm text-[#6E6E73] mt-1">
                Assign certified coaches to 2D1N dive schedules. Student capacity computes automatically as <strong>4 pax per coach</strong>.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" 
                    @click="openBatchModal = true"
                    class="btn-primary px-5 py-2.5 text-xs sm:text-sm font-bold shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Add 2D1N Schedule</span>
            </button>
        </div>
    </div>

    <!-- Batch Assignment Schedule -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($batches as $batch)
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 shadow-sm flex flex-col justify-between space-y-4 hover:border-[#D1D1D6] transition-colors">
            
            <!-- Batch Header -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-mono font-extrabold text-base text-[#780000]">{{ $batch->batch_code }}</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $batch->status_badge['class'] }}">
                        {{ $batch->status_badge['label'] }}
                    </span>
                </div>

                <div class="text-xs text-[#1D1D1F] font-bold flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-[#6E6E73]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <span>{{ $batch->start_date->format('M d, Y (D)') }} - {{ $batch->end_date->format('M d, Y (D)') }}</span>
                </div>

                <!-- Capacity Display -->
                <div class="p-3 rounded-xl border text-xs flex items-center justify-between"
                     style="background-color: {{ $batch->is_coach_pending ? '#FFFBEB' : '#ECFDF5' }}; border-color: {{ $batch->is_coach_pending ? '#FDE68A' : '#A7F3D0' }}; color: {{ $batch->is_coach_pending ? '#92400E' : '#065F46' }}">
                    <span class="font-bold">
                        {{ $batch->is_coach_pending ? 'Coach Pending' : '✓ Capacity Ready' }}
                    </span>
                    <strong class="text-sm font-extrabold">
                        {{ $batch->computed_capacity }} Student Slots ({{ $batch->activeAssignments->count() }} Coach{{ $batch->activeAssignments->count() == 1 ? '' : 'es' }})
                    </strong>
                </div>
            </div>

            <!-- Assigned Coaches List -->
            <div class="space-y-2 pt-2 border-t border-[#E5E5EA]">
                <span class="text-xs font-bold uppercase tracking-wider text-[#6E6E73] block">Assigned Coaches:</span>

                @forelse($batch->activeAssignments as $assignment)
                    <div class="flex items-center justify-between p-2 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA] text-xs">
                        <div>
                            <strong class="text-[#1D1D1F] block">{{ $assignment->coach->full_name }}</strong>
                            <span class="text-xs text-[#6E6E73]">{{ $assignment->coach->certification_level }}</span>
                        </div>

                        <form action="{{ route('admin.coaches.assignments.unassign', [$batch, $assignment->coach]) }}" method="POST" onsubmit="return confirm('Unassign {{ $assignment->coach->full_name }} from this batch?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-bold text-[#FF3B3C] hover:underline px-1.5 py-1">
                                Unassign
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="text-xs text-[#8E8E93] italic py-1">No coaches assigned to this 2D1N schedule yet.</p>
                @endforelse
            </div>

            <!-- Assign Coach Button -->
            <div class="pt-2">
                <button type="button" 
                        @click="selectedBatchId = {{ $batch->id }}; assignBatchCode = '{{ $batch->batch_code }}'; assignBatchDate = '{{ $batch->start_date->format('M d, Y') }}'; openAssignModal = true"
                        class="btn-primary w-full py-2 text-xs font-bold shadow-sm flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>Assign Coach (+4 Pax)</span>
                </button>
            </div>

        </div>
        @empty
        <div class="col-span-3 bg-white rounded-xl border border-[#E5E5EA] p-12 text-center text-[#6E6E73]">
            <p>No 2D1N dive batches scheduled yet.</p>
            <button type="button" @click="openBatchModal = true" class="btn-primary px-5 py-2 text-xs font-bold mt-3">
                Create First Batch Schedule
            </button>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="bg-[#FAFAFC] rounded-xl border border-[#E5E5EA] overflow-hidden shadow-xs [&>*]:border-t-0">
        {{ $batches->links() }}
    </div>

    <!-- Create Batch Schedule Modal -->
    <div x-show="openBatchModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-[#E5E5EA]" @click.outside="openBatchModal = false">
            <h3 class="text-lg font-bold text-[#1D1D1F]">Create 2D1N Batch Schedule</h3>
            <p class="text-xs text-[#6E6E73]">
                Create an upcoming weekend dive schedule. Capacity will compute automatically as coaches are assigned.
            </p>

            <form action="{{ route('admin.coaches.assignments.batches.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-2">Batch Code (Optional)</label>
                    <input type="text" name="batch_code" placeholder="e.g. BATCH-2026-SEP05" class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-[#1D1D1F] mb-2">Start Date (Day 1) <span class="text-[#780000]">*</span></label>
                        <input type="date" name="start_date" required class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                    </div>

                    <div>
                        <label class="block font-bold text-[#1D1D1F] mb-2">End Date (Day 2) <span class="text-[#780000]">*</span></label>
                        <input type="date" name="end_date" required class="w-full px-3 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-2">Notes / Destination</label>
                    <textarea name="notes" rows="2" placeholder="e.g. Open for Discovery & Practice Dive students" class="w-full px-3.5 py-2 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#E5E5EA]">
                    <button type="button" @click="openBatchModal = false" class="btn-secondary px-3.5 py-1.5 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary px-5 py-1.5 text-xs font-bold shadow-sm">Create Schedule</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Assign Coach Modal -->
    <div x-show="openAssignModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-[#E5E5EA]" @click.outside="openAssignModal = false">
            <h3 class="text-lg font-bold text-[#1D1D1F]">Assign Coach to Batch</h3>
            <p class="text-xs text-[#6E6E73]">
                Assigning a certified coach to <strong class="text-[#780000]" x-text="assignBatchCode"></strong> increases student capacity by +4 pax.
            </p>

            <form :action="'{{ url('/admin/coaches/assignments/batches') }}/' + selectedBatchId + '/assign'" method="POST" class="space-y-3 text-xs">
                @csrf

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-2">Select Active Coach <span class="text-[#780000]">*</span></label>
                    <select name="coach_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] text-xs text-[#1D1D1F] bg-white font-medium">
                        <option value="">-- Choose Certified Coach --</option>
                        @foreach($activeCoaches as $c)
                            <option value="{{ $c->id }}">
                                {{ $c->full_name }} ({{ $c->certification_level }} - Exp: {{ $c->certification_expiry->format('M d, Y') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="p-3 bg-[#EFF6FF] rounded-xl border border-[#BFDBFE] text-xs text-[#1E40AF]">
                    <strong>Validation Rules:</strong> The system automatically verifies that the coach's certification is active on the dive date and checks against double-booking conflicts.
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#E5E5EA]">
                    <button type="button" @click="openAssignModal = false" class="btn-secondary px-3.5 py-1.5 text-xs">Cancel</button>
                    <button type="submit" class="btn-primary px-5 py-1.5 text-xs font-bold shadow-sm">Confirm Assignment</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
