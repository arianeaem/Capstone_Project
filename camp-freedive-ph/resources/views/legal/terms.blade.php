@extends('layouts.app')

@section('title', 'Terms and Conditions | Camp FreedivePH')
@section('meta_description', 'Read Camp FreedivePH terms and conditions, water safety policies, cancellation rules, and camp guidelines in Mabini, Batangas.')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-12 sm:py-20 text-sm text-[#1D1D1F] leading-relaxed">

    <!-- Legal Header -->
    <header class="space-y-4 pb-8 border-b border-[#E5E5EA]">
        <div class="flex items-center gap-2 text-xs font-semibold text-[#6E6E73] uppercase tracking-wider">
            <a href="{{ route('landing') }}" class="hover:text-[#1D1D1F] transition-colors">Home</a>
            <span>/</span>
            <span>Legal</span>
        </div>

        <h1 class="text-3xl sm:text-5xl font-extrabold text-[#1D1D1F] tracking-tight">
            Camp FreedivePH Terms and Conditions of Service
        </h1>

        <p class="text-xs sm:text-sm text-[#6E6E73]">
            <span>Effective as of September 13, 2026</span><br>
            <span>Mabini, Batangas, Philippines</span>
        </p>

        <!-- Document Switcher -->
        <nav class="flex items-center gap-4 pt-2 text-sm" aria-label="Legal documents">
            <span class="font-bold text-[#1D1D1F] border-b-2 border-[#1D1D1F] pb-1">Terms and Conditions</span>
            <a href="{{ route('legal.privacy') }}" class="text-[#6E6E73] hover:text-[#1D1D1F] transition-colors pb-1">
                Privacy Policy
            </a>
        </nav>
    </header>

    <!-- Table of Contents (Spotify Style) -->
    <nav class="my-10 p-6 sm:p-8 bg-[#F8F9FA] rounded-xl space-y-3 border border-[#E5E5EA]" 
         aria-label="Table of Contents"
         x-data="{
             scrollToSection(id) {
                 const target = document.getElementById(id);
                 if (target) {
                     target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                     try {
                         history.pushState(null, '', '#' + id);
                     } catch(e) {}
                 }
             }
         }">
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#6E6E73]">Table of Contents</h2>
        <ol class="space-y-2 text-sm text-[#1D1D1F] list-decimal list-inside">
            <li><a href="#introduction" @click.prevent="scrollToSection('introduction')" class="text-[#1D1D1F] hover:text-[#780000] hover:underline transition-colors font-medium">Introduction and Scope</a></li>
            <li><a href="#water-safety" @click.prevent="scrollToSection('water-safety')" class="text-[#1D1D1F] hover:text-[#780000] hover:underline transition-colors font-medium">Water Safety, Eligibility and Health Disclosures</a></li>
            <li><a href="#downpayment-pricing" @click.prevent="scrollToSection('downpayment-pricing')" class="text-[#1D1D1F] hover:text-[#780000] hover:underline transition-colors font-medium">Downpayments, Dynamic Pricing and Payment Terms</a></li>
            <li><a href="#cancellation-policy" @click.prevent="scrollToSection('cancellation-policy')" class="text-[#1D1D1F] hover:text-[#780000] hover:underline transition-colors font-medium">14-Day Cancellation and Reschedule Policy</a></li>
            <li><a href="#weather-safety" @click.prevent="scrollToSection('weather-safety')" class="text-[#1D1D1F] hover:text-[#780000] hover:underline transition-colors font-medium">Weather Safety and Force Majeure</a></li>
            <li><a href="#ocean-conservation" @click.prevent="scrollToSection('ocean-conservation')" class="text-[#1D1D1F] hover:text-[#780000] hover:underline transition-colors font-medium">Marine Sanctuary and Ocean Conservation</a></li>
            <li><a href="#liability-waiver" @click.prevent="scrollToSection('liability-waiver')" class="text-[#1D1D1F] hover:text-[#780000] hover:underline transition-colors font-medium">Assumption of Risk and Liability Waiver</a></li>
            <li><a href="#contact" @click.prevent="scrollToSection('contact')" class="text-[#1D1D1F] hover:text-[#780000] hover:underline transition-colors font-medium">Contact Us</a></li>
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
        <section id="cancellation-policy" class="scroll-mt-24 sm:scroll-mt-28 space-y-3 pt-6 border-t border-[#E5E5EA]">
            <h2 class="text-xl sm:text-2xl font-bold text-[#1D1D1F] tracking-tight">
                4. 14-Day Cancellation and Reschedule Policy
            </h2>
            <p>
                We enforce a structured 14-day policy to accommodate hotel room allocations and coach scheduling:
            </p>
            <div class="space-y-4 pt-2">
                <div>
                    <h3 class="font-bold text-[#1D1D1F]">More than 14 Days Before Dive Date:</h3>
                    <p class="text-[#3A3A3C]">
                        You are eligible for a <strong>100% full downpayment refund</strong> processed within 3 to 5 banking days, or a <strong>free reschedule</strong> to any available future batch date within 6 months.
                    </p>
                </div>
                <div>
                    <h3 class="font-bold text-[#1D1D1F]">Within 7 to 14 Days Before Dive Date:</h3>
                    <p class="text-[#3A3A3C]">
                        You may request a <strong>free reschedule</strong> to another open batch date. Downpayments are preserved but non-refundable at this stage.
                    </p>
                </div>
                <div>
                    <h3 class="font-bold text-[#1D1D1F]">Within 7 Days Before Dive Date:</h3>
                    <p class="text-[#3A3A3C]">
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
            <p>
                If official PAGASA storm signals (TCWS) are active in Batangas, or the Philippine Coast Guard (PCG) issues a sea travel suspension or gale warning, Camp FreedivePH will notify participants immediately.
            </p>
            <p>
                In such force majeure events, standard cancellation restrictions are automatically waived, and guests may choose between a <strong>100% full refund</strong> or <strong>priority free rescheduling</strong>.
            </p>
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
                        <a href="https://www.facebook.com/Campfreediveph/" target="_blank" rel="noopener noreferrer" class="text-[#780000] font-bold underline hover:text-[#5E0000]">@Campfreediveph</a>
                    </div>
                    <div>
                        <span class="text-[#6E6E73] font-medium">Instagram:</span>
                        <a href="https://www.instagram.com/campfreediveph/" target="_blank" rel="noopener noreferrer" class="text-[#780000] font-bold underline hover:text-[#5E0000]">@campfreediveph</a>
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
</div>
@endsection
