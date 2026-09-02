<!DOCTYPE html>
<html lang="en" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password | Camp FreedivePH</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800,900" rel="stylesheet" />
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans bg-white text-[#1D1D1F]">

<div class="min-h-screen flex flex-col lg:flex-row">
    
    <!-- LEFT SIDE: PICTURE CONTAINER -->
    <div class="hidden lg:block lg:w-1/2 relative bg-[#1D1D1F] overflow-hidden min-h-screen">
        <img src="{{ asset('images/about-hero.jpg') }}" 
             alt="Camp FreedivePH Ocean" 
             class="absolute inset-0 w-full h-full object-cover opacity-85">
    </div>

    <!-- RIGHT SIDE: FORGOT PASSWORD FORM -->
    <div class="w-full lg:w-1/2 flex flex-col justify-center p-8 sm:p-12 lg:p-16 xl:p-20 bg-white min-h-screen">

        <!-- Form Container -->
        <div class="max-w-md w-full mx-auto py-8">
            
            <div class="space-y-2 mb-8">
                <h1 class="text-3xl sm:text-4xl font-bold text-[#1D1D1F] tracking-tight">Recover Password</h1>
                <p class="text-sm text-[#6E6E73]">Enter your registered staff email address to receive a secure password reset link.</p>
            </div>

            @if(session('status'))
                <div class="mb-6 p-4 rounded-xl bg-[#ECFDF5] border border-[#A7F3D0] text-xs font-semibold text-[#065F46] space-y-2">
                    <div>{{ session('status') }}</div>
                    @if(session('dev_reset_link'))
                        <div class="pt-2 border-t border-[#A7F3D0]/60">
                            <span class="font-bold text-[11px] uppercase tracking-wider block text-emerald-800 mb-1">Local Testing Link:</span>
                            <a href="{{ session('dev_reset_link') }}" class="font-bold underline text-[#780000] break-all block hover:text-[#500000]">
                                Click here to open Reset Password page
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-[#FEF2F2] border border-[#FECACA] text-xs font-semibold text-[#991B1B]">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Email Input -->
                <div class="space-y-1.5">
                    <label for="email" class="block font-bold text-[#1D1D1F] text-xs uppercase tracking-wider">
                        Registered Email <span class="text-[#780000]">*</span>
                    </label>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           value="{{ old('email') }}" 
                           placeholder="staff@campfreedive.ph" 
                           required 
                           autofocus
                           class="w-full px-4 py-3 rounded-xl border {{ $errors->has('email') ? 'border-[#FF3B3C]' : 'border-[#D1D1D6]' }} focus:border-[#780000] text-sm text-[#1D1D1F] bg-white transition-colors">
                    
                    @error('email')
                        <p class="text-xs text-[#FF3B3C] font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-primary w-full py-3.5 text-sm font-bold shadow-md hover:bg-[#5E0000] transition-all">
                    Send Password Reset Link
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-[#E5E5EA] text-center">
                <a href="{{ route('login') }}" class="text-xs font-bold text-[#780000] hover:underline flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                    <span>Return to Login</span>
                </a>
            </div>
        </div>

    </div>

</div>

</body>
</html>
