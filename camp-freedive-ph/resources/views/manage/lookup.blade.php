@extends('layouts.app')

@section('title', 'Manage Booking | Camp FreedivePH')
@section('meta_description', 'Retrieve and manage booking details using Booking Number and PIN.')

@section('content')
<div class="max-w-md mx-auto px-4 sm:px-6 py-12 sm:py-20 text-sm">
    <div class="bg-white rounded-xl border border-[#E5E5EA] shadow-sm p-6 sm:p-10">
        
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-14 h-14 rounded-xl bg-[#F8EAEA] text-[#780000] flex items-center justify-center mx-auto mb-4 shadow-sm">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            </div>
            <h1 class="text-2xl font-extrabold text-[#1D1D1F]">Manage Booking</h1>
            <p class="text-sm text-[#6E6E73] mt-1.5">
                Enter the Booking Number and 4-digit PIN to view reservation details, reschedule, or request cancellation.
            </p>
        </div>

        <!-- Form -->
        <form action="{{ route('manage.search') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="booking_number" class="block font-bold text-[#1D1D1F] mb-1.5">
                    Booking Number <span class="text-[#780000]">*</span>
                </label>
                <input type="text" 
                       name="booking_number" 
                       id="booking_number" 
                       value="{{ old('booking_number', $prefilledNumber) }}" 
                       placeholder="e.g. CFP-2026-8942" 
                       required
                       class="w-full px-4 py-3 rounded-xl border border-[#D1D1D6] focus:border-[#780000] focus:ring-2 focus:ring-[#780000]/20 text-sm font-mono uppercase tracking-wider text-[#1D1D1F] bg-white">
            </div>

            <div>
                <label for="pin" class="block font-bold text-[#1D1D1F] mb-1.5">
                    4-Digit PIN <span class="text-[#780000]">*</span>
                </label>
                <input type="password" 
                       name="pin" 
                       id="pin" 
                       value="{{ old('pin', $prefilledPin) }}" 
                       placeholder="••••" 
                       maxlength="8"
                       required
                       class="w-full px-4 py-3 rounded-xl border border-[#D1D1D6] focus:border-[#780000] focus:ring-2 focus:ring-[#780000]/20 text-sm font-mono tracking-widest text-[#1D1D1F] bg-white">
                <span class="text-xs text-[#6E6E73] mt-1.5 block">
                    The 4-digit PIN was sent to the booking email.
                </span>
            </div>

            <button type="submit" class="btn-primary w-full py-3.5 text-sm font-bold shadow-md mt-2">
                Find Booking →
            </button>
        </form>

        <!-- Help Strip -->
        <div class="mt-8 pt-6 border-t border-[#E5E5EA] text-center text-sm text-[#6E6E73]">
            <p>Need help with your reservation? <br>
            Message us on <a href="https://www.facebook.com/Campfreediveph/" target="_blank" rel="noopener noreferrer" class="text-[#780000] font-bold underline">Facebook Messenger</a> or call <a href="tel:09278879894" class="text-[#780000] font-bold">0927 887 9894</a>.</p>
        </div>
    </div>
</div>
@endsection
