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
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="d-flex flex-column h-100 bg-light text-dark antialiased fs-6 overflow-x-hidden" style="min-width: 320px; font-family: 'Instrument Sans', sans-serif;">

    <!-- Navigation Bar -->
    <header class="sticky-top z-3 bg-white bg-opacity-75 backdrop-blur border-bottom border-secondary-subtle shadow-sm">
        <div class="container-xxl px-3 px-sm-4 px-lg-5 py-3 d-flex align-items-center justify-content-between gap-2">
            <!-- Brand Logo -->
            <a href="{{ route('landing') }}" class="d-flex align-items-center gap-2 gap-sm-3 text-decoration-none flex-shrink-0 group">
                <img src="{{ asset('images/logo.png') }}" alt="Camp FreedivePH Logo" class="rounded-circle object-fit-contain bg-white shadow-sm border border-secondary-subtle" style="width: 40px; height: 40px; transition: transform 0.2s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <div class="d-block">
                    <span class="fw-bolder fs-6 fs-sm-5 tracking-tight text-dark d-block lh-1">Camp Freedive<span style="color: #780000;">PH</span></span>
                    <span class="d-block mt-1 text-secondary fw-medium" style="font-size: 0.75rem; letter-spacing: 0.05em;">Mabini, Batangas</span>
                </div>
            </a>

            <!-- Actions (Manage Booking & Primary CTA) -->
            <div class="d-flex align-items-center gap-3 gap-sm-4">
                <a href="{{ route('manage.index') }}" class="text-dark fw-semibold text-decoration-none text-nowrap transition-colors" style="font-size: 0.8125rem;" onmouseover="this.style.color='#780000'" onmouseout="this.style.color='#1D1D1F'">
                    Manage Booking
                </a>

                <a href="{{ route('booking.create') }}" class="btn btn-dark px-3 px-sm-4 py-2 fw-bold shadow-sm text-nowrap" style="font-size: 0.8125rem; background-color: #780000; border-color: #780000;">
                    Book Now
                </a>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    @if(session('success'))
    <div class="border-bottom py-3 px-4 text-center fw-medium d-flex align-items-center justify-content-center gap-2" style="background-color: #ECFDF5; border-color: #A7F3D0 !important; color: #065F46; font-size: 0.875rem;">
        <svg class="flex-shrink-0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#34C759" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="border-bottom py-3 px-4 text-center fw-medium d-flex align-items-center justify-content-center gap-2" style="background-color: #FEF2F2; border-color: #FECACA !important; color: #991B1B; font-size: 0.875rem;">
        <svg class="flex-shrink-0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FF3B3C" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Main Content -->
    <main class="flex-grow-1 w-100 mw-100 overflow-hidden">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="text-white pt-5 pb-4 mt-auto" style="background-color: #1D1D1F; border-top: 1px solid #3A3A3C; font-size: 0.875rem;">
        <div class="container-xxl px-4 px-sm-5">
            <div class="row g-4 pb-5 border-bottom" style="border-color: #3A3A3C !important;">

                <!-- Left Column: Brand Logo, Name, Gmail & Clickable Google Maps Address -->
                <div class="col-12 col-md-6 d-flex flex-column gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ asset('images/logo.png') }}" alt="Camp FreedivePH Logo" class="rounded-circle object-fit-contain bg-white shadow-sm border border-light border-opacity-25" style="width: 44px; height: 44px;">
                        <div>
                            <span class="fw-bolder fs-5 text-white d-block lh-1">Camp Freedive<span style="color: #00C3D0;">PH</span></span>
                            <span class="d-block mt-1 text-secondary" style="font-size: 0.75rem;">Mabini, Batangas</span>
                        </div>
                    </div>

                    <!-- Gmail Contact -->
                    <div class="d-flex align-items-center gap-2 text-secondary">
                        <a href="mailto:campfreediveph@gmail.com" class="text-light text-decoration-none fw-medium transition-colors" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                            campfreediveph@gmail.com
                        </a>
                    </div>

                    <!-- Number Contact -->
                    <div class="d-flex align-items-center gap-2 text-secondary">
                        <a href="tel:+639154069330" class="text-light text-decoration-none fw-medium transition-colors" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                            +63 000 000 0000
                        </a>
                    </div>

                    <!-- Clickable Address to Google Maps -->
                    <div class="pt-1">
                        <div class="text-uppercase text-secondary fw-semibold mb-1" style="font-size: 0.75rem; letter-spacing: 0.05em;">Camp Location & Resort Venue:</div>
                        <a href="https://www.google.com/maps/search/?api=1&query=The+Shack+Hideaway+by+Mayumi+Resort+Barangay+Bagalangit+Mabini+Batangas"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="d-flex align-items-start gap-2 text-light text-decoration-none lh-base group" style="font-size: 0.875rem;">
                            <span class="text-secondary" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                                The Shack Hideaway by Mayumi Resort, located along the National Road in Barangay Bagalangit, Mabini, Batangas, Philippines.
                            </span>
                        </a>
                    </div>
                </div>

                <!-- Right Column: Partnership with The Shack Hideaway & Social Logos -->
                <div class="col-12 col-md-6 d-flex flex-column justify-content-between text-md-end align-items-md-end gap-4">
                    <div>
                        <div class="text-uppercase text-secondary fw-semibold mb-1" style="font-size: 0.75rem; letter-spacing: 0.05em;">Official Resort Partner</div>
                        <div class="fs-6 fs-sm-5 fw-bold text-white">
                            In Partnership with <span style="color: #00C3D0;">The Shack Hideaway</span>
                        </div>
                        <p class="text-secondary mt-1 ms-md-auto mb-0" style="font-size: 0.75rem; max-width: 350px;">
                            Enjoy direct ocean access, sunset views, and dedicated dive training facilities in Anilao, Mabini.
                        </p>
                    </div>

                    <!-- Social Logos (Facebook & Instagram) -->
                    <div class="d-flex flex-column gap-2 w-100 align-items-md-end">
                        <div class="text-uppercase text-secondary fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.05em;">Follow Our Adventures</div>
                        <div class="d-flex align-items-center gap-2 justify-content-md-end">
                            <!-- Facebook -->
                            <a href="https://www.facebook.com/Campfreediveph/"
                                target="_blank"
                                rel="noopener noreferrer"
                                title="Camp FreedivePH on Facebook"
                                class="rounded-circle text-white d-flex align-items-center justify-content-center shadow-sm border border-light border-opacity-10"
                                style="width: 40px; height: 40px; background-color: rgba(255,255,255,0.1); transition: all 0.2s ease;"
                                onmouseover="this.style.backgroundColor='#1877F2'; this.style.transform='scale(1.1)';"
                                onmouseout="this.style.backgroundColor='rgba(255,255,255,0.1)'; this.style.transform='scale(1)';">
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                </svg>
                            </a>

                            <!-- Instagram -->
                            <a href="https://www.instagram.com/campfreediveph/"
                                target="_blank"
                                rel="noopener noreferrer"
                                title="Camp FreedivePH on Instagram"
                                class="rounded-circle text-white d-flex align-items-center justify-content-center shadow-sm border border-light border-opacity-10"
                                style="width: 40px; height: 40px; background-color: rgba(255,255,255,0.1); transition: all 0.2s ease;"
                                onmouseover="this.style.background='linear-gradient(135deg, #F58529, #DD2A7B, #8134AF)'; this.style.transform='scale(1.1)';"
                                onmouseout="this.style.backgroundColor='rgba(255,255,255,0.1)'; this.style.background='rgba(255,255,255,0.1)'; this.style.transform='scale(1)';">
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.13-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Bottom Copyright -->
            <div class="pt-4 d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 text-secondary" style="font-size: 0.8125rem;">
                <p class="mb-0">&copy; 2026 Camp FreedivePH. All rights reserved.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>

</html>