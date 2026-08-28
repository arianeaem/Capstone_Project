@extends('layouts.app')

@section('title', 'Reset Password | Camp FreedivePH')
@section('meta_description', 'Set a new password for your Camp FreedivePH account.')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center px-4 sm:px-6 lg:px-8 py-10">
    <div class="max-w-md w-full space-y-6">
        
        <!-- Header & Logo -->
        <div class="text-center">
            <a href="{{ route('landing') }}" class="inline-block group mb-3">
                <img src="{{ asset('images/logo.png') }}" alt="Camp FreedivePH Logo" class="w-16 h-16 sm:w-20 sm:h-20 rounded-full object-contain mx-auto shadow-md border border-[#E5E5EA] group-hover:scale-105 transition-transform bg-white">
            </a>
            <h1 class="text-2xl font-extrabold text-[#1D1D1F] tracking-tight">Set New Password</h1>
            <p class="text-sm text-[#6E6E73] mt-1">Choose a secure password with at least 8 characters.</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 sm:p-8 shadow-sm">
            
            <form action="{{ route('password.update') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <!-- Email Input -->
                <div>
                    <label for="email" class="block font-bold text-[#1D1D1F] mb-2.5 text-sm">
                        Staff Email <span class="text-[#780000]">*</span>
                    </label>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           value="{{ old('email', $email) }}" 
                           required 
                           class="w-full px-4 py-3 rounded-xl border border-[#D1D1D6] focus:border-[#780000] text-sm text-[#1D1D1F] bg-white">
                    
                    @error('email')
                        <p class="text-xs text-[#FF3B3C] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- New Password -->
                <div>
                    <label for="password" class="block font-bold text-[#1D1D1F] mb-2.5 text-sm">
                        New Password <span class="text-[#780000]">*</span>
                    </label>
                    <input type="password" 
                           name="password" 
                           id="password" 
                           placeholder="At least 8 characters" 
                           required
                           class="w-full px-4 py-3 rounded-xl border border-[#D1D1D6] focus:border-[#780000] text-sm text-[#1D1D1F] bg-white">
                    
                    @error('password')
                        <p class="text-xs text-[#FF3B3C] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block font-bold text-[#1D1D1F] mb-2.5 text-sm">
                        Confirm New Password <span class="text-[#780000]">*</span>
                    </label>
                    <input type="password" 
                           name="password_confirmation" 
                           id="password_confirmation" 
                           placeholder="Re-type new password" 
                           required
                           class="w-full px-4 py-3 rounded-xl border border-[#D1D1D6] focus:border-[#780000] text-sm text-[#1D1D1F] bg-white">
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-primary w-full py-3.5 text-sm font-bold shadow-md">
                    Update Password & Log In →
                </button>
            </form>
        </div>

    </div>
</div>
@endsection
