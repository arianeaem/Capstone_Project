@extends('layouts.app')

@section('title', 'Camp FreedivePH - Freediving Camps in Mabini, Batangas')
@section('meta_description', 'Learn freediving in Mabini, Batangas. Discovery classes for non-swimmers, fundives, and refinement practice dives.')

@section('content')
<div class="w-full">

    <!-- Hero Section -->
    <section id="hero-section" class="relative overflow-hidden bg-[#780000] pt-12 sm:pt-20 pb-0 text-white transition-colors m-0" style="--field-mask-x: 50%; --field-mask-y: 10%;">
        
        <!-- Background Glow Effect -->
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_60%_at_50%_0%,_#A0151E_0%,_#780000_50%,_#450000_100%)] pointer-events-none"></div>

        <!-- Dynamic Background Canvas -->
        <div class="hero-dynamic-field absolute inset-0 pointer-events-none overflow-hidden" 
             style="-webkit-mask-image: radial-gradient(ellipse 90% 70% at 50% 0%, black 0%, rgba(0,0,0,0.85) 50%, transparent 100%), linear-gradient(to bottom, black 65%, transparent 100%);
                    mask-image: radial-gradient(ellipse 90% 70% at 50% 0%, black 0%, rgba(0,0,0,0.85) 50%, transparent 100%), linear-gradient(to bottom, black 65%, transparent 100%);
                    -webkit-mask-composite: source-in;
                    mask-composite: intersect;">
            <canvas id="hero-gradient-canvas" class="w-full h-full block opacity-90"></canvas>
        </div>

        <!-- Lighting Overlay -->
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_0%,_rgba(255,255,255,0.12)_0%,_transparent_60%)] pointer-events-none"></div>

        <!-- Hero Content -->
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto">

                <!-- Main Heading -->
                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight text-white leading-[1.15] mb-5">
                    Sharing the love for ocean through freediving in <br class="hidden sm:inline" />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#63a5c4] via-[#164B60] to-[#2e80a3]">
                        Mabini, Batangas.
                    </span>
                </h1>

                <!-- Subtitle -->
                <p class="text-sm sm:text-lg text-[#FFFFFF]/90 leading-relaxed mb-8 sm:mb-10 font-normal max-w-2xl mx-auto">
                    Learn to hold your breath and dive safely with our friendly coaches.
                </p>

                <!-- Call to Action Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4">
                    <a href="{{ route('booking.create') }}" class="w-full sm:w-auto px-8 sm:px-10 py-3.5 sm:py-4 rounded-xl text-sm sm:text-base font-extrabold bg-[#00c3d0] hover:bg-[#00abb7] text-[#1D1D1F] text-center transition-all hover:scale-[1.02] flex items-center justify-center gap-2">
                        <span>Book Slot Now</span>
                        <svg class="w-5 h-5 ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>

                    <a href="{{ route('manage.index') }}" class="text-white hover:text-[#00c3d0] font-bold text-sm sm:text-base transition-colors px-3 py-3.5">
                        Manage Booking
                    </a>
                </div>
            </div>

            <!-- Hero Image Preview -->
            <div class="mt-12 sm:mt-16 max-w-5xl mx-auto">
                <div class="rounded-t-2xl sm:rounded-t-3xl border-t border-x border-white/20 bg-white/10 p-1.5 sm:p-2.5 shadow-2xl backdrop-blur-xs">
                    <div class="rounded-t-xl sm:rounded-t-2xl border-t border-x border-black/10 sm:border-white/30 overflow-hidden bg-white">
                        <img src="{{ asset('images/AdobeStock_272067459.jpeg') }}" 
                             alt="Camp FreedivePH Batangas" 
                             class="w-full h-64 sm:h-[420px] lg:h-[480px] object-cover object-top block">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Content Sections -->
    <div class="space-y-14 sm:space-y-24 mt-12 sm:mt-18">

    <!-- Class Packages Section -->
    <section id="packages" class="w-full bg-[#FAFAFC] py-16 sm:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12 sm:mb-16">
                <h2 class="text-4xl sm:text-6xl font-extrabold text-[#1D1D1F] tracking-tight">Diving packages for every stage of your journey</h2>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8 items-stretch">
                @foreach($classes as $c)
                <!-- Package Item -->
                <div class="bg-white rounded-2xl border border-[#E5E5EA] shadow-md hover:shadow-xl transition-all duration-300 flex flex-col justify-between overflow-hidden text-sm">
                    
                    <!-- Package Header & Price -->
                    <div class="bg-gradient-to-b from-[#F8EAEA] via-[#F8EAEA]/40 to-transparent border-b border-[#E5E5EA] p-6 sm:p-8 flex flex-col justify-between text-center min-h-[360px]">
                        <div>
                            <!-- Category Badge -->
                            <span class="text-xs font-black uppercase tracking-wider text-[#780000] block mb-2">
                                {{ $c['category'] }}
                            </span>

                            <!-- Title -->
                            <h3 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight mb-3">
                                {{ $c['name'] }}
                            </h3>

                            <!-- Pricing Display -->
                            <div class="my-3">
                                @if($c['id'] === 'fundive')
                                    <div class="space-y-2 text-sm">
                                        <div>
                                            <span class="text-xs font-semibold text-[#6E6E73] block">Certified freedivers:</span>
                                            <div class="text-3xl sm:text-4xl font-black text-[#1D1D1F] tracking-tight">
                                                2,500 <span class="text-base font-bold text-[#780000]">php</span>
                                            </div>
                                            <span class="text-xs text-[#8E8E93] italic block mt-0.5">(safety coach not included)</span>
                                        </div>
                                        <div class="pt-2 border-t border-[#F1D5D5]/80">
                                            <span class="text-xs font-semibold text-[#6E6E73] block">Non certified freedivers:</span>
                                            <div class="text-3xl sm:text-4xl font-black text-[#1D1D1F] tracking-tight">
                                                3,300 <span class="text-base font-bold text-[#780000]">php</span>
                                            </div>
                                        </div>
                                    </div>
                                @elseif($c['id'] === 'discovery')
                                    <div>
                                        <div class="text-4xl sm:text-5xl font-black text-[#1D1D1F] tracking-tight">
                                            4,250 <span class="text-xl font-bold text-[#780000]">php</span>
                                        </div>
                                        <span class="text-xs text-[#6E6E73] font-medium block mt-1">per person</span>
                                    </div>
                                @else
                                    <div>
                                        <div class="text-4xl sm:text-5xl font-black text-[#1D1D1F] tracking-tight">
                                            4,100 <span class="text-xl font-bold text-[#780000]">php</span>
                                        </div>
                                        <span class="text-xs text-[#6E6E73] font-medium block mt-1">per person</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Target Audience Description -->
                            <p class="text-sm sm:text-base text-[#4A4A4F] font-medium mt-3 mb-6 min-h-[44px] flex items-center justify-center leading-relaxed text-center">
                                {{ $c['note'] }}
                            </p>
                        </div>

                        <!-- Top CTA Button -->
                        <div class="pt-2">
                            <a href="{{ route('booking.create', ['class' => $c['id']]) }}" 
                                class="w-full py-3.5 px-6 rounded-xl font-extrabold text-sm text-center block shadow-md hover:shadow-lg transition-all duration-200 bg-[#780000] hover:bg-[#5E0000] text-white">
                                Book Class
                            </a>
                        </div>
                    </div>

                    <!-- Inclusions & Exclusions -->
                    <div class="p-6 sm:p-8 space-y-6 bg-white flex-grow flex flex-col justify-between">
                        <div class="space-y-5">
                            <!-- Inclusions -->
                            <div>
                                <h4 class="text-xs font-extrabold uppercase tracking-wider text-[#1D1D1F] mb-3">
                                    Inclusions
                                </h4>
                                <ul class="space-y-2.5 text-sm text-[#1D1D1F]">
                                    @foreach($c['inclusions'] as $inc)
                                    <li class="flex items-start gap-3">
                                        <svg class="w-4 h-4 text-[#780000] shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                        <span class="leading-snug">{{ $inc }}</span>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>

                            <!-- Exclusions -->
                            <div class="pt-4 border-t border-[#E5E5EA]">
                                <h4 class="text-xs font-extrabold uppercase tracking-wider text-[#6E6E73] mb-3">
                                    Exclusions
                                </h4>
                                <ul class="space-y-2 text-sm text-[#6E6E73]">
                                    @foreach($c['exclusions'] as $exc)
                                    <li class="flex items-start gap-3">
                                        <span class="text-[#8E8E93] shrink-0 font-bold leading-none mt-1">-</span>
                                        <span class="leading-snug">{{ $exc }}</span>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Logistics, Add-ons & Fees -->
    <section id="addons" class="max-w-7xl mx-auto px-8 sm:px-16 lg:px-32 text-sm">
        <div class="text-center max-w-2xl mx-auto mb-8 sm:mb-12">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight">Carpool, Add-ons & Reservation Policies</h2>
            <p class="text-sm sm:text-base text-[#6E6E73] mt-2">Transparent pricing and clear logistics for your 2D1N Batangas freedive experience.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8">
            
            <!-- Manila Carpool Service -->
            <div class="bg-[#F8EAEA] rounded-xl p-6 sm:p-7 border border-[#F1D5D5] space-y-4 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <h4 class="font-extrabold text-[#1D1D1F] text-base sm:text-lg">Manila Carpool Service</h4>
                    </div>
                    <p class="text-md sm:text-sm text-[#1D1D1F] leading-relaxed">
                        ₱1,200/person for roundtrip van transportation to Mabini, Batangas. Pickup points: Monumento, Shell Tiendesitas, Market! Market! BGC, Starmall Alabang, and Sto. Tomas SLEX Exit.
                    </p>
                </div>
            </div>

            <!-- Reservation Downpayment Policy -->
            <div class="bg-[#F8EAEA] rounded-xl p-6 sm:p-7 border border-[#F1D5D5] space-y-4 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="flex items-center justify-between gap-2">
                        <h4 class="font-extrabold text-[#1D1D1F] text-base sm:text-lg">Reservation Downpayment</h4>
                    </div>
                    <p class="text-md sm:text-sm text-[#1D1D1F] leading-relaxed">
                        A per-head downpayment is required to secure your slot: ₱3,000/person with carpool or ₱2,000/person with own transportation.
                    </p>
                </div>
            </div>

            <!-- Boat Dive Option -->
            <div class="bg-[#F8EAEA] rounded-xl p-6 sm:p-7 border border-[#F1D5D5] space-y-4 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <h4 class="font-extrabold text-[#1D1D1F] text-base sm:text-lg">Boat Dive (Optional)</h4>
                    </div>
                    <p class="text-md sm:text-sm text-[#1D1D1F] leading-relaxed">
                        ₱600/person for an optional boat excursion to deeper marine sanctuaries in Anilao for extended reef exploration and marine life observation.
                    </p>
                </div>
            </div>

            <!-- Mabini LGU Marine Fees -->
            <div class="bg-[#F8EAEA] rounded-xl p-6 sm:p-7 border border-[#F1D5D5] space-y-4 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <h4 class="font-extrabold text-[#1D1D1F] text-base sm:text-lg">Mabini LGU Marine Fees</h4>
                    </div>
                    <p class="text-md sm:text-sm text-[#1D1D1F] leading-relaxed">
                        Mandatory LGU fees support marine sanctuary preservation and coastal management. ₱50 one-time Municipal Environmental Fee and ₱300 for Mabini LGU Dive Pass fo 2 days.
                    </p>
                </div>
            </div>

        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-sm" x-data="{ openFaq: null }">
        <div class="text-center mb-10 sm:mb-12">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-[#1D1D1F] tracking-tight">Frequently Asked Questions</h2>
            <p class="text-sm sm:text-base text-[#6E6E73] mt-2">Everything you need to know before joining Camp FreedivePH.</p>
        </div>

        <div class="space-y-4">
            <!-- Things to Bring FAQ Item -->
            <div class="bg-white rounded-xl border border-[#E5E5EA] overflow-hidden transition-all">
                <button 
                    @click="openFaq = (openFaq === 'bring' ? null : 'bring')"
                    class="w-full px-5 sm:px-6 py-4 sm:py-5 text-left font-bold text-sm sm:text-base text-[#1D1D1F] flex items-center justify-between gap-4 hover:bg-[#FAFAFC] transition-colors"
                >
                    <span class="flex items-center gap-2">
                        <span>What are the things to bring for the 2D1N freediving camp?</span>
                    </span>
                    <svg class="w-5 h-5 text-[#6E6E73] transition-transform duration-200 shrink-0" :class="{ 'rotate-180 text-[#780000]': openFaq === 'bring' }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div x-show="openFaq === 'bring'" x-collapse x-cloak class="px-5 sm:px-6 pb-5 text-sm text-[#6E6E73] leading-relaxed border-t border-[#E5E5EA] pt-4 space-y-2">
                    <p>Please bring the following for your weekend stay:</p>
                    <ul class="list-disc list-inside space-y-1 text-[#1D1D1F] pl-2">
                        <li><strong>Swimming clothes:</strong> Any swimwear, rashguard, or leggings you are comfortable wearing in the water.</li>
                        <li><strong>Toiletries:</strong> Personal care items, sunscreen, and dry change of clothes.</li>
                        <li><strong>Personal essentials:</strong> Medication or personal items.</li>
                        <li><strong>A pair of socks:</strong> Any kind of socks to ensure comfortable fin fitting.</li>
                    </ul>
                    <p class="text-xs text-[#065F46] font-semibold">
                        Note: Towels, shampoo, and bath soap are all provided by the resort.
                    </p>
                </div>
            </div>

            @foreach($faqs as $index => $faq)
            <div class="bg-white rounded-xl border border-[#E5E5EA] overflow-hidden transition-all">
                <button 
                    @click="openFaq = (openFaq === {{ $index }} ? null : {{ $index }})"
                    class="w-full px-5 sm:px-6 py-4 sm:py-5 text-left font-bold text-sm sm:text-base text-[#1D1D1F] flex items-center justify-between gap-4 hover:bg-[#FAFAFC] transition-colors"
                >
                    <span>{{ $faq['q'] }}</span>
                    <svg class="w-5 h-5 text-[#6E6E73] transition-transform duration-200 shrink-0" :class="{ 'rotate-180 text-[#780000]': openFaq === {{ $index }} }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div x-show="openFaq === {{ $index }}" x-collapse x-cloak class="px-5 sm:px-6 pb-5 text-sm text-[#6E6E73] leading-relaxed border-t border-[#E5E5EA] pt-4">
                    {{ $faq['a'] }}
                </div>
            </div>
            @endforeach
        </div>
    </section>

    <!-- 5. Bottom Call to Action -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <div class="bg-gradient-to-r from-[#780000] to-[#5E0000] rounded-xl p-6 sm:p-14 text-white text-center shadow-xl flex flex-col items-center justify-center">
            <h3 class="text-2xl sm:text-4xl font-extrabold mb-4">Ready to Dive in Batangas?</h3>
            <p class="text-sm sm:text-base text-[#F8EAEA] max-w-xl mb-8 leading-relaxed">
                Join our 2D1N freedive camp. Discovery beginner classes, fundives, and refinement practice dives are open for booking.
            </p>
            <div class="flex flex-col sm:flex-row items-center gap-3 sm:gap-4 w-full sm:w-auto">
                <a href="{{ route('booking.create') }}" class="w-full sm:w-auto px-8 sm:px-10 py-3.5 sm:py-4 rounded-xl text-sm sm:text-base font-extrabold bg-[#00c3d0] hover:bg-[#00abb7] text-[#1D1D1F] text-center transition-all hover:scale-[1.02] flex items-center justify-center gap-2">
                    Book Your Freediving Adventure
                </a>
                <a href="{{ route('manage.index') }}" class="w-full sm:w-auto px-6 py-3.5 sm:py-4 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-sm transition-colors border border-white/20">
                    Find My Booking
                </a>
            </div>
        </div>
    </section>

</div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const heroSection = document.getElementById('hero-section');
    const canvas = document.getElementById('hero-gradient-canvas');
    if (!heroSection || !canvas) return;

    const ctx = canvas.getContext('2d');
    let width = 0;
    let height = 0;
    let dpr = window.devicePixelRatio || 1;

    function resize() {
        const rect = heroSection.getBoundingClientRect();
        width = rect.width;
        height = rect.height;
        canvas.width = width * dpr;
        canvas.height = height * dpr;
        ctx.scale(dpr, dpr);
    }

    window.addEventListener('resize', resize, { passive: true });
    resize();

    // Fixed top-centered mask coordinates (no mouse tracking)
    heroSection.style.setProperty('--field-mask-x', '50%');
    heroSection.style.setProperty('--field-mask-y', '0%');

    // Stationary harmonic field nodes in #780000 palette with subtle organic pulsation
    const nodes = [
        { x: 0.50, y: 0.05, r: 0.65, color1: 'rgba(215, 38, 56, 0.45)', color2: 'rgba(120, 0, 0, 0)' },   // Luminous Coral Red
        { x: 0.35, y: 0.20, r: 0.50, color1: 'rgba(255, 140, 50, 0.25)', color2: 'rgba(120, 0, 0, 0)' },  // Warm Amber Glow
        { x: 0.65, y: 0.20, r: 0.50, color1: 'rgba(235, 87, 87, 0.35)', color2: 'rgba(120, 0, 0, 0)' },   // Soft Rose Radiant
        { x: 0.50, y: 0.35, r: 0.70, color1: 'rgba(150, 10, 25, 0.50)', color2: 'rgba(74, 0, 0, 0)' },    // Deep Velvet Maroon
    ];

    let t = 0;
    function animate() {
        t += 0.008; // Gentle breathing ambient wave

        // Clear canvas
        ctx.clearRect(0, 0, width, height);

        // Base background fill in #780000
        ctx.globalCompositeOperation = 'source-over';
        const baseGrad = ctx.createLinearGradient(0, 0, 0, height);
        baseGrad.addColorStop(0, '#8A0000');
        baseGrad.addColorStop(0.5, '#780000');
        baseGrad.addColorStop(1, '#4A0000');
        ctx.fillStyle = baseGrad;
        ctx.fillRect(0, 0, width, height);

        // Ambient glow blend mode
        ctx.globalCompositeOperation = 'screen';

        nodes.forEach((node, i) => {
            // Subtle slow harmonic oscillation (strictly top-centered, no mouse drift)
            const nx = (node.x + Math.sin(t * 0.5 + i * 1.8) * 0.04) * width;
            const ny = (node.y + Math.cos(t * 0.4 + i * 1.5) * 0.03) * height;
            const radius = (node.r + Math.sin(t * 0.6 + i) * 0.05) * Math.max(width, height);

            const grad = ctx.createRadialGradient(nx, ny, 0, nx, ny, Math.max(1, radius));
            grad.addColorStop(0, node.color1);
            grad.addColorStop(0.5, node.color1.replace(/[\d\.]+\)$/, '0.15)'));
            grad.addColorStop(1, node.color2);

            ctx.fillStyle = grad;
            ctx.beginPath();
            ctx.arc(nx, ny, radius, 0, Math.PI * 2);
            ctx.fill();
        });

        requestAnimationFrame(animate);
    }

    requestAnimationFrame(animate);
});
</script>
@endpush
