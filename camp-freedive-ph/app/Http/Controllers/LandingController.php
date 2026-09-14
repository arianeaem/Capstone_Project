<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class LandingController extends Controller
{
    /**
     * Display the Camp FreedivePH landing page.
     */
    public function index(): View
    {
        $classes = [
            'discovery' => [
                'id' => 'discovery',
                'name' => 'Discovery',
                'category' => 'BEGINNER CLASS',
                'price' => 4250,
                'price_label' => '4,250 php',
                'inclusions' => [
                    '2 open water dives (2-3 hrs per session)',
                    '1 pool session (10 ft deep pool access)',
                    '2D1N shared AC room accommodation',
                    'Lesson fee and coach fee',
                    'Safety buoy set up',
                    '3 full board meals',
                    'Photos and videos',
                    'Gears',
                ],
                'exclusions' => [
                    'Transportation (We arrange carpool)',
                    'Boat dive (optional)',
                    'Mabini LGU divepass',
                ],
                'note' => 'Perfect for first-time divers. Solo joiners and non-swimmers welcome.',
                'prerequisite' => null,
            ],
            'fundive' => [
                'id' => 'fundive',
                'name' => 'Fundive',
                'category' => 'PREREQUISITE: DISCOVERY CLASS',
                'price_certified' => 2500,
                'price_non_certified' => 3300,
                'price_label' => 'For certified freedivers: 2,500 php (safety coach not included) | For non certified freedivers: 3,300 php',
                'inclusions' => [
                    '2 open water dives (2-3 hrs per session)',
                    '1 pool session (10 ft deep pool access)',
                    '2D1N shared AC room accommodation',
                    'Safety coach fee',
                    'Safety buoy set up',
                    '3 full board meals',
                    'Photos and videos',
                    'Gears',
                ],
                'exclusions' => [
                    'Transportation (We arrange carpool)',
                    'Boat dive (optional)',
                    'Mabini LGU divepass',
                ],
                'note' => 'For divers ready to explore open water. Solo joiners welcome.',
                'prerequisite' => 'PREREQUISITE: DISCOVERY CLASS',
            ],
            'refinement' => [
                'id' => 'refinement',
                'name' => 'Refinement',
                'category' => 'PRACTICE DIVE',
                'price' => 4100,
                'price_label' => '4,100 php',
                'inclusions' => [
                    '2 open water dives (2-3 hrs per session)',
                    '1 pool session (10 ft deep pool access)',
                    '2D1N shared AC room accommodation',
                    '3 full board meals',
                    'Safety buoy set up',
                    'Photos and videos',
                    'Coach fee',
                    'Gears',
                ],
                'exclusions' => [
                    'Transportation (We arrange carpool)',
                    'Boat dive (optional)',
                    'Mabini LGU divepass',
                ],
                'note' => 'For divers looking to improve their skills. Solo joiners welcome.',
                'prerequisite' => 'Prerequisite: Discovery Class completion.',
            ],
        ];

        $faqs = [
            [
                'q' => 'Is this safe for someone who does not know how to swim?',
                'a' => 'Yes, our Discovery Class is open to solo joiners and non-swimmers. You will be guided closely with safety coach support and proper gear.',
            ],
            [
                'q' => 'What is the cancellation and reschedule policy?',
                'a' => 'More than 14 days before your dive date: Full downpayment refund or free reschedule. Within 7 to 14 days: Free reschedule to another available date. Within 7 days: Non-refundable and non-reschedulable unless an official storm warning / force majeure is active.',
            ],
            [
                'q' => 'How does transportation and carpool work?',
                'a' => 'We arrange carpools with pickup points in Monumento, Shell Tiendesitas, Market! Market!, Starmall Alabang, and Sto. Tomas Exit. If you choose carpool, the booking downpayment is 3,000 php per person. If you bring your own transpo, the downpayment is 2,000 php per person.',
            ],
            [
                'q' => 'What are the required local municipal fees?',
                'a' => 'Mabini LGU requires a Municipal Environmental Protection Fee and an LGU Dive Pass for marine conservation.',
            ],
        ];

        return view('landing', compact('classes', 'faqs'));
    }
}
