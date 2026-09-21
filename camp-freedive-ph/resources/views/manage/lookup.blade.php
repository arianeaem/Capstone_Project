@extends('layouts.app')

@section('title', 'Manage Booking | Camp FreedivePH')
@section('meta_description', 'Retrieve and manage booking details using Booking Number and PIN.')

@section('content')
<div class="max-w-md mx-auto px-4 sm:px-6 py-12 sm:py-20 text-sm">
    <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 sm:p-10">
        
        <!-- Lookup Header -->
        <div class="text-center mb-8">
            <h1 class="text-2xl font-extrabold text-[#1D1D1F]">Manage Booking</h1>
            <p class="text-sm text-[#6E6E73] mt-1.5">
                Enter the Booking Number and 4-digit PIN to view reservation details, reschedule, or request cancellation.
            </p>
        </div>

        <!-- Booking Lookup Form -->
        <form action="{{ route('manage.search') }}" method="POST" class="space-y-5" novalidate>
            @csrf

            <div>
                <label for="booking_number" class="block font-bold text-[#1D1D1F] mb-2.5">
                    Booking Number <span class="text-[#D70015]">*</span>
                </label>
                <input type="text" 
                       name="booking_number" 
                       id="booking_number" 
                       value="{{ old('booking_number', $prefilledNumber) }}" 
                       placeholder="e.g. CFP-2026-8942" 
                       autocapitalize="characters"
                       autocorrect="off"
                       spellcheck="false"
                       autocomplete="off"
                       aria-invalid="{{ $errors->has('booking_number') ? 'true' : 'false' }}"
                       @if($errors->has('booking_number')) aria-describedby="err-booking-number" @endif
                       required
                        class="w-full px-4 py-3 rounded-xl border text-sm font-mono uppercase tracking-wider text-[#1D1D1F] bg-white transition-colors {{ $errors->has('booking_number') ? 'border-[#D70015] bg-red-50/10 focus:border-[#D70015]' : 'border-[#D1D1D6] focus:border-[#780000]' }}">
                @error('booking_number')
                <span id="err-booking-number" class="text-xs text-[#D70015] font-semibold mt-1.5 block">
                    {{ $message }}
                </span>
                @enderror
            </div>

            <div>
                <label for="pin" class="block font-bold text-[#1D1D1F] mb-2.5">
                    4-Digit PIN <span class="text-[#D70015]">*</span>
                </label>
                <input type="password" 
                       name="pin" 
                       id="pin" 
                       value="{{ old('pin', $prefilledPin) }}" 
                       placeholder="••••" 
                       maxlength="8"
                       inputmode="numeric"
                       pattern="[0-9]*"
                       autocomplete="one-time-code"
                       autocapitalize="off"
                       aria-invalid="{{ $errors->has('pin') ? 'true' : 'false' }}"
                       aria-describedby="{{ $errors->has('pin') ? 'err-pin' : 'pin-helper' }}"
                       required
                       class="w-full px-4 py-3 rounded-xl border text-sm font-mono tracking-widest text-[#1D1D1F] bg-white transition-colors {{ $errors->has('pin') ? 'border-[#D70015] bg-red-50/10 focus:border-[#D70015]' : 'border-[#D1D1D6] focus:border-[#780000]' }}">
                @error('pin')
                <span id="err-pin" class="text-xs text-[#D70015] font-semibold mt-1.5 block">
                    {{ $message }}
                </span>
                @enderror
                <span id="pin-helper" class="text-sm text-[#6E6E73] mt-1.5 block">
                    The 4-digit PIN was sent to the booking email.
                </span>
            </div>

            <button type="submit" class="btn-primary w-full py-3.5 text-sm font-bold mt-2 cursor-pointer transition-all active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000] focus-visible:ring-offset-2">
                Find Booking
            </button>
        </form>

        <!-- Support Contact Information -->
        <div class="mt-8 pt-6 border-t border-[#E5E5EA] text-center text-sm text-[#6E6E73] space-y-1.5">
            <p class="font-semibold text-[#1D1D1F]">Need help with your reservation?</p>
            <p class="leading-relaxed">
                You may contact us on 
                <a href="https://www.facebook.com/Campfreediveph/" target="_blank" rel="noopener noreferrer" class="text-[#780000] font-bold underline hover:text-[#5E0000] inline-flex items-center gap-1">
                    <span>Facebook</span>
                    <img src="{{ asset('icons/icons8-linking-60.png') }}" class="w-4 h-4 object-contain" alt="" aria-hidden="true">
                </a>, 
                <a href="https://www.instagram.com/campfreediveph/" target="_blank" rel="noopener noreferrer" class="text-[#780000] font-bold underline hover:text-[#5E0000] inline-flex items-center gap-1">
                    <span>Instagram</span>
                    <img src="{{ asset('icons/icons8-linking-60.png') }}" class="w-4 h-4 object-contain" alt="" aria-hidden="true">
                </a>, 
                email <a href="mailto:campfreediveph@gmail.com" class="text-[#780000] font-bold underline hover:text-[#5E0000]">campfreediveph@gmail.com</a>, 
                or call <a href="tel:+639278879894" class="text-[#780000] font-bold whitespace-nowrap hover:underline">+63 927 887 9894</a>.
            </p>
        </div>
    </div>
</div>
@endsection
