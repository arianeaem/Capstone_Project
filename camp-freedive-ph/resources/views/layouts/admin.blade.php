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
            <nav class="flex-1 px-4 space-y-1.5 overflow-y-auto">
                @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
                    <!-- Dashboard -->
                    <a href="{{ route('admin.dashboard') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-[#780000] text-white shadow-sm' : 'text-[#1D1D1F] hover:bg-[#F2F2F7] hover:text-[#780000]' }}">
                        <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- Bookings -->
                    <a href="{{ route('admin.bookings.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('admin.bookings.*') ? 'bg-[#780000] text-white shadow-sm' : 'text-[#1D1D1F] hover:bg-[#F2F2F7] hover:text-[#780000]' }}">
                        <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        <span>Bookings</span>
                    </a>

                    <!-- Batches -->
                    <a href="{{ route('admin.batches.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('admin.batches.*') ? 'bg-[#780000] text-white shadow-sm' : 'text-[#1D1D1F] hover:bg-[#F2F2F7] hover:text-[#780000]' }}">
                        <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        <span>Batches</span>
                    </a>

                    <!-- Weather & Marine Safety -->
                    <a href="{{ route('admin.weather.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('admin.weather.*') ? 'bg-[#780000] text-white shadow-sm' : 'text-[#1D1D1F] hover:bg-[#F2F2F7] hover:text-[#780000]' }}">
                        <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"></path></svg>
                        <span>Weather & Safety</span>
                    </a>

                    <!-- Dynamic Pricing -->
                    <a href="{{ route('admin.pricing.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('admin.pricing.*') ? 'bg-[#780000] text-white shadow-sm' : 'text-[#1D1D1F] hover:bg-[#F2F2F7] hover:text-[#780000]' }}">
                        <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                        <span>Dynamic Pricing</span>
                    </a>

                    <!-- Payments & Refunds -->
                    <a href="{{ route('admin.payments.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('admin.payments.*') ? 'bg-[#780000] text-white shadow-sm' : 'text-[#1D1D1F] hover:bg-[#F2F2F7] hover:text-[#780000]' }}">
                        <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                        <span>Payments & Refunds</span>
                    </a>

                    <!-- Coaches & Schedules -->
                    <a href="{{ route('admin.coaches.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('admin.coaches.*') ? 'bg-[#780000] text-white shadow-sm' : 'text-[#1D1D1F] hover:bg-[#F2F2F7] hover:text-[#780000]' }}">
                        <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>
                        <span>Coaches & Schedules</span>
                    </a>

                    <!-- User Management -->
                    <a href="{{ route('admin.users.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('admin.users.*') ? 'bg-[#780000] text-white shadow-sm' : 'text-[#1D1D1F] hover:bg-[#F2F2F7] hover:text-[#780000]' }}">
                        <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        <span>User Management</span>
                    </a>

                    <!-- Audit Logs (Owner Only) -->
                    @if(auth()->user()->isOwner())
                        <a href="{{ route('admin.audit_logs.index') }}" 
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('admin.audit_logs.*') ? 'bg-[#780000] text-white shadow-sm' : 'text-[#1D1D1F] hover:bg-[#F2F2F7] hover:text-[#780000]' }}">
                            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                            <span>Audit Logs</span>
                        </a>
                    @endif
                @else
                    <!-- Coach Portal -->
                    <a href="{{ route('coach.dashboard') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition-all {{ request()->routeIs('coach.dashboard') ? 'bg-[#780000] text-white shadow-sm' : 'text-[#1D1D1F] hover:bg-[#F2F2F7] hover:text-[#780000]' }}">
                        <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        <span>My Schedule & Students</span>
                    </a>
                @endif
            </nav>

            <!-- Active User Info Badge -->
            <div class="p-4 mx-4 my-4 rounded-xl bg-[#FAFAFC]">
                <div class="font-bold text-sm text-[#1D1D1F] truncate">{{ auth()->user()->name }}</div>
                <div class="text-xs text-[#6E6E73] truncate mt-0.5">{{ auth()->user()->email }}</div>
                <div class="mt-2">
                    <span class="text-xs px-2.5 py-0.5 rounded-full inline-block font-semibold {{ auth()->user()->role_badge['class'] }}">
                        {{ auth()->user()->role_badge['label'] }}
                    </span>
                </div>
            </div>

            <!-- Bottom Actions -->
            <div class="p-4 border-t border-[#E5E5EA] space-y-2 bg-white">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2.5 text-xs font-bold text-[#FF3B3C] hover:bg-[#FEE2E2] rounded-xl border border-[#FECACA] transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        <span>Sign Out</span>
                    </button>
                </form>
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

                <div class="p-4">
                    <div class="font-bold text-sm text-[#1D1D1F]">{{ auth()->user()->name }}</div>
                    <span class="text-xs px-2 py-0.5 rounded-full inline-block border font-semibold mt-1 {{ auth()->user()->role_badge['class'] }}">
                        {{ auth()->user()->role_badge['label'] }}
                    </span>
                </div>

                <nav class="px-4 space-y-1.5">
                    @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold {{ request()->routeIs('admin.dashboard') ? 'bg-[#780000] text-white' : 'text-[#1D1D1F] hover:bg-[#F2F2F7]' }}">
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('admin.bookings.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold {{ request()->routeIs('admin.bookings.*') ? 'bg-[#780000] text-white' : 'text-[#1D1D1F] hover:bg-[#F2F2F7]' }}">
                            <span>Bookings</span>
                        </a>
                        <a href="{{ route('admin.batches.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold {{ request()->routeIs('admin.batches.*') ? 'bg-[#780000] text-white' : 'text-[#1D1D1F] hover:bg-[#F2F2F7]' }}">
                            <span>Batches</span>
                        </a>
                        <a href="{{ route('admin.weather.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold {{ request()->routeIs('admin.weather.*') ? 'bg-[#780000] text-white' : 'text-[#1D1D1F] hover:bg-[#F2F2F7]' }}">
                            <span>Weather & Safety</span>
                        </a>
                        <a href="{{ route('admin.payments.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold {{ request()->routeIs('admin.payments.*') ? 'bg-[#780000] text-white' : 'text-[#1D1D1F] hover:bg-[#F2F2F7]' }}">
                            <span>Payments & Refunds</span>
                        </a>
                        <a href="{{ route('admin.coaches.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold {{ request()->routeIs('admin.coaches.*') ? 'bg-[#780000] text-white' : 'text-[#1D1D1F] hover:bg-[#F2F2F7]' }}">
                            <span>Coaches & Schedules</span>
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold {{ request()->routeIs('admin.users.*') ? 'bg-[#780000] text-white' : 'text-[#1D1D1F] hover:bg-[#F2F2F7]' }}">
                            <span>User Management</span>
                        </a>
                        @if(auth()->user()->isOwner())
                            <a href="{{ route('admin.audit_logs.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold {{ request()->routeIs('admin.audit_logs.*') ? 'bg-[#780000] text-white' : 'text-[#1D1D1F] hover:bg-[#F2F2F7]' }}">
                                <span>Audit Logs</span>
                            </a>
                        @endif
                    @else
                        <a href="{{ route('coach.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold {{ request()->routeIs('coach.dashboard') ? 'bg-[#780000] text-white' : 'text-[#1D1D1F] hover:bg-[#F2F2F7]' }}">
                            <span>My Schedule & Students</span>
                        </a>
                    @endif
                </nav>
            </div>

            <div class="p-4 border-t border-[#E5E5EA] space-y-2">
                <a href="{{ route('landing') }}" class="flex items-center gap-2 px-3 py-2 text-xs font-semibold text-[#6E6E73] hover:text-[#1D1D1F] rounded-xl">
                    <span>View Customer Site</span>
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 text-xs font-bold text-[#FF3B3C] bg-[#FEF2F2] rounded-xl border border-[#FECACA]">
                        <span>Sign Out</span>
                    </button>
                </form>
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
