@extends('layouts.app')

@section('title', 'Terms and Conditions | Camp FreedivePH')
@section('meta_description', 'Read Camp FreedivePH terms and conditions, water safety policies, cancellation rules, and camp guidelines in Mabini, Batangas.')

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
            Camp FreedivePH Terms and Conditions of Service
        </h1>

        <p class="text-xs sm:text-sm text-[#636366]">
            Effective as of September 13, 2026
        </p>

        <!-- Document Switcher -->
        <nav class="pt-2" aria-label="Legal documents">
            <div class="inline-flex p-1 bg-[#F2F2F7] rounded-xl text-sm font-semibold">
                <span class="px-4 py-2 bg-white text-[#1D1D1F] rounded-lg shadow-2xs">Terms &amp; Conditions</span>
                <a href="{{ route('legal.privacy') }}" 
                   class="min-h-[44px] px-4 py-2 inline-flex items-center text-[#636366] hover:text-[#1D1D1F] transition-colors rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]">
                    Privacy Policy
                </a>
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
                    <span>1. Introduction and Scope</span>
                    <img src="{{ asset('icons/icons8-arrow-right-50.png') }}" class="w-4 h-4 object-contain opacity-60 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all shrink-0" alt="" aria-hidden="true">
                </a>
            </li>
            <li>
                <a href="#water-safety" @click.prevent="scrollToSection('water-safety')" 
                   class="min-h-[44px] px-3.5 py-2.5 rounded-xl hover:bg-white/80 transition-all font-medium flex items-center justify-between group focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]">
                    <span>2. Water Safety, Eligibility and Health Disclosures</span>
                    <img src="{{ asset('icons/icons8-arrow-right-50.png') }}" class="w-4 h-4 object-contain opacity-60 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all shrink-0" alt="" aria-hidden="true">
                </a>
            </li>
            <li>
                <a href="#downpayment-pricing" @click.prevent="scrollToSection('downpayment-pricing')" 
                   class="min-h-[44px] px-3.5 py-2.5 rounded-xl hover:bg-white/80 transition-all font-medium flex items-center justify-between group focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]">
                    <span>3. Downpayments, Dynamic Pricing and Payment Terms</span>
                    <img src="{{ asset('icons/icons8-arrow-right-50.png') }}" class="w-4 h-4 object-contain opacity-60 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all shrink-0" alt="" aria-hidden="true">
                </a>
            </li>
            <li>
                <a href="#cancellation-policy" @click.prevent="scrollToSection('cancellation-policy')" 
                   class="min-h-[44px] px-3.5 py-2.5 rounded-xl hover:bg-white/80 transition-all font-medium flex items-center justify-between group focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]">
                    <span>4. 14-Day Cancellation and Reschedule Policy</span>
                    <img src="{{ asset('icons/icons8-arrow-right-50.png') }}" class="w-4 h-4 object-contain opacity-60 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all shrink-0" alt="" aria-hidden="true">
                </a>
            </li>
            <li>
                <a href="#weather-safety" @click.prevent="scrollToSection('weather-safety')" 
                   class="min-h-[44px] px-3.5 py-2.5 rounded-xl hover:bg-white/80 transition-all font-medium flex items-center justify-between group focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]">
                    <span>5. Weather Safety and Force Majeure</span>
                    <img src="{{ asset('icons/icons8-arrow-right-50.png') }}" class="w-4 h-4 object-contain opacity-60 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all shrink-0" alt="" aria-hidden="true">
                </a>
            </li>
            <li>
                <a href="#ocean-conservation" @click.prevent="scrollToSection('ocean-conservation')" 
                   class="min-h-[44px] px-3.5 py-2.5 rounded-xl hover:bg-white/80 transition-all font-medium flex items-center justify-between group focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]">
                    <span>6. Marine Sanctuary and Ocean Conservation</span>
                    <img src="{{ asset('icons/icons8-arrow-right-50.png') }}" class="w-4 h-4 object-contain opacity-60 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all shrink-0" alt="" aria-hidden="true">
                </a>
            </li>
            <li>
                <a href="#liability-waiver" @click.prevent="scrollToSection('liability-waiver')" 
                   class="min-h-[44px] px-3.5 py-2.5 rounded-xl hover:bg-white/80 transition-all font-medium flex items-center justify-between group focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]">
                    <span>7. Assumption of Risk and Liability Waiver</span>
                    <img src="{{ asset('icons/icons8-arrow-right-50.png') }}" class="w-4 h-4 object-contain opacity-60 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all shrink-0" alt="" aria-hidden="true">
                </a>
            </li>
            <li>
                <a href="#contact" @click.prevent="scrollToSection('contact')" 
                   class="min-h-[44px] px-3.5 py-2.5 rounded-xl hover:bg-white/80 transition-all font-medium flex items-center justify-between group focus:outline-none focus-visible:ring-2 focus-visible:ring-[#780000]">
                    <span>8. Contact Us</span>
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
                1. Introduction and Scope
            </h2>
            <p>
                Welcome to Camp FreedivePH. These Terms and Conditions of Service ("Terms") govern your reservation, participation, and use of the services offered by Camp FreedivePH ("we", "us", or "our") in partnership with <em>The Shack Hideaway</em> in Barangay Bagalangit, Mabini, Batangas, Philippines.
            </p>
            <p>
                By reserving a slot, paying a downpayment, or attending any of our 2D1N weekend dive camps, open-water training sessions, or discovery classes, you agree to be bound by these Terms in full.
            </p>
        </section>

        <!-- 2. Water Safety -->
        <section id="water-safety" class="scroll-mt-24 sm:scroll-mt-28 space-y-3 pt-6 border-t border-[#E5E5EA]">
            <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] tracking-tight">
                2. Water Safety, Eligibility and Health Disclosures
            </h2>
            <p>
                Safety is our highest priority. To participate in our dive camps:
            </p>
            <ul class="list-disc list-outside pl-5 space-y-2 text-[#3A3A3C]">
                <li><strong>Beginner Friendliness:</strong> Discovery Class is open to solo joiners and non-swimmers. All sessions are conducted under direct supervision of certified freediving instructors maintaining a strict maximum ratio.</li>
                <li><strong>Physical Fitness:</strong> Participants must be in suitable physical and medical condition for aquatic activities. If you have a medical history of cardiovascular disease, epilepsy, asthma, pneumothorax, ear/sinus surgery, or other serious health conditions, you must present a written medical clearance from a physician prior to diving.</li>
                <li><strong>Substance Policy:</strong> Alcohol, drugs, or impairing substances are strictly prohibited before and during open-water dive sessions. Instructors reserve the right to exclude any participant from in-water sessions for safety reasons without refund.</li>
            </ul>
        </section>

        <!-- 3. Downpayment & Pricing -->
        <section id="downpayment-pricing" class="scroll-mt-24 sm:scroll-mt-28 space-y-3 pt-6 border-t border-[#E5E5EA]">
            <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] tracking-tight">
                3. Downpayments, Dynamic Pricing and Payment Terms
            </h2>
            <p>
                To secure your reservation, a downpayment is required per participant:
            </p>
            <ul class="list-disc list-outside pl-5 space-y-2 text-[#3A3A3C]">
                <li><strong>Own Transportation:</strong> ₱2,000 downpayment per participant.</li>
                <li><strong>Carpool Service:</strong> ₱3,000 downpayment per participant.</li>
            </ul>
            <p>
                Downpayments are processed online through our secure PayMongo checkout gateway (supporting GCash, Maya, GrabPay, and Cards). The remaining balance is payable upon check-in at the camp in cash or digital bank transfer.
            </p>
            <p>
                Dynamic pricing discounts or seasonal adjustments are calculated at the time of reservation submission and locked once downpayment is completed.
            </p>
        </section>

        <!-- 4. Cancellation & Reschedule -->
        <section id="cancellation-policy" class="scroll-mt-24 sm:scroll-mt-28 space-y-4 pt-6 border-t border-[#E5E5EA]">
            <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] tracking-tight">
                4. 14-Day Cancellation and Reschedule Policy
            </h2>
            <p>
                We enforce a structured 14-day policy to accommodate hotel room allocations and coach scheduling:
            </p>

            <div class="space-y-3 pt-1">
                <!-- Tier 1: > 14 Days -->
                <div class="p-4 sm:p-5 rounded-2xl bg-[#F2F2F7] space-y-2">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h3 class="font-extrabold text-[#1D1D1F] text-base">More than 14 Days Before Dive Date</h3>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                            100% Refund or Free Reschedule
                        </span>
                    </div>
                    <p class="text-sm text-[#3A3A3C]">
                        You are eligible for a <strong>100% full downpayment refund</strong> processed within 3 to 5 banking days, or a <strong>free reschedule</strong> to any available future batch date within 6 months.
                    </p>
                </div>

                <!-- Tier 2: 7 - 14 Days -->
                <div class="p-4 sm:p-5 rounded-2xl bg-[#F2F2F7] space-y-2">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h3 class="font-extrabold text-[#1D1D1F] text-base">Within 7 to 14 Days Before Dive Date</h3>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900">
                            Free Reschedule Only
                        </span>
                    </div>
                    <p class="text-sm text-[#3A3A3C]">
                        You may request a <strong>free reschedule</strong> to another open batch date. Downpayments are preserved but non-refundable at this stage.
                    </p>
                </div>

                <!-- Tier 3: < 7 Days -->
                <div class="p-4 sm:p-5 rounded-2xl space-y-2">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h3 class="font-extrabold text-[#780000] text-base">Within 7 Days Before Dive Date</h3>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-[#D70015] text-white">
                            Non-Refundable / Locked
                        </span>
                    </div>
                    <p class="text-sm text-[#3A3A3C]">
                        Reservations are <strong>non-refundable and locked</strong>. Downpayments are forfeited due to committed resort accommodation reservations and coach allocations.
                    </p>
                </div>
            </div>
        </section>

        <!-- 5. Weather Safety -->
        <section id="weather-safety" class="scroll-mt-24 sm:scroll-mt-28 space-y-3 pt-6 border-t border-[#E5E5EA]">
            <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] tracking-tight">
                5. Weather Safety and Force Majeure
            </h2>
            <div class="p-4 sm:p-5 rounded-2xl bg-sky-50/80 text-sky-950 space-y-2">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-200 text-sky-900">Force Majeure Protection</span>
                </div>
                <p class="text-sm leading-relaxed">
                    If official PAGASA storm signals (TCWS) are active in Batangas, or the Philippine Coast Guard (PCG) issues a sea travel suspension or gale warning, Camp FreedivePH will notify participants immediately.
                </p>
                <p class="text-sm leading-relaxed font-medium text-sky-900">
                    In such force majeure events, standard cancellation restrictions are automatically waived, and guests may choose between a <strong>100% full refund</strong> or <strong>priority free rescheduling</strong>.
                </p>
            </div>
        </section>

        <!-- 6. Ocean Conservation -->
        <section id="ocean-conservation" class="scroll-mt-24 sm:scroll-mt-28 space-y-3 pt-6 border-t border-[#E5E5EA]">
            <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] tracking-tight">
                6. Marine Sanctuary and Ocean Conservation
            </h2>
            <p>
                Mabini, Batangas is a protected marine sanctuary. All divers must observe the following environmental practices:
            </p>
            <ul class="list-disc list-outside pl-5 space-y-1.5 text-[#3A3A3C]">
                <li>Use reef-safe sunscreen only.</li>
                <li>Do not touch, stand on, or kick coral reefs.</li>
                <li>Never harass, chase, or touch sea turtles, marine mammals, or other wildlife.</li>
                <li>Comply with Mabini LGU Marine Environmental Fee requirements upon arrival.</li>
            </ul>
        </section>

        <!-- 7. Liability Waiver -->
        <section id="liability-waiver" class="scroll-mt-24 sm:scroll-mt-28 space-y-3 pt-6 border-t border-[#E5E5EA]">
            <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] tracking-tight">
                7. Assumption of Risk and Liability Waiver
            </h2>
            <p>
                Freediving is an aquatic sport involving inherent physical risks, including changes in hydrostatic pressure, sea currents, fatigue, and hypoxic events (blackouts or loss of motor control if safety rules are violated). By participating, you acknowledge these risks and agree to follow all safety instructions given by Camp FreedivePH coaches.
            </p>
        </section>

        <!-- 8. Contact Us -->
        <section id="contact" class="scroll-mt-24 sm:scroll-mt-28 space-y-3 pt-6 border-t border-[#E5E5EA]">
            <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] tracking-tight">
                8. Contact Us
            </h2>
            <p>
                If you have questions regarding these Terms and Conditions, you may contact us through any of the following channels:
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
