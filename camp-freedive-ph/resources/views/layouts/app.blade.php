<!DOCTYPE html>
<html lang="en" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
    <title>@yield('title', 'Camp FreedivePH | Freediving Camp in Mabini, Batangas')</title>
    <meta name="description" content="@yield('meta_description', 'Learn freediving in Mabini, Batangas. Discovery classes for non-swimmers, fundives, and refinement practice dives.')">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">

    <!-- Favicon / Logo -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-full flex flex-col bg-[#FAFAFC] text-[#1D1D1F] antialiased text-sm overflow-x-hidden min-w-[320px]">

    <!-- Navigation Bar -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-[#E5E5EA]">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-2">
            <!-- Brand Logo -->
            <a href="{{ route('landing') }}" class="flex items-center gap-2.5 sm:gap-3 group shrink-0">
                <img src="{{ asset('images/logo.png') }}" alt="Camp FreedivePH Logo" class="w-10 h-10 sm:w-12 sm:h-12 rounded-full object-contain bg-white shadow-sm border border-[#E5E5EA] group-hover:scale-105 transition-transform">
                <div class="block">
                    <span class="font-extrabold text-base sm:text-xl tracking-tight text-[#1D1D1F] block leading-none">Camp Freedive<span class="text-[#780000]">PH</span></span>
                    <span class="text-xs sm:text-sm text-[#6E6E73] font-medium tracking-wider block mt-0.5">Mabini, Batangas</span>
                </div>
            </a>

            <!-- Actions (Text Link for Manage Booking, Staff Portal & Primary CTA) -->
            <div class="flex items-center gap-3 sm:gap-5">
                <a href="{{ route('manage.index') }}" class="text-xs sm:text-sm font-semibold text-[#1D1D1F] hover:text-[#780000] transition-colors whitespace-nowrap">
                    Manage Booking
                </a>

                <a href="{{ route('booking.create') }}" class="btn-primary px-4 sm:px-6 py-2.5 text-xs sm:text-sm font-bold shadow-sm whitespace-nowrap">
                    Book Now
                </a>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="bg-[#ECFDF5] border-b border-[#A7F3D0] text-[#065F46] py-3.5 px-4 text-sm text-center font-medium flex items-center justify-center gap-2">
            <svg class="w-5 h-5 text-[#34C759] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-[#FEF2F2] border-b border-[#FECACA] text-[#991B1B] py-3.5 px-4 text-sm text-center font-medium flex items-center justify-center gap-2">
            <svg class="w-5 h-5 text-[#FF3B3C] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Main Content -->
    <main class="flex-grow w-full max-w-full overflow-hidden">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-[#1D1D1F] text-white pt-12 pb-10 border-t border-[#3A3A3C] mt-20 text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-16 pb-10 border-b border-[#3A3A3C]">
                
                <!-- Left Column: Brand Logo, Name, Gmail & Clickable Google Maps Address -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/logo.png') }}" alt="Camp FreedivePH Logo" class="w-11 h-11 rounded-full object-contain bg-white shadow-sm border border-white/20">
                        <div>
                            <span class="font-extrabold text-xl text-white block leading-none">Camp Freedive<span class="text-[#00C3D0]">PH</span></span>
                            <span class="text-xs text-[#8E8E93] block mt-0.5">Mabini, Batangas</span>
                        </div>
                    </div>

                    <!-- Gmail Contact -->
                    <div class="flex items-center gap-2 text-sm text-[#D1D1D6]">
                        <a href="mailto:campfreediveph@gmail.com" class="hover:text-white hover:underline transition-colors font-medium">
                            campfreediveph@gmail.com
                        </a>
                    </div>

                    <!-- Clickable Address to Google Maps -->
                    <div class="pt-1">
                        <div class="text-xs uppercase tracking-wider text-[#8E8E93] font-semibold mb-1">Camp Location & Resort Venue:</div>
                        <a href="https://www.google.com/maps/search/?api=1&query=The+Shack+Hideaway+by+Mayumi+Resort+Barangay+Bagalangit+Mabini+Batangas" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="group flex items-start gap-2.5 text-sm text-[#D1D1D6] hover:text-white transition-colors leading-relaxed">
                            <span class="group-hover:underline underline-offset-4">
                                The Shack Hideaway by Mayumi Resort, located along the National Road in Barangay Bagalangit, Mabini, Batangas, Philippines.
                            </span>
                        </a>
                    </div>
                </div>

                <!-- Right Column: Partnership with The Shack Hideaway & Social Logos -->
                <div class="space-y-5 md:text-right flex flex-col md:items-end justify-between">
                    <div>
                        <div class="text-xs uppercase tracking-wider text-[#8E8E93] font-semibold mb-1">Official Resort Partner</div>
                        <div class="text-base sm:text-lg font-bold text-white">
                            In Partnership with <span class="text-[#00C3D0]">The Shack Hideaway</span>
                        </div>
                        <p class="text-xs text-[#8E8E93] mt-1 max-w-sm md:ml-auto">
                            Enjoy direct ocean access, sunset views, and dedicated dive training facilities in Anilao, Mabini.
                        </p>
                    </div>

                    <!-- Social Logos (Facebook & Instagram) -->
                    <div class="space-y-2">
                        <div class="text-xs uppercase tracking-wider text-[#8E8E93] font-semibold">Follow Our Adventures</div>
                        <div class="flex items-center gap-3 md:justify-end">
                            <!-- Facebook -->
                            <a href="https://www.facebook.com/Campfreediveph/" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               title="Camp FreedivePH on Facebook"
                               class="w-10 h-10 rounded-full bg-white/10 hover:bg-[#1877F2] text-white flex items-center justify-center transition-all hover:scale-110 shadow-sm border border-white/10">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>

                            <!-- Instagram -->
                            <a href="https://www.instagram.com/campfreediveph/" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               title="Camp FreedivePH on Instagram"
                               class="w-10 h-10 rounded-full bg-white/10 hover:bg-gradient-to-tr hover:from-[#F58529] hover:via-[#DD2A7B] hover:to-[#8134AF] text-white flex items-center justify-center transition-all hover:scale-110 shadow-sm border border-white/10">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.13-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Bottom Copyright -->
            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs sm:text-sm text-[#8E8E93]">
                <p>&copy; 2026 Camp FreedivePH. All rights reserved.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
