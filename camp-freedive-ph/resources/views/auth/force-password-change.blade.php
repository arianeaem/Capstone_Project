<!DOCTYPE html>
<html lang="en" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>First-Time Password Setup | Camp FreedivePH</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800,900" rel="stylesheet" />
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans bg-white text-[#1D1D1F]">

<div class="min-h-screen flex flex-col lg:flex-row">
    
    <!-- Auth Brand Banner -->
    <div class="hidden lg:block lg:w-1/2 relative bg-[#1D1D1F] overflow-hidden min-h-screen">
        <img src="{{ asset('images/about-hero.jpg') }}" 
             alt="Camp FreedivePH Ocean" 
             class="absolute inset-0 w-full h-full object-cover opacity-85">
    </div>

    <!-- Force Password Change Form -->
    <div class="w-full lg:w-1/2 flex flex-col justify-center p-8 sm:p-12 lg:p-16 xl:p-20 bg-white min-h-screen">

        <!-- Form Container -->
        <div class="max-w-md w-full mx-auto py-8">
            
            <div class="space-y-2 mb-8">
                <h1 class="text-3xl sm:text-4xl font-bold text-[#1D1D1F] tracking-tight">First-Time Setup</h1>
                <p class="text-sm text-[#6E6E73]">
                    Welcome, <strong class="text-[#1D1D1F]">{{ $user->name }}</strong>! Please set a private, secure password before accessing your dashboard.
                </p>
            </div>

            @if(session('warning'))
                <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-xs font-semibold text-amber-900 leading-relaxed">
                    {{ session('warning') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-[#FEF2F2] border border-[#FECACA] text-xs font-semibold text-[#991B1B]">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('password.force_change.update') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Current Temporary Password -->
                <div class="space-y-1.5" x-data="{ show: false }">
                    <label for="current_password" class="block font-bold text-[#1D1D1F] text-xs uppercase tracking-wider">
                        Current Temporary Password <span class="text-[#780000]">*</span>
                    </label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" 
                               name="current_password" 
                               id="current_password" 
                               required 
                               autofocus
                               placeholder="Enter temporary password"
                               class="w-full px-4 py-3 rounded-xl border {{ $errors->has('current_password') ? 'border-[#FF3B3C]' : 'border-[#D1D1D6]' }} focus:border-[#780000] text-sm text-[#1D1D1F] bg-white transition-colors pr-11">
                        <button type="button" 
                                @click="show = !show" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-[#8E8E93] hover:text-[#1D1D1F] p-1">
                            <svg x-show="!show" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            <svg x-show="show" x-cloak class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="text-xs text-[#FF3B3C] font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- New Permanent Password -->
                <div class="space-y-1.5" x-data="{ show: false }">
                    <label for="password" class="block font-bold text-[#1D1D1F] text-xs uppercase tracking-wider">
                        New Permanent Password <span class="text-[#780000]">*</span>
                    </label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" 
                               name="password" 
                               id="password" 
                               required 
                               placeholder="Min. 8 characters"
                               class="w-full px-4 py-3 rounded-xl border {{ $errors->has('password') ? 'border-[#FF3B3C]' : 'border-[#D1D1D6]' }} focus:border-[#780000] text-sm text-[#1D1D1F] bg-white transition-colors pr-11">
                        <button type="button" 
                                @click="show = !show" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-[#8E8E93] hover:text-[#1D1D1F] p-1">
                            <svg x-show="!show" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            <svg x-show="show" x-cloak class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-xs text-[#FF3B3C] font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm New Password -->
                <div class="space-y-1.5" x-data="{ show: false }">
                    <label for="password_confirmation" class="block font-bold text-[#1D1D1F] text-xs uppercase tracking-wider">
                        Confirm New Permanent Password <span class="text-[#780000]">*</span>
                    </label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" 
                               name="password_confirmation" 
                               id="password_confirmation" 
                               required 
                               placeholder="Re-type new password"
                               class="w-full px-4 py-3 rounded-xl border border-[#D1D1D6] focus:border-[#780000] text-sm text-[#1D1D1F] bg-white transition-colors pr-11">
                        <button type="button" 
                                @click="show = !show" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-[#8E8E93] hover:text-[#1D1D1F] p-1">
                            <svg x-show="!show" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            <svg x-show="show" x-cloak class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-primary w-full py-3.5 text-sm font-bold shadow-md hover:bg-[#5E0000] transition-all">
                    Set Password
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-[#E5E5EA] text-center">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs text-[#6E6E73] hover:text-[#780000] font-semibold underline cursor-pointer">
                        Sign Out
                    </button>
                </form>
            </div>
        </div>

    </div>

</div>

</body>
</html>
