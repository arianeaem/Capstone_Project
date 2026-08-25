@extends('layouts.app')

@section('title', 'Forgot Password | Camp FreedivePH')
@section('meta_description', 'Recover your Camp FreedivePH staff account password.')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center px-4 sm:px-6 lg:px-8 py-10">
    <div class="max-w-md w-full space-y-6">
        
        <!-- Header & Logo -->
        <div class="text-center">
            <a href="{{ route('landing') }}" class="inline-block group mb-3">
                <img src="{{ asset('images/logo.png') }}" alt="Camp FreedivePH Logo" class="w-16 h-16 sm:w-20 sm:h-20 rounded-full object-contain mx-auto shadow-md border border-[#E5E5EA] group-hover:scale-105 transition-transform bg-white">
            </a>
            <h1 class="text-2xl font-extrabold text-[#1D1D1F] tracking-tight">Recover Password</h1>
            <p class="text-sm text-[#6E6E73] mt-1">Enter your staff email to receive a password reset link.</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 sm:p-8 shadow-sm">
            
            @if(session('status'))
                <div class="mb-5 p-4 rounded-xl bg-[#ECFDF5] border border-[#A7F3D0] text-[#065F46] text-xs leading-relaxed font-medium">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="block font-bold text-[#1D1D1F] mb-1.5 text-sm">
                        Registered Staff Email <span class="text-[#780000]">*</span>
                    </label>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           value="{{ old('email') }}" 
                           placeholder="staff@campfreedive.ph" 
                           required 
                           autofocus
                           class="w-full px-4 py-3 rounded-xl border {{ $errors->has('email') ? 'border-[#FF3B3C] ring-1 ring-[#FF3B3C]' : 'border-[#D1D1D6]' }} focus:border-[#780000] focus:ring-2 focus:ring-[#780000]/20 text-sm text-[#1D1D1F] bg-white">
                    
                    @error('email')
                        <p class="text-xs text-[#FF3B3C] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-primary w-full py-3.5 text-sm font-bold shadow-md">
                    Send Password Reset Link →
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-[#E5E5EA] text-center">
                <a href="{{ route('login') }}" class="text-xs font-bold text-[#780000] hover:underline">
                    ← Return to Login
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
