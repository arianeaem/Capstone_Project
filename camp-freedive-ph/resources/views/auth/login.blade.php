@extends('layouts.app')

@section('title', 'Internal Staff Login | Camp FreedivePH')
@section('meta_description', 'Internal portal login for Camp FreedivePH coaches, administrators, and owner.')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center px-4 sm:px-6 lg:px-8 py-10">
    <div class="max-w-md w-full space-y-6">
        
        <!-- Header & Logo -->
        <div class="text-center">
            <h1 class="text-2xl font-extrabold text-[#1D1D1F] tracking-tight">Login</h1>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-xl border border-[#E5E5EA] p-6 sm:p-8 space-y-5">

            <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="block font-bold text-[#1D1D1F] mb-1.5 text-sm">
                        Staff Email Address <span class="text-[#780000]">*</span>
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

                <!-- Password Input -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block font-bold text-[#1D1D1F] text-sm">
                            Password <span class="text-[#780000]">*</span>
                        </label>
                        <a href="{{ route('password.request') }}" class="text-xs font-semibold text-[#780000] hover:underline">
                            Forgot password?
                        </a>
                    </div>
                    <input type="password" 
                           name="password" 
                           id="password" 
                           placeholder="••••••••" 
                           required
                           class="w-full px-4 py-3 rounded-xl border {{ $errors->has('password') ? 'border-[#FF3B3C] ring-1 ring-[#FF3B3C]' : 'border-[#D1D1D6]' }} focus:border-[#780000] focus:ring-2 focus:ring-[#780000]/20 text-sm text-[#1D1D1F] bg-white">
                    
                    @error('password')
                        <p class="text-xs text-[#FF3B3C] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded text-[#780000] focus:ring-[#780000] border-[#D1D1D6]">
                        <span class="text-sm text-[#6E6E73] font-medium">Remember my session</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-primary w-full py-3.5 text-sm font-bold shadow-md">
                    Sign In to Portal
                </button>
            </form>

        </div>

    </div>
</div>
@endsection
