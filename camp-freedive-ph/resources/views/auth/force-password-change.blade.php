@extends('layouts.app')

@section('title', 'Change Temporary Password | Camp FreedivePH')
@section('meta_description', 'Update your initial temporary password before accessing the Camp FreedivePH portal.')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center px-4 sm:px-6 lg:px-8 py-10">
    <div class="max-w-md w-full space-y-6">
        
        <!-- Header & Logo -->
        <div class="text-center">
            <div class="w-16 h-16 rounded-full bg-[#FFFBEB] text-[#FF8D28] flex items-center justify-center mx-auto mb-3 shadow-sm border border-[#FDE68A]">
                <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            </div>
            <h1 class="text-2xl font-extrabold text-[#1D1D1F] tracking-tight">First-Time Password Setup</h1>
            <p class="text-sm text-[#6E6E73] mt-1">
                Welcome, <strong>{{ $user->name }}</strong>! For security, please update your temporary password before proceeding.
            </p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 sm:p-8 shadow-sm">
            
            <div class="mb-5 p-3.5 bg-[#FFFBEB] border border-[#FDE68A] rounded-xl text-xs text-[#92400E] leading-relaxed">
                <strong>First-login policy:</strong> You must choose a private, permanent password. It must be at least 8 characters long and different from your temporary password.
            </div>

            <form action="{{ route('password.force_change.update') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Current Temporary Password -->
                <div>
                    <label for="current_password" class="block font-bold text-[#1D1D1F] mb-2 text-sm">
                        Current Temporary Password <span class="text-[#780000]">*</span>
                    </label>
                    <input type="password" 
                           name="current_password" 
                           id="current_password" 
                           required 
                           autofocus
                           placeholder="Enter current temp password"
                           class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('current_password') ? 'border-[#FF3B3C]' : 'border-[#D1D1D6]' }} focus:border-[#780000] text-sm text-[#1D1D1F] bg-white">
                    
                    @error('current_password')
                        <p class="text-xs text-[#FF3B3C] font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- New Password -->
                <div>
                    <label for="password" class="block font-bold text-[#1D1D1F] mb-2 text-sm">
                        New Permanent Password <span class="text-[#780000]">*</span>
                    </label>
                    <input type="password" 
                           name="password" 
                           id="password" 
                           required 
                           placeholder="Min. 8 characters"
                           class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('password') ? 'border-[#FF3B3C]' : 'border-[#D1D1D6]' }} focus:border-[#780000] text-sm text-[#1D1D1F] bg-white">
                    
                    @error('password')
                        <p class="text-xs text-[#FF3B3C] font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm New Password -->
                <div>
                    <label for="password_confirmation" class="block font-bold text-[#1D1D1F] mb-2 text-sm">
                        Confirm New Permanent Password <span class="text-[#780000]">*</span>
                    </label>
                    <input type="password" 
                           name="password_confirmation" 
                           id="password_confirmation" 
                           required 
                           placeholder="Re-type new password"
                           class="w-full px-4 py-2.5 rounded-xl border border-[#D1D1D6] focus:border-[#780000] text-sm text-[#1D1D1F] bg-white">
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" class="btn-primary w-full py-3 text-sm font-bold shadow-md">
                        Set Password & Proceed to Dashboard →
                    </button>
                </div>
            </form>

            <div class="mt-5 pt-4 border-t border-[#E5E5EA] text-center">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs text-[#6E6E73] hover:text-[#1D1D1F] font-semibold underline">
                        Cancel & Sign Out
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
