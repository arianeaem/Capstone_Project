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
<body class="h-full text-[#1D1D1F] antialiased text-sm overflow-x-hidden min-w-[320px] bg-[#FAFAFC]"
      x-data="{ 
          sidebarCollapsed: localStorage.getItem('sidebar_collapsed') === 'true',
          mobileMenuOpen: false,
          toggleSidebar() {
              this.sidebarCollapsed = !this.sidebarCollapsed;
              localStorage.setItem('sidebar_collapsed', this.sidebarCollapsed);
          }
      }">

@php
    $userName = auth()->user()->name ?? 'User';
    $nameParts = array_values(array_filter(explode(' ', trim($userName))));
    $userInitials = '';
    if (count($nameParts) >= 2) {
        $userInitials = strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1));
    } else {
        $userInitials = strtoupper(substr($nameParts[0] ?? 'U', 0, 2));
    }

    $routeName = request()->route() ? request()->route()->getName() : '';
    $pageBreadcrumbTitle = match(true) {
        str_starts_with($routeName, 'admin.dashboard') => 'Dashboard',
        str_starts_with($routeName, 'admin.bookings') => 'Bookings',
        str_starts_with($routeName, 'admin.batches') => 'Batches',
        str_starts_with($routeName, 'admin.weather') => 'Safety Monitoring',
        str_starts_with($routeName, 'admin.pricing') => 'Dynamic Pricing',
        str_starts_with($routeName, 'admin.payments') => 'Payments',
        str_starts_with($routeName, 'admin.coaches') => 'Coaches & Schedules',
        str_starts_with($routeName, 'admin.users') => 'User Management',
        str_starts_with($routeName, 'admin.audit_logs') => 'Audit Logs',
        str_starts_with($routeName, 'coach.dashboard') => 'Dashboard',
        str_starts_with($routeName, 'coach.availability') => 'Availability Calendar',
        str_starts_with($routeName, 'coach.schedule') => 'My Schedule & History',
        str_starts_with($routeName, 'coach.requests') => 'Open Slot Requests',
        default => 'Dashboard',
    };
