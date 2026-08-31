<!DOCTYPE html>
<html lang="en" class="h-full bg-[#FAFAFC]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
    <title>@yield('title', 'Staff Portal | Camp FreedivePH')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full text-[#1D1D1F] antialiased text-sm overflow-x-hidden min-w-[320px] bg-[#FAFAFC]" x-data="{ mobileMenuOpen: false }">

    <div class="min-h-full flex flex-col md:flex-row">

        <!-- ========================================================================= -->
        <!-- 1. DESKTOP LEFT SIDEBAR -->
        <!-- ========================================================================= -->
        <aside class="hidden md:flex md:w-64 md:flex-col md:fixed md:inset-y-0 bg-white border-r border-[#E5E5EA] z-30">
            
            <!-- Brand Logo & Context -->
            <div class="h-20 flex items-center px-6 border-b border-[#E5E5EA] bg-white">
                <a href="{{ auth()->user()->isCoach() ? route('coach.dashboard') : route('admin.dashboard') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/logo.png') }}" alt="Camp FreedivePH Logo" class="w-10 h-10 rounded-full object-contain bg-white shadow-sm border border-[#E5E5EA] group-hover:scale-105 transition-transform shrink-0">
                    <div class="min-w-0">
                        <span class="font-extrabold text-base text-[#1D1D1F] block leading-none truncate">Camp Freedive<span class="text-[#780000]">PH</span></span>
                    </div>
                </a>
            </div>

            <!-- Sidebar Navigation Links -->
            <nav class="flex-1 py-2 px-4 space-y-1.5 overflow-y-auto">
                @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
                    <!-- Dashboard -->
                    <a href="{{ route('admin.dashboard') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-[#780000]/10 text-[#780000] font-bold shadow-2xs' : 'text-[#3A3A3C] hover:bg-[#F2F2F7] hover:text-[#780000]' }}">
                        <img src="{{ asset('icons/icons8-home-32.png') }}" class="w-5 h-5 shrink-0" alt="Dashboard">
                        <span>Dashboard</span>
                    </a>

                    <!-- Bookings -->
                    <a href="{{ route('admin.bookings.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('admin.bookings.*') ? 'bg-[#780000]/10 text-[#780000] font-bold shadow-2xs' : 'text-[#3A3A3C] hover:bg-[#F2F2F7] hover:text-[#780000]' }}">
                        <img src="{{ asset('icons/icons8-booking-60.png') }}" class="w-5 h-5 shrink-0" alt="Bookings">
                        <span>Bookings</span>
                    </a>

                    <!-- Batches -->
                    <a href="{{ route('admin.batches.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('admin.batches.*') ? 'bg-[#780000]/10 text-[#780000] font-bold shadow-2xs' : 'text-[#3A3A3C] hover:bg-[#F2F2F7] hover:text-[#780000]' }}">
                        <img src="{{ asset('icons/icons8-medium-icons-32.png') }}" class="w-5 h-5 shrink-0" alt="Batches">
                        <span>Batches</span>
                    </a>

                    <!-- Weather & Marine Safety -->
                    <a href="{{ route('admin.weather.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('admin.weather.*') ? 'bg-[#780000]/10 text-[#780000] font-bold shadow-2xs' : 'text-[#3A3A3C] hover:bg-[#F2F2F7] hover:text-[#780000]' }}">
                        <img src="{{ asset('icons/icons8-warning-shield-32.png') }}" class="w-5 h-5 shrink-0" alt="SafetyMonitoring">
                        <span>Safety Monitoring</span>
                    </a>

                    <!-- Dynamic Pricing -->
                    <a href="{{ route('admin.pricing.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('admin.pricing.*') ? 'bg-[#780000]/10 text-[#780000] font-bold shadow-2xs' : 'text-[#3A3A3C] hover:bg-[#F2F2F7] hover:text-[#780000]' }}">
                        <img src="{{ asset('icons/icons8-price-tag-60.png') }}" class="w-5 h-5 shrink-0" alt="DynamicPricing">
                        <span>Dynamic Pricing</span>
                    </a>

                    <!-- Payments & Refunds -->
                    <a href="{{ route('admin.payments.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('admin.payments.*') ? 'bg-[#780000]/10 text-[#780000] font-bold shadow-2xs' : 'text-[#3A3A3C] hover:bg-[#F2F2F7] hover:text-[#780000]' }}">
                        <img src="{{ asset('icons/icons8-magnetic-card-60.png') }}" class="w-5 h-5 shrink-0" alt="PaymentsAndRefunds">
                        <span>Payments & Refunds</span>
                    </a>

                    <!-- Coaches & Schedules -->
                    <a href="{{ route('admin.coaches.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('admin.coaches.*') ? 'bg-[#780000]/10 text-[#780000] font-bold shadow-2xs' : 'text-[#3A3A3C] hover:bg-[#F2F2F7] hover:text-[#780000]' }}">
                        <img src="{{ asset('icons/icons8-coach-60.png') }}" class="w-5 h-5 shrink-0" alt="Coaches">
                        <span>Coaches & Schedules</span>
                    </a>

                    <!-- User Management -->
                    <a href="{{ route('admin.users.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('admin.users.*') ? 'bg-[#780000]/10 text-[#780000] font-bold shadow-2xs' : 'text-[#3A3A3C] hover:bg-[#F2F2F7] hover:text-[#780000]' }}">
                        <img src="{{ asset('icons/icons8-user-account-60.png') }}" class="w-5 h-5 shrink-0" alt="User">
                        <span>User Management</span>
                    </a>

                    <!-- Audit Logs (Owner Only) -->
                    @if(auth()->user()->isOwner())
                        <a href="{{ route('admin.audit_logs.index') }}" 
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('admin.audit_logs.*') ? 'bg-[#780000]/10 text-[#780000] font-bold shadow-2xs' : 'text-[#3A3A3C] hover:bg-[#F2F2F7] hover:text-[#780000]' }}">
                            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                            <span>Audit Logs</span>
                        </a>
                    @endif
                @else
                    <!-- Coach Portal Navigation -->
                    <!-- 1. Dashboard -->
                    <a href="{{ route('coach.dashboard') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('coach.dashboard') ? 'bg-[#780000]/10 text-[#780000] font-bold shadow-2xs' : 'text-[#3A3A3C] hover:bg-[#F2F2F7] hover:text-[#780000]' }}">
                        <img src="{{ asset('icons/icons8-home-32.png') }}" class="w-5 h-5 shrink-0" alt="Dashboard">
                        <span>Dashboard</span>
                    </a>

                    <!-- 2. Availability Calendar -->
                    <a href="{{ route('coach.availability.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('coach.availability.*') ? 'bg-[#780000]/10 text-[#780000] font-bold shadow-2xs' : 'text-[#3A3A3C] hover:bg-[#F2F2F7] hover:text-[#780000]' }}">
                        <svg class="w-5 h-5 shrink-0 text-current" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        <span>Availability Calendar</span>
                    </a>

                    <!-- 3. My Schedule & Students -->
                    <a href="{{ route('coach.schedule.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('coach.schedule.*') ? 'bg-[#780000]/10 text-[#780000] font-bold shadow-2xs' : 'text-[#3A3A3C] hover:bg-[#F2F2F7] hover:text-[#780000]' }}">
                        <img src="{{ asset('icons/icons8-booking-60.png') }}" class="w-5 h-5 shrink-0" alt="Schedule">
                        <span>My Schedule & History</span>
                    </a>

                    <!-- 4. Open Requests Board -->
                    <a href="{{ route('coach.requests.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('coach.requests.*') ? 'bg-[#780000]/10 text-[#780000] font-bold shadow-2xs' : 'text-[#3A3A3C] hover:bg-[#F2F2F7] hover:text-[#780000]' }}">
                        <img src="{{ asset('icons/icons8-coach-60.png') }}" class="w-5 h-5 shrink-0" alt="OpenRequests">
                        <span>Open Slot Requests</span>
                    </a>
                @endif
            </nav>

            <!-- Bottom User Info & Sign Out -->
            <div class="p-3 border-t border-[#E5E5EA] bg-white">
                <div class="flex items-center justify-between gap-2.5 p-3 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA]">
                    <div class="min-w-0 flex-1">
                        <div class="font-bold text-xs text-[#1D1D1F] truncate">{{ auth()->user()->name }}</div>
                        <div class="text-[11px] text-[#6E6E73] truncate mt-0.5">{{ auth()->user()->email }}</div>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                        @csrf
                        <button type="submit" 
                                title="Sign Out" 
                                class="p-2 rounded-xl text-[#8E8E93] hover:text-[#FF3B30] hover:bg-[#FEE2E2] transition-all flex items-center justify-center border border-transparent hover:border-[#FECACA]">
                            <img src="{{ asset('icons/icons8-logout-60.png') }}" class="w-5 h-5 object-contain" alt="Sign Out">
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- ========================================================================= -->
        <!-- 2. MOBILE TOP HEADER & DRAWER -->
        <!-- ========================================================================= -->
        <div class="md:hidden sticky top-0 z-40 bg-white border-b border-[#E5E5EA] px-4 h-16 flex items-center justify-between">
            <a href="{{ auth()->user()->isCoach() ? route('coach.dashboard') : route('admin.dashboard') }}" class="flex items-center gap-2.5">
                <img src="{{ asset('images/logo.png') }}" alt="Camp FreedivePH" class="w-8 h-8 rounded-full object-contain bg-white border border-[#E5E5EA]">
                <span class="font-extrabold text-base text-[#1D1D1F]">Camp Freedive<span class="text-[#780000]">PH</span></span>
            </a>

            <button type="button" 
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    class="p-2 rounded-xl border border-[#E5E5EA] text-[#1D1D1F] hover:bg-[#F2F2F7]">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
        </div>

        <!-- Mobile Drawer Overlay -->
        <div x-show="mobileMenuOpen" x-cloak class="md:hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm" @click="mobileMenuOpen = false"></div>

        <!-- Mobile Drawer Content -->
        <div x-show="mobileMenuOpen" 
             x-cloak 
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="md:hidden fixed inset-y-0 left-0 z-50 w-72 bg-white flex flex-col justify-between shadow-2xl border-r border-[#E5E5EA]">
            
            <div>
                <div class="h-16 flex items-center justify-between px-6 border-b border-[#E5E5EA]">
                    <span class="font-extrabold text-base text-[#1D1D1F]">Camp Freedive<span class="text-[#780000]">PH</span></span>
                    <button type="button" @click="mobileMenuOpen = false" class="text-lg font-bold text-[#8E8E93]">✕</button>
                </div>

                <nav class="px-4 pt-2 space-y-1.5 overflow-y-auto">
                    @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold {{ request()->routeIs('admin.dashboard') ? 'bg-[#780000]/10 text-[#780000] font-bold' : 'text-[#3A3A3C] hover:bg-[#F2F2F7]' }}">
                            <img src="{{ asset('icons/icons8-home-32.png') }}" class="w-5 h-5 shrink-0" alt="Dashboard">
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('admin.bookings.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold {{ request()->routeIs('admin.bookings.*') ? 'bg-[#780000]/10 text-[#780000] font-bold' : 'text-[#3A3A3C] hover:bg-[#F2F2F7]' }}">
                            <img src="{{ asset('icons/icons8-booking-60.png') }}" class="w-5 h-5 shrink-0" alt="Bookings">
                            <span>Bookings</span>
                        </a>
                        <a href="{{ route('admin.batches.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold {{ request()->routeIs('admin.batches.*') ? 'bg-[#780000]/10 text-[#780000] font-bold' : 'text-[#3A3A3C] hover:bg-[#F2F2F7]' }}">
                            <img src="{{ asset('icons/icons8-medium-icons-32.png') }}" class="w-5 h-5 shrink-0" alt="Batches">
                            <span>Batches</span>
                        </a>
                        <a href="{{ route('admin.weather.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold {{ request()->routeIs('admin.weather.*') ? 'bg-[#780000]/10 text-[#780000] font-bold' : 'text-[#3A3A3C] hover:bg-[#F2F2F7]' }}">
                            <img src="{{ asset('icons/icons8-warning-shield-32.png') }}" class="w-5 h-5 shrink-0" alt="SafetyMonitoring">
                            <span>Safety Monitoring</span>
                        </a>
                        <a href="{{ route('admin.pricing.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold {{ request()->routeIs('admin.pricing.*') ? 'bg-[#780000]/10 text-[#780000] font-bold' : 'text-[#3A3A3C] hover:bg-[#F2F2F7]' }}">
                            <img src="{{ asset('icons/icons8-price-tag-60.png') }}" class="w-5 h-5 shrink-0" alt="DynamicPricing">
                            <span>Dynamic Pricing</span>
                        </a>
                        <a href="{{ route('admin.payments.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold {{ request()->routeIs('admin.payments.*') ? 'bg-[#780000]/10 text-[#780000] font-bold' : 'text-[#3A3A3C] hover:bg-[#F2F2F7]' }}">
                            <img src="{{ asset('icons/icons8-magnetic-card-60.png') }}" class="w-5 h-5 shrink-0" alt="PaymentsAndRefunds">
                            <span>Payments & Refunds</span>
                        </a>
                        <a href="{{ route('admin.coaches.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold {{ request()->routeIs('admin.coaches.*') ? 'bg-[#780000]/10 text-[#780000] font-bold' : 'text-[#3A3A3C] hover:bg-[#F2F2F7]' }}">
                            <img src="{{ asset('icons/icons8-coach-60.png') }}" class="w-5 h-5 shrink-0" alt="Coaches">
                            <span>Coaches & Schedules</span>
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold {{ request()->routeIs('admin.users.*') ? 'bg-[#780000]/10 text-[#780000] font-bold' : 'text-[#3A3A3C] hover:bg-[#F2F2F7]' }}">
                            <img src="{{ asset('icons/icons8-user-account-60.png') }}" class="w-5 h-5 shrink-0" alt="User">
                            <span>User Management</span>
                        </a>
                        @if(auth()->user()->isOwner())
                            <a href="{{ route('admin.audit_logs.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold {{ request()->routeIs('admin.audit_logs.*') ? 'bg-[#780000]/10 text-[#780000] font-bold' : 'text-[#3A3A3C] hover:bg-[#F2F2F7]' }}">
                                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                <span>Audit Logs</span>
                            </a>
                        @endif
                    @else
                        <!-- Coach Portal Mobile Navigation -->
                        <a href="{{ route('coach.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold {{ request()->routeIs('coach.dashboard') ? 'bg-[#780000]/10 text-[#780000] font-bold' : 'text-[#3A3A3C] hover:bg-[#F2F2F7]' }}">
                            <img src="{{ asset('icons/icons8-home-32.png') }}" class="w-5 h-5 shrink-0" alt="Dashboard">
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('coach.availability.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold {{ request()->routeIs('coach.availability.*') ? 'bg-[#780000]/10 text-[#780000] font-bold' : 'text-[#3A3A3C] hover:bg-[#F2F2F7]' }}">
                            <svg class="w-5 h-5 shrink-0 text-current" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            <span>Availability Calendar</span>
                        </a>
                        <a href="{{ route('coach.schedule.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold {{ request()->routeIs('coach.schedule.*') ? 'bg-[#780000]/10 text-[#780000] font-bold' : 'text-[#3A3A3C] hover:bg-[#F2F2F7]' }}">
                            <img src="{{ asset('icons/icons8-booking-60.png') }}" class="w-5 h-5 shrink-0" alt="Schedule">
                            <span>My Schedule & History</span>
                        </a>
                        <a href="{{ route('coach.requests.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold {{ request()->routeIs('coach.requests.*') ? 'bg-[#780000]/10 text-[#780000] font-bold' : 'text-[#3A3A3C] hover:bg-[#F2F2F7]' }}">
                            <img src="{{ asset('icons/icons8-coach-60.png') }}" class="w-5 h-5 shrink-0" alt="OpenRequests">
                            <span>Open Slot Requests</span>
                        </a>
                    @endif
                </nav>
            </div>

            <div class="p-3 border-t border-[#E5E5EA] bg-white">
                <div class="flex items-center justify-between gap-2.5 p-2.5 rounded-xl bg-[#FAFAFC] border border-[#E5E5EA]">
                    <div class="min-w-0 flex-1">
                        <div class="font-bold text-xs text-[#1D1D1F] truncate">{{ auth()->user()->name }}</div>
                        <div class="text-[11px] text-[#6E6E73] truncate mt-0.5">{{ auth()->user()->email }}</div>
                        <div class="mt-1">
                        </div>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                        @csrf
                        <button type="submit" 
                                title="Sign Out" 
                                class="p-2 rounded-xl text-[#8E8E93] hover:text-[#FF3B30] hover:bg-[#FEE2E2] transition-all flex items-center justify-center border border-transparent hover:border-[#FECACA]">
                            <img src="{{ asset('icons/icons8-logout-60.png') }}" class="w-5 h-5 object-contain" alt="Sign Out">
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- 3. MAIN CONTENT CONTAINER -->
        <!-- ========================================================================= -->
        <main class="flex-1 md:pl-64 flex flex-col min-h-screen">
            
            <!-- Global Flash Messages -->
            <div class="px-4 sm:px-8 pt-6">
                @if(session('success'))
                    <div class="p-4 mb-4 rounded-xl bg-[#ECFDF5] border border-[#A7F3D0] text-[#065F46] flex items-center justify-between text-xs sm:text-sm font-medium shadow-sm">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button type="button" @click="$el.parentElement.remove()" class="text-xs font-bold text-[#065F46]/60 hover:text-[#065F46]">✕</button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="p-4 mb-4 rounded-xl bg-[#FEF2F2] border border-[#FECACA] text-[#991B1B] flex items-center justify-between text-xs sm:text-sm font-medium shadow-sm">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2 h-2 rounded-full bg-[#FF3B3C]"></span>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button type="button" @click="$el.parentElement.remove()" class="text-xs font-bold text-[#991B1B]/60 hover:text-[#991B1B]">✕</button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="p-4 mb-4 rounded-xl bg-[#EFF6FF] border border-[#BFDBFE] text-[#1E40AF] flex items-center justify-between text-xs sm:text-sm font-medium shadow-sm">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2 h-2 rounded-full bg-[#0088FF]"></span>
                            <span>{{ session('info') }}</span>
                        </div>
                        <button type="button" @click="$el.parentElement.remove()" class="text-xs font-bold text-[#1E40AF]/60 hover:text-[#1E40AF]">✕</button>
                    </div>
                @endif
            </div>

            <!-- Page Specific Content -->
            <div class="flex-1 px-4 sm:px-6 lg:px-8 xl:px-10 pb-12 pt-2 max-w-[1600px] w-full mx-auto">
                @yield('content')
            </div>

            <!-- Footer Note -->

        </main>

    </div>

    @stack('scripts')
</body>
</html>
