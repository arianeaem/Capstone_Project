<!DOCTYPE html>
<html lang="en" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Camp FreedivePH</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans bg-white text-[#1D1D1F]">

<div class="min-h-screen flex flex-col lg:flex-row">
    
    <!-- Auth Brand Banner -->
    <div class="hidden lg:block lg:w-1/2 relative bg-[#1D1D1F] overflow-hidden min-h-screen">
        <img src="{{ asset('images/bg-auth.png') }}" 
             alt="Camp FreedivePH Ocean" 
             class="absolute inset-0 w-full h-full object-cover">
    </div>

    <!-- Login Form -->
    <div class="w-full lg:w-1/2 flex flex-col justify-center p-8 sm:p-12 lg:p-16 xl:p-20 bg-white min-h-screen">

        <!-- Form Container -->
        <div class="max-w-md w-full mx-auto py-8">
            <div class="space-y-2 mb-8">
                <h1 class="text-3xl sm:text-4xl font-bold text-[#1D1D1F] tracking-tight">Welcome Back</h1>
                <p class="text-sm text-[#6E6E73]">Please enter your details to access the dashboard.</p>
            </div>

            @if(session('error') || (isset($errors) && ($errors->has('email') || $errors->has('password'))))
                <div class="mb-6 p-4 rounded-xl bg-[#FEF2F2] flex items-center gap-3">
                    <div class="text-sm font-semibold text-[#991B1B] leading-relaxed">
                        {{ session('error') ?? ($errors->first('password') ?: $errors->first('email')) }}
                    </div>
                </div>
            @endif

            @if(session('status') || session('success'))
                <div class="mb-6 p-4 rounded-xl bg-[#ECFDF5] flex items-center gap-3">
                    <div class="text-sm font-semibold text-[#065F46] leading-relaxed">
                        {{ session('status') ?? session('success') }}
                    </div>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-5" data-no-spa data-native>
                @csrf

                <!-- Email Input -->
                <div class="space-y-1.5">
                    <label for="email" class="block font-bold text-[#1D1D1F] text-sm uppercase tracking-wider">
                        Email Address <span class="text-[#780000]">*</span>
                    </label>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           value="{{ old('email') }}" 
                           placeholder="name@example.com" 
                           required 
                           autofocus
                           class="w-full px-4 py-3 rounded-xl border {{ (isset($errors) && $errors->has('email') && !session('error')) ? 'border-[#FF3B3C] ring-2 ring-[#FF3B3C]/20 bg-rose-50/10' : 'border-[#D1D1D6]' }} focus:border-[#780000] focus:ring-2 focus:ring-[#780000]/20 text-sm text-[#1D1D1F] bg-white transition-all">
                </div>

                <!-- Password Input -->
                <div class="space-y-1.5" x-data="{ show: false }">
                    <div class="flex items-center justify-between">
                        <label for="password" class="block font-bold text-[#1D1D1F] text-sm uppercase tracking-wider">
                            Password <span class="text-[#780000]">*</span>
                        </label>
                        <a href="{{ route('password.request') }}" class="text-sm font-semibold text-[#780000] hover:underline" data-no-spa>
                            Forgot password?
                        </a>
                    </div>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" 
                               name="password" 
                               id="password" 
                               placeholder="••••••••" 
                               required
                               class="w-full px-4 py-3 rounded-xl border {{ (session('error') || (isset($errors) && $errors->has('password'))) ? 'border-[#FF3B3C] ring-2 ring-[#FF3B3C]/20 bg-rose-50/10' : 'border-[#D1D1D6]' }} focus:border-[#780000] focus:ring-2 focus:ring-[#780000]/20 text-sm text-[#1D1D1F] bg-white transition-all pr-11">
                        <button type="button" 
                                @click="show = !show" 
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-[#8E8E93] hover:text-[#1D1D1F] p-1">
                            <svg x-show="!show" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            <svg x-show="show" x-cloak class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded text-[#780000] focus:ring-0 border-[#D1D1D6]">
                        <span class="text-sm text-[#6E6E73] font-medium">Remember my session</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-primary w-full py-3.5 text-sm font-bold shadow-md hover:bg-[#5E0000] transition-all cursor-pointer">
                    Sign In
                </button>
            </form>
        </div>

    </div>

</div>

</body>
</html>