@endphp

    <div class="min-h-full flex flex-col md:flex-row">

        <!-- ========================================================================= -->
        <!-- 1. DESKTOP LEFT SIDEBAR (COLLAPSIBLE) -->
        <!-- ========================================================================= -->
        <aside class="hidden md:flex md:flex-col md:fixed md:inset-y-0 bg-white border-r border-[#E5E5EA] z-30 transition-all duration-300"
               :class="sidebarCollapsed ? 'md:w-20' : 'md:w-64'">
            
            <!-- Top: User First & Last Name / Profile Info (No bottom border) -->
            <div class="h-16 flex items-center bg-white transition-all overflow-hidden"
                 :class="sidebarCollapsed ? 'justify-center px-2' : 'px-4'">
                
                <!-- Expanded State: Initials Avatar + Full First & Last Name -->
                <div x-show="!sidebarCollapsed" class="flex items-center gap-2.5 min-w-0 flex-1">
                    <div class="w-8 h-8 rounded-full bg-[#F8EAEA] text-[#780000] border border-[#780000] flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                        {{ $userInitials }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="font-bold text-xs text-[#1D1D1F] truncate leading-tight">{{ auth()->user()->name }}</div>
                        <div class="text-[10px] font-semibold text-[#6E6E73] truncate uppercase tracking-wider mt-0.5">
                            {{ auth()->user()->role_label ?? ucfirst(auth()->user()->role) }}
                        </div>
                    </div>
                </div>

                <!-- Collapsed State: Compact Initials Avatar -->
                <div x-show="sidebarCollapsed" class="flex items-center justify-center">
                    <div class="w-9 h-9 rounded-full bg-[#F8EAEA] text-[#780000] border border-[#780000] flex items-center justify-center font-bold text-xs shadow-2xs cursor-pointer"
                         title="{{ auth()->user()->name }} ({{ auth()->user()->role_label ?? ucfirst(auth()->user()->role) }})">
                        {{ $userInitials }}
                    </div>
                </div>
            </div>

            <!-- Sidebar Navigation Links -->
            <nav class="flex-1 py-3 space-y-1.5 overflow-y-auto"
                 :class="sidebarCollapsed ? 'px-2' : 'px-3'">
                @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
                    <!-- Dashboard -->
                    <a href="{{ route('admin.dashboard') }}" 
                       title="Dashboard"
                       class="flex items-center rounded-xl font-semibold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-[#780000]/10 text-[#780000] font-bold shadow-2xs' : 'text-[#3A3A3C] hover:bg-[#F2F2F7] hover:text-[#780000]' }}"
                       :class="sidebarCollapsed ? 'justify-center p-2.5' : 'gap-3 px-3.5 py-2.5'">
                        <img src="{{ asset('icons/icons8-home-32.png') }}" class="w-5 h-5 shrink-0" alt="Dashboard">
                        <span x-show="!sidebarCollapsed" class="truncate">Dashboard</span>
                    </a>

                    <!-- Bookings -->
                    <a href="{{ route('admin.bookings.index') }}" 
                       title="Bookings"
                       class="flex items-center rounded-xl font-semibold transition-all {{ request()->routeIs('admin.bookings.*') ? 'bg-[#780000]/10 text-[#780000] font-bold shadow-2xs' : 'text-[#3A3A3C] hover:bg-[#F2F2F7] hover:text-[#780000]' }}"
                       :class="sidebarCollapsed ? 'justify-center p-2.5' : 'gap-3 px-3.5 py-2.5'">
                        <img src="{{ asset('icons/icons8-booking-60.png') }}" class="w-5 h-5 shrink-0" alt="Bookings">
                        <span x-show="!sidebarCollapsed" class="truncate">Bookings</span>
                    </a>

                    <!-- Batches -->
                    <a href="{{ route('admin.batches.index') }}" 
                       title="Batches"
                       class="flex items-center rounded-xl font-semibold transition-all {{ request()->routeIs('admin.batches.*') ? 'bg-[#780000]/10 text-[#780000] font-bold shadow-2xs' : 'text-[#3A3A3C] hover:bg-[#F2F2F7] hover:text-[#780000]' }}"
                       :class="sidebarCollapsed ? 'justify-center p-2.5' : 'gap-3 px-3.5 py-2.5'">
                        <img src="{{ asset('icons/icons8-medium-icons-32.png') }}" class="w-5 h-5 shrink-0" alt="Batches">
                        <span x-show="!sidebarCollapsed" class="truncate">Batches</span>
                    </a>

                    <!-- Weather & Marine Safety -->
                    <a href="{{ route('admin.weather.index') }}" 
                       title="Safety Monitoring"
                       class="flex items-center rounded-xl font-semibold transition-all {{ request()->routeIs('admin.weather.*') ? 'bg-[#780000]/10 text-[#780000] font-bold shadow-2xs' : 'text-[#3A3A3C] hover:bg-[#F2F2F7] hover:text-[#780000]' }}"
                       :class="sidebarCollapsed ? 'justify-center p-2.5' : 'gap-3 px-3.5 py-2.5'">
                        <img src="{{ asset('icons/icons8-warning-shield-32.png') }}" class="w-5 h-5 shrink-0" alt="SafetyMonitoring">
                        <span x-show="!sidebarCollapsed" class="truncate">Safety Monitoring</span>
                    </a>

                    <!-- Dynamic Pricing -->
                    <a href="{{ route('admin.pricing.index') }}" 
                       title="Dynamic Pricing"
                       class="flex items-center rounded-xl font-semibold transition-all {{ request()->routeIs('admin.pricing.*') ? 'bg-[#780000]/10 text-[#780000] font-bold shadow-2xs' : 'text-[#3A3A3C] hover:bg-[#F2F2F7] hover:text-[#780000]' }}"
                       :class="sidebarCollapsed ? 'justify-center p-2.5' : 'gap-3 px-3.5 py-2.5'">
                        <img src="{{ asset('icons/icons8-price-tag-60.png') }}" class="w-5 h-5 shrink-0" alt="DynamicPricing">
                        <span x-show="!sidebarCollapsed" class="truncate">Dynamic Pricing</span>
                    </a>

                    <!-- Payments & Refunds -->
                    <a href="{{ route('admin.payments.index') }}" 
                       title="Payments & Refunds"
                       class="flex items-center rounded-xl font-semibold transition-all {{ request()->routeIs('admin.payments.*') ? 'bg-[#780000]/10 text-[#780000] font-bold shadow-2xs' : 'text-[#3A3A3C] hover:bg-[#F2F2F7] hover:text-[#780000]' }}"
                       :class="sidebarCollapsed ? 'justify-center p-2.5' : 'gap-3 px-3.5 py-2.5'">
                        <img src="{{ asset('icons/icons8-magnetic-card-60.png') }}" class="w-5 h-5 shrink-0" alt="PaymentsAndRefunds">
                        <span x-show="!sidebarCollapsed" class="truncate">Payments & Refunds</span>
                    </a>

                    <!-- Coaches & Schedules -->
                    <a href="{{ route('admin.coaches.index') }}" 
                       title="Coaches & Schedules"
                       class="flex items-center rounded-xl font-semibold transition-all {{ request()->routeIs('admin.coaches.*') ? 'bg-[#780000]/10 text-[#780000] font-bold shadow-2xs' : 'text-[#3A3A3C] hover:bg-[#F2F2F7] hover:text-[#780000]' }}"
                       :class="sidebarCollapsed ? 'justify-center p-2.5' : 'gap-3 px-3.5 py-2.5'">
                        <img src="{{ asset('icons/icons8-coach-60.png') }}" class="w-5 h-5 shrink-0" alt="Coaches">
                        <span x-show="!sidebarCollapsed" class="truncate">Coaches & Schedules</span>
                    </a>

                    <!-- User Management -->
                    <a href="{{ route('admin.users.index') }}" 
                       title="User Management"
                       class="flex items-center rounded-xl font-semibold transition-all {{ request()->routeIs('admin.users.*') ? 'bg-[#780000]/10 text-[#780000] font-bold shadow-2xs' : 'text-[#3A3A3C] hover:bg-[#F2F2F7] hover:text-[#780000]' }}"
                       :class="sidebarCollapsed ? 'justify-center p-2.5' : 'gap-3 px-3.5 py-2.5'">
                        <img src="{{ asset('icons/icons8-user-account-60.png') }}" class="w-5 h-5 shrink-0" alt="User">
                        <span x-show="!sidebarCollapsed" class="truncate">User Management</span>
                    </a>

                    <!-- Audit Logs (Owner Only) -->
                    @if(auth()->user()->isOwner())
                        <a href="{{ route('admin.audit_logs.index') }}" 
                           title="Audit Logs"
                           class="flex items-center rounded-xl font-semibold transition-all {{ request()->routeIs('admin.audit_logs.*') ? 'bg-[#780000]/10 text-[#780000] font-bold shadow-2xs' : 'text-[#3A3A3C] hover:bg-[#F2F2F7] hover:text-[#780000]' }}"
                           :class="sidebarCollapsed ? 'justify-center p-2.5' : 'gap-3 px-3.5 py-2.5'">
                            <img src="{{ asset('icons/icons8-audit-60.png') }}" class="w-5 h-5 shrink-0" alt="Audit">
                            <span x-show="!sidebarCollapsed" class="truncate">Audit Logs</span>
                        </a>
                    @endif
                @else
                    <!-- Coach Portal Navigation -->
                    <!-- 1. Dashboard -->
                    <a href="{{ route('coach.dashboard') }}" 
                       title="Dashboard"
                       class="flex items-center rounded-xl font-semibold transition-all {{ request()->routeIs('coach.dashboard') ? 'bg-[#780000]/10 text-[#780000] font-bold shadow-2xs' : 'text-[#3A3A3C] hover:bg-[#F2F2F7] hover:text-[#780000]' }}"
                       :class="sidebarCollapsed ? 'justify-center p-2.5' : 'gap-3 px-3.5 py-2.5'">
                        <img src="{{ asset('icons/icons8-home-32.png') }}" class="w-5 h-5 shrink-0" alt="Dashboard">
                        <span x-show="!sidebarCollapsed" class="truncate">Dashboard</span>
                    </a>

                    <!-- 2. Availability Calendar -->
                    <a href="{{ route('coach.availability.index') }}" 
                       title="Availability Calendar"
                       class="flex items-center rounded-xl font-semibold transition-all {{ request()->routeIs('coach.availability.*') ? 'bg-[#780000]/10 text-[#780000] font-bold shadow-2xs' : 'text-[#3A3A3C] hover:bg-[#F2F2F7] hover:text-[#780000]' }}"
                       :class="sidebarCollapsed ? 'justify-center p-2.5' : 'gap-3 px-3.5 py-2.5'">
                        <img src="{{ asset('icons/icons8-calendar-60.png') }}" class="w-5 h-5 shrink-0" alt="Availability Calendar">
                        <span x-show="!sidebarCollapsed" class="truncate">Availability Calendar</span>
                    </a>

                    <!-- 3. My Schedule & Students -->
                    <a href="{{ route('coach.schedule.index') }}" 
                       title="My Schedule & History"
                       class="flex items-center rounded-xl font-semibold transition-all {{ request()->routeIs('coach.schedule.*') ? 'bg-[#780000]/10 text-[#780000] font-bold shadow-2xs' : 'text-[#3A3A3C] hover:bg-[#F2F2F7] hover:text-[#780000]' }}"
                       :class="sidebarCollapsed ? 'justify-center p-2.5' : 'gap-3 px-3.5 py-2.5'">
                        <img src="{{ asset('icons/icons8-booking-60.png') }}" class="w-5 h-5 shrink-0" alt="Schedule">
                        <span x-show="!sidebarCollapsed" class="truncate">My Schedule & History</span>
                    </a>

                    <!-- 4. Open Requests Board -->
                    <a href="{{ route('coach.requests.index') }}" 
                       title="Open Slot Requests"
                       class="flex items-center rounded-xl font-semibold transition-all {{ request()->routeIs('coach.requests.*') ? 'bg-[#780000]/10 text-[#780000] font-bold shadow-2xs' : 'text-[#3A3A3C] hover:bg-[#F2F2F7] hover:text-[#780000]' }}"
                       :class="sidebarCollapsed ? 'justify-center p-2.5' : 'gap-3 px-3.5 py-2.5'">
                        <img src="{{ asset('icons/icons8-coach-60.png') }}" class="w-5 h-5 shrink-0" alt="OpenRequests">
                        <span x-show="!sidebarCollapsed" class="truncate">Open Slot Requests</span>
                    </a>
                @endif
            </nav>

            <!-- Bottom: Logo & Camp FreedivePH Brand -->
            <div class="p-3 border-t border-[#E5E5EA] bg-white transition-all overflow-hidden"
                 :class="sidebarCollapsed ? 'flex justify-center' : 'px-4'">
                <a href="{{ auth()->user()->isCoach() ? route('coach.dashboard') : route('admin.dashboard') }}" 
                   class="flex items-center gap-2.5 group"
                   :class="sidebarCollapsed ? 'justify-center' : ''">
                    <img src="{{ asset('images/logo.png') }}" alt="Camp FreedivePH Logo" class="w-8 h-8 rounded-full object-contain bg-white shadow-2xs border border-[#E5E5EA] group-hover:scale-105 transition-transform shrink-0">
                    <div x-show="!sidebarCollapsed" class="min-w-0">
                        <span class="font-extrabold text-xs text-[#1D1D1F] block leading-tight truncate">Camp Freedive<span class="text-[#780000]">PH</span></span>
                    </div>
                </a>
            </div>
        </aside>

        <!-- ========================================================================= -->
        <!-- 2. MOBILE DRAWER -->
        <!-- ========================================================================= -->
        <div x-show="mobileMenuOpen" x-cloak class="md:hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm" @click="mobileMenuOpen = false"></div>

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
                <!-- Drawer Header with User Name -->
                <div class="h-16 flex items-center justify-between px-4 border-b border-[#E5E5EA]">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-[#F8EAEA] text-[#780000] border border-[#780000] flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                            {{ $userInitials }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-bold text-xs text-[#1D1D1F] truncate">{{ auth()->user()->name }}</div>
                            <div class="text-[10px] text-[#6E6E73] truncate">{{ auth()->user()->email }}</div>
                        </div>
                    </div>
                    <button type="button" @click="mobileMenuOpen = false" class="text-base font-bold text-[#8E8E93] hover:text-[#1D1D1F] p-1">✕</button>
                </div>

                <nav class="px-3 pt-3 space-y-1.5 overflow-y-auto">
                    @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-semibold {{ request()->routeIs('admin.dashboard') ? 'bg-[#780000]/10 text-[#780000] font-bold' : 'text-[#3A3A3C] hover:bg-[#F2F2F7]' }}">
                            <img src="{{ asset('icons/icons8-home-32.png') }}" class="w-5 h-5 shrink-0" alt="Dashboard">
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('admin.bookings.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-semibold {{ request()->routeIs('admin.bookings.*') ? 'bg-[#780000]/10 text-[#780000] font-bold' : 'text-[#3A3A3C] hover:bg-[#F2F2F7]' }}">
                            <img src="{{ asset('icons/icons8-booking-60.png') }}" class="w-5 h-5 shrink-0" alt="Bookings">
                            <span>Bookings</span>
                        </a>
                        <a href="{{ route('admin.batches.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-semibold {{ request()->routeIs('admin.batches.*') ? 'bg-[#780000]/10 text-[#780000] font-bold' : 'text-[#3A3A3C] hover:bg-[#F2F2F7]' }}">
                            <img src="{{ asset('icons/icons8-medium-icons-32.png') }}" class="w-5 h-5 shrink-0" alt="Batches">
                            <span>Batches</span>
                        </a>
                        <a href="{{ route('admin.weather.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-semibold {{ request()->routeIs('admin.weather.*') ? 'bg-[#780000]/10 text-[#780000] font-bold' : 'text-[#3A3A3C] hover:bg-[#F2F2F7]' }}">
                            <img src="{{ asset('icons/icons8-warning-shield-32.png') }}" class="w-5 h-5 shrink-0" alt="SafetyMonitoring">
                            <span>Safety Monitoring</span>
                        </a>
                        <a href="{{ route('admin.pricing.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-semibold {{ request()->routeIs('admin.pricing.*') ? 'bg-[#780000]/10 text-[#780000] font-bold' : 'text-[#3A3A3C] hover:bg-[#F2F2F7]' }}">
                            <img src="{{ asset('icons/icons8-price-tag-60.png') }}" class="w-5 h-5 shrink-0" alt="DynamicPricing">
                            <span>Dynamic Pricing</span>
                        </a>
                        <a href="{{ route('admin.payments.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-semibold {{ request()->routeIs('admin.payments.*') ? 'bg-[#780000]/10 text-[#780000] font-bold' : 'text-[#3A3A3C] hover:bg-[#F2F2F7]' }}">
                            <img src="{{ asset('icons/icons8-magnetic-card-60.png') }}" class="w-5 h-5 shrink-0" alt="PaymentsAndRefunds">
                            <span>Payments & Refunds</span>
                        </a>
                        <a href="{{ route('admin.coaches.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-semibold {{ request()->routeIs('admin.coaches.*') ? 'bg-[#780000]/10 text-[#780000] font-bold' : 'text-[#3A3A3C] hover:bg-[#F2F2F7]' }}">
                            <img src="{{ asset('icons/icons8-coach-60.png') }}" class="w-5 h-5 shrink-0" alt="Coaches">
                            <span>Coaches & Schedules</span>
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-semibold {{ request()->routeIs('admin.users.*') ? 'bg-[#780000]/10 text-[#780000] font-bold' : 'text-[#3A3A3C] hover:bg-[#F2F2F7]' }}">
                            <img src="{{ asset('icons/icons8-user-account-60.png') }}" class="w-5 h-5 shrink-0" alt="User">
                            <span>User Management</span>
                        </a>
                        @if(auth()->user()->isOwner())
                            <a href="{{ route('admin.audit_logs.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-semibold {{ request()->routeIs('admin.audit_logs.*') ? 'bg-[#780000]/10 text-[#780000] font-bold' : 'text-[#3A3A3C] hover:bg-[#F2F2F7]' }}">
                                <img src="{{ asset('icons/icons8-audit-60.png') }}" class="w-5 h-5 shrink-0" alt="Audit">
                                <span>Audit Logs</span>
                            </a>
                        @endif
                    @else
                        <!-- Coach Portal Mobile Navigation -->
                        <a href="{{ route('coach.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-semibold {{ request()->routeIs('coach.dashboard') ? 'bg-[#780000]/10 text-[#780000] font-bold' : 'text-[#3A3A3C] hover:bg-[#F2F2F7]' }}">
                            <img src="{{ asset('icons/icons8-home-32.png') }}" class="w-5 h-5 shrink-0" alt="Dashboard">
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('coach.availability.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-semibold {{ request()->routeIs('coach.availability.*') ? 'bg-[#780000]/10 text-[#780000] font-bold' : 'text-[#3A3A3C] hover:bg-[#F2F2F7]' }}">
                            <img src="{{ asset('icons/icons8-calendar-60.png') }}" class="w-5 h-5 shrink-0" alt="Availability Calendar">
                            <span>Availability Calendar</span>
                        </a>
                        <a href="{{ route('coach.schedule.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-semibold {{ request()->routeIs('coach.schedule.*') ? 'bg-[#780000]/10 text-[#780000] font-bold' : 'text-[#3A3A3C] hover:bg-[#F2F2F7]' }}">
                            <img src="{{ asset('icons/icons8-booking-60.png') }}" class="w-5 h-5 shrink-0" alt="Schedule">
                            <span>My Schedule & History</span>
                        </a>
                        <a href="{{ route('coach.requests.index') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl font-semibold {{ request()->routeIs('coach.requests.*') ? 'bg-[#780000]/10 text-[#780000] font-bold' : 'text-[#3A3A3C] hover:bg-[#F2F2F7]' }}">
                            <img src="{{ asset('icons/icons8-coach-60.png') }}" class="w-5 h-5 shrink-0" alt="OpenRequests">
                            <span>Open Slot Requests</span>
                        </a>
                    @endif
                </nav>
            </div>

            <!-- Bottom: Logo & Sign Out -->
            <div class="p-3 border-t border-[#E5E5EA] bg-white space-y-2">
                <div class="flex items-center gap-2.5 px-1 py-1">
                    <img src="{{ asset('images/logo.png') }}" alt="Camp FreedivePH" class="w-7 h-7 rounded-full object-contain bg-white border border-[#E5E5EA]">
                    <span class="font-extrabold text-xs text-[#1D1D1F]">Camp Freedive<span class="text-[#780000]">PH</span></span>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- 3. MAIN CONTENT CONTAINER WITH TOP NAV HEADER -->
        <!-- ========================================================================= -->
        <main class="flex-1 flex flex-col min-h-screen transition-all duration-300"
              :class="sidebarCollapsed ? 'md:pl-20' : 'md:pl-64'">
            
            <!-- Sticky Top Header (Sidebar Toggle + Breadcrumbs + Avatar Dropdown) -->
            <header class="sticky top-0 z-20 bg-white border-b border-[#E5E5EA] px-4 sm:px-6 h-14 flex items-center justify-between shadow-2xs">
                
                <!-- Left: Sidebar Toggle Button + Vertical Divider + Breadcrumb Title -->
                <div class="flex items-center gap-3">
                    <!-- Desktop Sidebar Toggle Button -->
                    <button type="button" 
                            @click="toggleSidebar()" 
                            class="hidden md:flex items-center justify-center w-8 h-8 rounded-md hover:bg-[#F2F2F7] transition-all cursor-pointer"
                            :title="sidebarCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'">
                        <img src="{{ asset('icons/icons8-sidebar-60.png') }}" class="w-5 h-5 shrink-0 opacity-80 hover:opacity-100 transition-opacity" alt="Toggle Sidebar">
                    </button>

                    <!-- Mobile Menu Trigger Button -->
                    <button type="button" 
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="md:hidden flex items-center justify-center w-8 h-8 rounded-md hover:bg-[#F2F2F7] cursor-pointer">
                        <img src="{{ asset('icons/icons8-sidebar-60.png') }}" class="w-5 h-5 shrink-0 opacity-80 hover:opacity-100 transition-opacity" alt="Open Menu">
                    </button>

                    <!-- Vertical Divider -->
                    <div class="h-4 w-px bg-[#D1D1D6]"></div>

                    <!-- Breadcrumb Trail / Page Title -->
                    <div class="flex items-center gap-1.5 text-xs sm:text-sm">
                        @if(View::hasSection('breadcrumb'))
                            @yield('breadcrumb')
                        @else
                            <span class="font-bold text-[#1D1D1F]">{{ $pageBreadcrumbTitle }}</span>
                        @endif
                    </div>
                </div>

                <!-- Right: User Avatar + Interactive Profile Popover Dropdown -->
                <div class="relative" x-data="{ profileMenuOpen: false }">
                    <button type="button" 
                            @click="profileMenuOpen = !profileMenuOpen" 
                            @click.outside="profileMenuOpen = false"
                            class="flex items-center gap-2 cursor-pointer focus:outline-none group">
                        
                        <!-- Circular Avatar with User Initials -->
                        <div class="w-8 h-8 rounded-full bg-[#F8EAEA] text-[#780000] border border-[#780000] flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs group-hover:ring-2 group-hover:ring-[#780000]/30 transition-all">
                            {{ $userInitials }}
                        </div>
                    </button>

                    <!-- Dropdown Popover Menu (First & Last Name, Email, Role, Logout) -->
                    <div x-show="profileMenuOpen" 
                         x-cloak
                         x-transition:enter="transition ease-out duration-150 transform"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100 transform"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                         class="absolute right-0 mt-2 w-64 bg-white rounded-xl border border-[#E5E5EA] shadow-xl p-3 z-50 space-y-3">
                        
                        <!-- User Info Header (First & Last Name + Email) -->
                        <div class="flex items-center gap-3 pb-3 border-b border-[#E5E5EA]">
                            <div class="w-10 h-10 rounded-full bg-[#F8EAEA] text-[#780000] border border-[#780000] flex items-center justify-center font-bold text-sm shrink-0 shadow-2xs">
                                {{ $userInitials }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="font-bold text-xs text-[#1D1D1F] truncate">{{ auth()->user()->name }}</div>
                                <div class="text-[11px] text-[#6E6E73] truncate mt-0.5">{{ auth()->user()->email }}</div>
                                <span class="inline-block mt-1 px-2 py-0.2 rounded-md text-[10px] font-bold bg-[#F8EAEA] text-[#780000] border border-[#F1D5D5]">
                                    {{ auth()->user()->role_label ?? ucfirst(auth()->user()->role) }}
                                </span>
                            </div>
                        </div>

                        <!-- Logout Button -->
                        <div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-2.5 py-2 rounded-lg text-[#FF3B30] hover:bg-rose-50 font-bold text-xs transition-colors cursor-pointer">
                                    <img src="{{ asset('icons/icons8-logout-60.png') }}" class="w-4 h-4 shrink-0" alt="Sign Out">
                                    <span>Log Out</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </header>
            
            <!-- Global Flash Messages -->
            <div class="px-4 sm:px-8 pt-4">
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

        </main>

    </div>

    @stack('scripts')
</body>
</html>
