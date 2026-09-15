@extends('layouts.app')

@section('title', 'Privacy Policy | Camp FreedivePH')
@section('meta_description', 'Learn how Camp FreedivePH protects and manages your personal data in compliance with the Philippine Data Privacy Act of 2012 (RA 10173).')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-12 sm:py-20 text-sm text-[#1D1D1F] leading-relaxed relative"
     x-data="{
         showBackToTop: false,
         scrollToSection(id) {
             const target = document.getElementById(id);
             if (target) {
                 target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                 try { history.pushState(null, '', '#' + id); } catch(e) {}
             }
         },
         scrollToTop() {
             window.scrollTo({ top: 0, behavior: 'smooth' });
             try { history.pushState(null, '', window.location.pathname); } catch(e) {}
         }
     }"
     @scroll.window="showBackToTop = (window.pageYOffset > 400)">

    <!-- Legal Header -->
    <header class="space-y-4 pb-8 border-b border-[#E5E5EA]">
        <div class="flex items-center gap-2 text-xs font-semibold text-[#636366] uppercase tracking-wider">
            <a href="{{ route('landing') }}" class="min-h-[44px] inline-flex items-center hover:text-[#1D1D1F] transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000] rounded-md">Home</a>
            <span>/</span>
            <span>Legal</span>
        </div>

        <h1 class="text-3xl sm:text-5xl font-extrabold text-[#1D1D1F] tracking-tight">
            Camp FreedivePH Privacy Policy
        </h1>

        <p class="text-xs sm:text-sm text-[#636366]">
            Effective as of September 13, 2026
        </p>

        <!-- Document Switcher -->
        <nav class="pt-2" aria-label="Legal documents">
            <div class="inline-flex p-1 bg-[#F2F2F7] rounded-xl text-sm font-semibold">
                <a href="{{ route('legal.terms') }}" 
                   class="min-h-[44px] px-4 py-2 inline-flex items-center text-[#636366] hover:text-[#1D1D1F] transition-colors rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]">
                    Terms &amp; Conditions
                </a>
                <span class="px-4 py-2 bg-white text-[#1D1D1F] rounded-lg shadow-2xs">Privacy Policy</span>
            </div>
        </nav>
    </header>

    <!-- Table of Contents -->
    <nav class="my-8 p-5 sm:p-6 bg-[#F2F2F7] rounded-2xl space-y-3" 
         aria-label="Table of Contents">
        <h2 class="text-xs font-bold uppercase tracking-wider text-[#636366] px-1">Table of Contents</h2>
        <ol class="space-y-1 text-sm text-[#1D1D1F]">
            <li>
                <a href="#introduction" @click.prevent="scrollToSection('introduction')" 
                   class="min-h-[44px] px-3.5 py-2.5 rounded-xl hover:bg-white/80 transition-all font-medium flex items-center justify-between group focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]">
                    <span>1. Introduction</span>
                    <img src="{{ asset('icons/icons8-arrow-right-50.png') }}" class="w-4 h-4 object-contain opacity-60 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all shrink-0" alt="" aria-hidden="true">
                </a>
            </li>
            <li>
                <a href="#personal-data-collected" @click.prevent="scrollToSection('personal-data-collected')" 
                   class="min-h-[44px] px-3.5 py-2.5 rounded-xl hover:bg-white/80 transition-all font-medium flex items-center justify-between group focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]">
                    <span>2. Personal Data We Collect</span>
                    <img src="{{ asset('icons/icons8-arrow-right-50.png') }}" class="w-4 h-4 object-contain opacity-60 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all shrink-0" alt="" aria-hidden="true">
                </a>
            </li>
            <li>
                <a href="#how-we-use-data" @click.prevent="scrollToSection('how-we-use-data')" 
                   class="min-h-[44px] px-3.5 py-2.5 rounded-xl hover:bg-white/80 transition-all font-medium flex items-center justify-between group focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]">
                    <span>3. How We Use Your Personal Data</span>
                    <img src="{{ asset('icons/icons8-arrow-right-50.png') }}" class="w-4 h-4 object-contain opacity-60 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all shrink-0" alt="" aria-hidden="true">
                </a>
            </li>
            <li>
                <a href="#payment-security" @click.prevent="scrollToSection('payment-security')" 
                   class="min-h-[44px] px-3.5 py-2.5 rounded-xl hover:bg-white/80 transition-all font-medium flex items-center justify-between group focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]">
                    <span>4. Payment Security and PayMongo Integration</span>
                    <img src="{{ asset('icons/icons8-arrow-right-50.png') }}" class="w-4 h-4 object-contain opacity-60 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all shrink-0" alt="" aria-hidden="true">
                </a>
            </li>
            <li>
                <a href="#third-party-sharing" @click.prevent="scrollToSection('third-party-sharing')" 
                   class="min-h-[44px] px-3.5 py-2.5 rounded-xl hover:bg-white/80 transition-all font-medium flex items-center justify-between group focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]">
                    <span>5. Sharing with Resort Partners and Authorities</span>
                    <img src="{{ asset('icons/icons8-arrow-right-50.png') }}" class="w-4 h-4 object-contain opacity-60 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all shrink-0" alt="" aria-hidden="true">
                </a>
            </li>
            <li>
                <a href="#cookies-storage" @click.prevent="scrollToSection('cookies-storage')" 
                   class="min-h-[44px] px-3.5 py-2.5 rounded-xl hover:bg-white/80 transition-all font-medium flex items-center justify-between group focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]">
                    <span>6. Cookies and Local Storage Technologies</span>
                    <img src="{{ asset('icons/icons8-arrow-right-50.png') }}" class="w-4 h-4 object-contain opacity-60 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all shrink-0" alt="" aria-hidden="true">
                </a>
            </li>
            <li>
                <a href="#data-retention-security" @click.prevent="scrollToSection('data-retention-security')" 
                   class="min-h-[44px] px-3.5 py-2.5 rounded-xl hover:bg-white/80 transition-all font-medium flex items-center justify-between group focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]">
                    <span>7. Data Retention and Security Safeguards</span>
                    <img src="{{ asset('icons/icons8-arrow-right-50.png') }}" class="w-4 h-4 object-contain opacity-60 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all shrink-0" alt="" aria-hidden="true">
                </a>
            </li>
            <li>
                <a href="#data-subject-rights" @click.prevent="scrollToSection('data-subject-rights')" 
                   class="min-h-[44px] px-3.5 py-2.5 rounded-xl hover:bg-white/80 transition-all font-medium flex items-center justify-between group focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]">
                    <span>8. Your Data Privacy Rights (RA 10173)</span>
                    <img src="{{ asset('icons/icons8-arrow-right-50.png') }}" class="w-4 h-4 object-contain opacity-60 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all shrink-0" alt="" aria-hidden="true">
                </a>
            </li>
            <li>
                <a href="#contact" @click.prevent="scrollToSection('contact')" 
                   class="min-h-[44px] px-3.5 py-2.5 rounded-xl hover:bg-white/80 transition-all font-medium flex items-center justify-between group focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]">
                    <span>9. Contact Us</span>
                    <img src="{{ asset('icons/icons8-arrow-right-50.png') }}" class="w-4 h-4 object-contain opacity-60 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all shrink-0" alt="" aria-hidden="true">
                </a>
            </li>
        </ol>
    </nav>

    <!-- Document Body -->
    <div class="space-y-10 text-base leading-relaxed text-[#3A3A3C]">

        <!-- 1. Introduction -->
        <section id="introduction" class="scroll-mt-24 sm:scroll-mt-28 space-y-3 pt-2">
            <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] tracking-tight">
                1. Introduction
            </h2>
            <p>
                Camp FreedivePH ("we", "us", or "our") is dedicated to protecting your privacy. This Privacy Policy explains how we collect, process, store, and safeguard your personal information when you use our website, book dive camps, or interact with our staff.
            </p>
            <p>
                We adhere strictly to <strong>Republic Act No. 10173</strong>, otherwise known as the <em>Data Privacy Act of 2012 (DPA)</em>, and all relevant guidelines set by the <strong>National Privacy Commission (NPC)</strong> of the Philippines.
            </p>
        </section>

        <!-- 2. Personal Data We Collect -->
        <section id="personal-data-collected" class="scroll-mt-24 sm:scroll-mt-28 space-y-3 pt-6 border-t border-[#E5E5EA]">
            <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] tracking-tight">
                2. Personal Data We Collect
            </h2>
            <p>
                We collect personal information that you provide directly to us when making a reservation or managing your booking:
            </p>
            <ul class="list-disc list-outside pl-5 space-y-2 text-[#3A3A3C]">
                <li><strong>Identity & Contact Details:</strong> Full name, email address, mobile contact number, emergency contact details, and pickup point selection.</li>
                <li><strong>Aquatic Safety Profile:</strong> Swimming ability (non-swimmer vs swimmer), medical disclosures necessary for safe freediving, and diving certification details.</li>
                <li><strong>Booking & Payment Information:</strong> Booking reference numbers, PayMongo transaction references, payment amount, and method used. <em>(Note: We never receive or store your credit/debit card numbers or bank PINs.)</em></li>
            </ul>
        </section>

        <!-- 3. How We Use Data -->
        <section id="how-we-use-data" class="scroll-mt-24 sm:scroll-mt-28 space-y-3 pt-6 border-t border-[#E5E5EA]">
            <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] tracking-tight">
                3. How We Use Your Personal Data
            </h2>
            <p>
                We use your personal information solely for legitimate operational and dive safety purposes:
            </p>
            <ul class="list-disc list-outside pl-5 space-y-2 text-[#3A3A3C]">
                <li>To confirm and manage your 2D1N dive camp reservations and send automated confirmation emails and 4-digit security PINs.</li>
                <li>To assign certified safety coaches maintaining the student-to-coach ratio suited to your comfort level.</li>
                <li>To reserve shared AC accommodations with our partner resort, <em>The Shack Hideaway</em>.</li>
                <li>To notify you promptly of weather alerts, tropical cyclone wind signals, or schedule adjustments.</li>
            </ul>
        </section>

        <!-- 4. Payment Security -->
        <section id="payment-security" class="scroll-mt-24 sm:scroll-mt-28 space-y-3 pt-6 border-t border-[#E5E5EA]">
            <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] tracking-tight">
                4. Payment Security and PayMongo Integration
            </h2>
            <p>
                Online downpayments are processed securely by <strong>PayMongo</strong>, a BSP-regulated and PCI-DSS Level 1 certified payment gateway. All communications during checkout are protected by 256-bit SSL encryption. We do not store raw card credentials or payment passwords on our servers.
            </p>
        </section>

        <!-- 5. Third-Party Sharing -->
        <section id="third-party-sharing" class="scroll-mt-24 sm:scroll-mt-28 space-y-3 pt-6 border-t border-[#E5E5EA]">
            <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] tracking-tight">
                5. Sharing with Resort Partners and Authorities
            </h2>
            <p>
                We only share personal data with third parties when necessary to execute your dive camp:
            </p>
            <ul class="list-disc list-outside pl-5 space-y-2 text-[#3A3A3C]">
                <li><strong>Resort Partner:</strong> Guest names are provided to <em>The Shack Hideaway</em> for room assignment and check-in.</li>
                <li><strong>Local Municipal Compliance:</strong> Participant details may be registered with the Mabini LGU Tourism Office and Philippine Coast Guard for maritime safety manifests.</li>
                <li><strong>Email Delivery:</strong> Transactional emails are dispatched via secure cloud mail gateways (Resend).</li>
            </ul>
            <p>
                We do not sell, rent, or trade your personal information to third-party advertisers or marketing brokers.
            </p>
        </section>

        <!-- 6. Cookies and Storage -->
        <section id="cookies-storage" class="scroll-mt-24 sm:scroll-mt-28 space-y-3 pt-6 border-t border-[#E5E5EA]">
            <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] tracking-tight">
                6. Cookies and Local Storage Technologies
            </h2>
            <p>
                We use strictly essential cookies and browser storage:
            </p>
            <ul class="list-disc list-outside pl-5 space-y-1.5 text-[#3A3A3C]">
                <li><strong>Session & Security Cookies:</strong> <code>laravel_session</code> and <code>XSRF-TOKEN</code> to keep your booking session secure and prevent cross-site request forgery.</li>
                <li><strong>Local Storage:</strong> Storing your cookie banner acknowledgment and reservation draft states.</li>
            </ul>
        </section>

        <!-- 7. Data Retention -->
        <section id="data-retention-security" class="scroll-mt-24 sm:scroll-mt-28 space-y-3 pt-6 border-t border-[#E5E5EA]">
            <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] tracking-tight">
                7. Data Retention and Security Safeguards
            </h2>
            <p>
                We maintain appropriate administrative, technical, and physical security measures to protect your personal information against unauthorized access, loss, or alteration. Data is retained only as long as necessary to complete your booking and meet statutory accounting and legal requirements.
            </p>
        </section>

        <!-- 8. Data Subject Rights -->
        <section id="data-subject-rights" class="scroll-mt-24 sm:scroll-mt-28 space-y-3 pt-6 border-t border-[#E5E5EA]">
            <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] tracking-tight">
                8. Your Data Privacy Rights (RA 10173)
            </h2>
            <p>
                Under the Data Privacy Act of 2012, you have the right to:
            </p>
            <ul class="list-disc list-outside pl-5 space-y-1.5 text-[#3A3A3C]">
                <li>Be informed of the processing of your personal information.</li>
                <li>Access your personal data held in our booking records.</li>
                <li>Rectify inaccurate or outdated information.</li>
                <li>Request erasure or blocking of your data when no longer necessary.</li>
                <li>Lodge a complaint with the National Privacy Commission (NPC) at <em>privacy.gov.ph</em>.</li>
            </ul>
        </section>

        <!-- 9. Contact Us -->
        <section id="contact" class="scroll-mt-24 sm:scroll-mt-28 space-y-3 pt-6 border-t border-[#E5E5EA]">
            <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] tracking-tight">
                9. Contact Us
            </h2>
            <p>
                For questions, data access requests, or privacy concerns, you may contact our team through any of the following channels:
            </p>
            <div class="rounded-xl space-y-2.5 text-[#3A3A3C]">
                <div><strong class="text-[#1D1D1F]">Camp FreedivePH</strong>, Barangay Bagalangit, Mabini, Batangas, Philippines</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1 text-sm">
                    <div>
                        <span class="text-[#6E6E73] font-medium">Facebook:</span>
                        <a href="https://www.facebook.com/Campfreediveph/" target="_blank" rel="noopener noreferrer" class="text-[#780000] font-bold underline hover:text-[#5E0000] inline-flex items-center gap-1.5">
                            <span>@Campfreediveph</span>
                            <img src="{{ asset('icons/icons8-linking-60.png') }}" class="w-4.5 h-4.5 object-contain shrink-0" alt="" aria-hidden="true">
                        </a>
                    </div>
                    <div>
                        <span class="text-[#6E6E73] font-medium">Instagram:</span>
                        <a href="https://www.instagram.com/campfreediveph/" target="_blank" rel="noopener noreferrer" class="text-[#780000] font-bold underline hover:text-[#5E0000] inline-flex items-center gap-1.5">
                            <span>@campfreediveph</span>
                            <img src="{{ asset('icons/icons8-linking-60.png') }}" class="w-4.5 h-4.5 object-contain shrink-0" alt="" aria-hidden="true">
                        </a>
                    </div>
                    <div>
                        <span class="text-[#6E6E73] font-medium">Email:</span>
                        <a href="mailto:campfreediveph@gmail.com" class="text-[#780000] font-bold underline hover:text-[#5E0000]">campfreediveph@gmail.com</a>
                    </div>
                    <div>
                        <span class="text-[#6E6E73] font-medium">Phone:</span>
                        <a href="tel:+639278879894" class="text-[#780000] font-bold hover:underline">+63 927 887 9894</a>
                    </div>
                </div>
            </div>
        </section>

    </div>

    <!-- Floating Back to Top Button -->
    <button type="button"
            x-show="showBackToTop"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-3 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-3 scale-95"
            @click="scrollToTop()"
            aria-label="Back to top"
            class="fixed bottom-6 right-6 z-40 p-2.5 sm:px-3.5 sm:py-2 rounded-2xl bg-white/90 hover:bg-white text-[#1D1D1F] shadow-lg backdrop-blur-md border border-[#E5E5EA]/80 flex items-center gap-2 cursor-pointer transition-all active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]">
        <img src="{{ asset('icons/icons8-up-squared-60.png') }}" class="w-5 h-5 object-contain" alt="" aria-hidden="true">
        <span class="text-xs font-bold hidden sm:inline">Back to top</span>
    </button>
</div>
@endsection
