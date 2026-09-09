@extends('layouts.admin')

@section('title', 'Booking #' . $booking->booking_number . ' | Camp FreedivePH')

@section('content')
<div class="space-y-6 text-sm" x-data="{ openStatusModal: false }">
    
    <!-- Top Breadcrumb & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#E5E5EA] pb-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.bookings.index') }}" class="text-xs text-[#6E6E73] hover:text-[#1D1D1F]">
                    ← Back to All Bookings
                </a>
                <span class="text-[#D1D1D6]">/</span>
                <span class="font-mono font-bold text-[#780000]">{{ $booking->booking_number }}</span>
            </div>
            <h1 class="text-2xl font-extrabold text-[#1D1D1F] mt-1">Reservation Details</h1>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.bookings.edit', $booking) }}" class="btn-secondary px-4 py-2 text-xs sm:text-sm font-semibold flex items-center gap-1.5">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                <span>Edit Details</span>
            </a>

            <button type="button" 
                    @click="openStatusModal = true"
                    class="btn-primary px-4 py-2 text-xs sm:text-sm font-bold flex items-center gap-1.5">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                <span>Change Status</span>
            </button>
        </div>
    </div>

    <!-- Booking Overview Banner -->
    <div class="bg-white rounded-xl border border-[#E5E5EA] p-5 sm:p-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-2">
            <h2 class="text-xl font-bold text-[#1D1D1F]">{{ $booking->formatted_class_type }}</h2>
            <div class="text-xs text-[#6E6E73]">
                <strong class="text-[#1D1D1F]">{{ $booking->start_date->format('F d, Y') }}</strong> to <strong class="text-[#1D1D1F]">{{ $booking->end_date->format('F d, Y') }}</strong>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $booking->status_badge['bg'] }}">
                    {{ $booking->status_badge['label'] }}
                </span>
                <span class="text-xs px-2.5 py-1 rounded-full font-bold border {{ $booking->payment_status_badge['class'] }}">
                    {{ $booking->payment_status_badge['label'] }}
                </span>
            </div>
        </div>

        <div class="bg-[#FAFAFC] border border-[#E5E5EA] p-4 rounded-xl text-left md:text-right space-y-1 shrink-0">
            <span class="text-xs text-[#6E6E73] block">Downpayment Required / Paid</span>
            <div class="text-2xl font-extrabold text-[#780000]">₱{{ number_format($booking->downpayment_amount, 2) }}</div>
            <span class="text-xs text-[#6E6E73] block">Total Amount: ₱{{ number_format($booking->total_amount, 2) }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
    <!-- Participants & Trip Details -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Participants Roster -->
            <div class="bg-white rounded-xl p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                    <h3 class="text-base font-bold text-[#1D1D1F]">Participant(s) ({{ $booking->participants->count() }} pax)</h3>
                </div>

                <div class="divide-y divide-[#E5E5EA]">
                    @foreach($booking->participants as $index => $p)
                    <div class="py-3.5 first:pt-0 last:pb-0 space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-sm text-[#1D1D1F]">{{ $index + 1 }}. {{ $p->name }}</span>
                                <span class="text-xs text-[#6E6E73]">({{ $p->age }} years old)</span>
                            </div>
                            <span class="text-xs px-2.5 py-0.5 rounded-md font-semibold bg-[#F2F2F7] text-[#1D1D1F]">
                                {{ ucfirst(str_replace('_', ' ', $p->swimmer_status ?: 'Swimmer')) }}
                            </span>
                        </div>
                        <strong class="text-[#1D1D1F]">Medical & Health Notes:</strong> 
                        <span class="{{ $p->health_condition && $p->health_condition !== 'None declared' ? 'text-[#92400E] font-medium' : '' }}">
                            {{ $p->health_condition ?: 'None declared' }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Contact and Logistics -->
            <div class="bg-white rounded-xl p-6 space-y-4">
                <h3 class="text-base font-bold text-[#1D1D1F] border-b border-[#E5E5EA] pb-3">Contact & Logistics</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm">
                    <div>
                        <span class="text-xs text-[#6E6E73] block">Primary Booker Name</span>
                        <strong class="text-[#1D1D1F]">{{ $booking->contact_name }}</strong>
                    </div>
                    <div>
                        <span class="text-xs text-[#6E6E73] block">Contact Email</span>
                        <span class="text-[#1D1D1F] font-medium">{{ $booking->contact_email }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-[#6E6E73] block">Mobile Phone</span>
                        <span class="text-[#1D1D1F] font-medium">{{ $booking->contact_phone }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-[#6E6E73] block">Transportation</span>
                        <strong class="text-[#1D1D1F]">{{ $booking->pickup_option === 'carpool' ? 'Manila Carpool Van' : 'Own Transportation' }}</strong>
                        @if($booking->pickup_location)
                            <div class="text-xs text-[#6E6E73] mt-0.5">{{ $booking->pickup_location }}</div>
                        @endif
                    </div>
                    <div>
                        <span class="text-xs text-[#6E6E73] block">Boat Dive Add-on</span>
                        <span class="text-[#1D1D1F]">{{ $booking->boat_dive ? 'Yes (+₱600/pax Sanctuary Boat Dive)' : 'No' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-[#6E6E73] block">Reservation PIN (Guest Access)</span>
                        <strong class="text-[#780000] font-mono text-sm">{{ $booking->pin }}</strong>
                    </div>
                </div>
            </div>

            <!-- Booking Audit Trail -->
            <div class="bg-white rounded-xl p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                    <h3 class="text-base font-bold text-[#1D1D1F]">Booking Audit Trail & History</h3>
                    <span class="text-xs text-[#6E6E73]">
                        Created {{ $booking->created_at->format('M d, Y h:i A') }}
                    </span>
                </div>

                <div class="divide-y divide-[#E5E5EA]">
                    @forelse($booking->statusLogs as $log)
                    <div class="py-3 text-xs space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[#1D1D1F]">
                                @if($log->old_status && $log->old_status !== $log->new_status && $log->old_status !== 'new')
                                    {{ ucfirst(str_replace('_', ' ', $log->old_status)) }} {{ ucfirst(str_replace('_', ' ', $log->new_status)) }}
                                @elseif($log->old_status === 'new' || $log->old_status === null)
                                    Reservation Initialized ({{ ucfirst(str_replace('_', ' ', $log->new_status)) }})
                                @else
                                    Details Modified
                                @endif
                            </span>
                            <span class="text-[#8E8E93]">{{ $log->created_at->format('M d, Y h:i A') }}</span>
                        </div>
                        <p class="text-[#6E6E73]">{{ $log->note }}</p>
                        <span class="text-xs text-[#8E8E93] block">Actor: <strong class="text-[#1D1D1F]">{{ $log->user ? $log->user->name : ($booking->created_by ? 'Staff' : 'Online Guest / System') }}</strong></span>
                    </div>
                    @empty
                    <!-- Fallback Creation Log -->
                    <div class="py-3 text-xs space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[#1D1D1F]">Reservation Created</span>
                            <span class="text-[#8E8E93]">{{ $booking->created_at->format('M d, Y h:i A') }}</span>
                        </div>
                        <p class="text-[#6E6E73]">Initial booking reservation logged in system for {{ $booking->contact_name }}.</p>
                        <span class="text-xs text-[#8E8E93] block">Actor: <strong class="text-[#1D1D1F]">{{ $booking->creator ? $booking->creator->name : 'Online Guest / System' }}</strong></span>
                    </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Invoice & Payment Snapshot -->
        <div class="space-y-6">
            
            <!-- Itemized Invoice Breakdown -->
            <div class="bg-white rounded-xl p-6 space-y-4">
                <h3 class="text-base font-bold text-[#1D1D1F] border-b border-[#E5E5EA] pb-3">Itemized Invoice</h3>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-[#6E6E73]">Course Tuition ({{ $booking->participants->count() }} pax):</span>
                        <strong class="text-[#1D1D1F]">₱{{ number_format($booking->subtotal, 2) }}</strong>
                    </div>

                    @if($booking->carpool_fee > 0)
                    <div class="flex justify-between">
                        <span class="text-[#6E6E73]">Carpool Van (Roundtrip):</span>
                        <strong class="text-[#1D1D1F]">₱{{ number_format($booking->carpool_fee, 2) }}</strong>
                    </div>
                    @endif

                    @if($booking->boat_dive_fee > 0)
                    <div class="flex justify-between">
                        <span class="text-[#6E6E73]">Sanctuary Boat Dive:</span>
                        <strong class="text-[#1D1D1F]">₱{{ number_format($booking->boat_dive_fee, 2) }}</strong>
                    </div>
                    @endif

                    <div class="flex justify-between">
                        <span class="text-[#6E6E73]">Mabini LGU Dive Pass:</span>
                        <strong class="text-[#1D1D1F]">₱{{ number_format($booking->lgu_fee, 2) }}</strong>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-[#6E6E73]">Municipal Environmental Fee:</span>
                        <strong class="text-[#1D1D1F]">₱{{ number_format($booking->environmental_fee, 2) }}</strong>
                    </div>

                    <div class="pt-2 border-t border-[#E5E5EA] flex justify-between text-sm">
                        <strong class="text-[#1D1D1F]">Total Amount:</strong>
                        <strong class="text-[#780000]">₱{{ number_format($booking->total_amount, 2) }}</strong>
                    </div>

                    <div class="flex justify-between text-xs pt-1 text-[#34C759]">
                        <span>Required Downpayment:</span>
                        <strong>₱{{ number_format($booking->downpayment_amount, 2) }}</strong>
                    </div>

                    <div class="flex justify-between text-xs text-[#6E6E73]">
                        <span>Remaining Balance:</span>
                        <strong>₱{{ number_format($booking->balance_amount, 2) }}</strong>
                    </div>
                </div>
            </div>

            <!-- Payment Summary Snapshot -->
            <div class="bg-white rounded-xl p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                    <h3 class="text-base font-bold text-[#1D1D1F]">Payment Summary</h3>
                    <span class="text-xs text-[#6E6E73]">Read-Only Snapshot</span>
                </div>

                <div class="space-y-3">
                    @forelse($booking->payments as $payment)
                    <div class="p-3 bg-[#FAFAFC] rounded-xl text-xs space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[#1D1D1F] uppercase">{{ str_replace('_', ' ', $payment->payment_method) }}</span>
                            <span class="font-bold text-[#34C759]">₱{{ number_format($payment->amount, 2) }}</span>
                        </div>
                        <div class="text-xs text-[#6E6E73]">Ref: <span class="font-mono">{{ $payment->transaction_id }}</span></div>
                        <div class="text-xs text-[#8E8E93]">Paid at: {{ $payment->paid_at ? $payment->paid_at->format('M d, Y h:i A') : 'N/A' }}</div>
                    </div>
                    @empty
                    <p class="text-xs text-[#6E6E73]">No payments recorded.</p>
                    @endforelse
                </div>

                <!-- Payment Navigation -->
                <div class="border-[#E5E5EA]">
                    <a href="javascript:void(0)" 
                       onclick="alert('Routing to Payments & Refunds Module for Booking #{{ $booking->booking_number }}')"
                       class="btn-secondary w-full py-2 text-xs font-bold text-center block">
                        Manage Payment in Payments & Refunds
                    </a>
                </div>
            </div>

        </div>

    </div>

    <!-- Status Change Modal -->
    <div x-show="openStatusModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-lg w-full p-6 sm:p-8 space-y-5 shadow-2xl border border-[#E5E5EA]" @click.outside="openStatusModal = false">
            <div class="flex items-center justify-between border-b border-[#E5E5EA] pb-3">
                <h3 class="text-lg font-bold text-[#1D1D1F]">Update Booking Status</h3>
                <button type="button" @click="openStatusModal = false" class="text-[#8E8E93] hover:text-[#1D1D1F] font-bold text-lg">✕</button>
            </div>

            <form action="{{ route('admin.bookings.status.update', $booking) }}" method="POST" class="space-y-4 text-sm">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-2">New Status <span class="text-[#780000]">*</span></label>
                    <select name="status" required class="w-full px-3.5 py-2.5 rounded-xl border border-[#D1D1D6] focus:border-[#780000] text-sm text-[#1D1D1F] bg-white font-medium">
                        <option value="confirmed" {{ $booking->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="completed" {{ $booking->status === 'completed' ? 'selected' : '' }}>Completed (Dive Completed)</option>
                        <option value="rescheduled" {{ $booking->status === 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                        <option value="no_show" {{ $booking->status === 'no_show' ? 'selected' : '' }}>No-Show (Forfeits Downpayment)</option>
                        <option value="cancelled_by_camp" {{ $booking->status === 'cancelled_by_camp' ? 'selected' : '' }}>Cancelled by Camp (Weather/Admin)</option>
                        <option value="cancelled_by_guest" {{ $booking->status === 'cancelled_by_guest' ? 'selected' : '' }}>Cancelled by Guest</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-[#1D1D1F] mb-2">Reason / Status Note</label>
                    <textarea name="note" rows="3" placeholder="Explain why this status is being changed..." class="w-full px-3.5 py-2 rounded-xl border border-[#D1D1D6] text-sm text-[#1D1D1F] bg-white"></textarea>
                </div>

                <div class="p-3 bg-[#FFFBEB] rounded-xl border border-[#FDE68A] text-xs text-[#92400E] leading-relaxed">
                    <strong>Note:</strong> Status changes propagate immediately to the Coach Portal, Batch schedule, and Reports. If setting to Cancelled, please process the refund via Payments & Refunds.
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-[#E5E5EA]">
                    <button type="button" @click="openStatusModal = false" class="btn-secondary px-4 py-2 text-sm">Cancel</button>
                    <button type="submit" class="btn-primary px-5 py-2 text-sm font-bold">
                        Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
