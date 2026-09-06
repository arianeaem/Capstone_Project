<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RestrictGroup8Tester
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && in_array($user->email, ['group8@campfreedive.ph', 'tester@campfreedive.ph'])) {
            if ($request->is('admin', 'admin/*', 'owner', 'owner/*')) {
                $isAllowed = $request->is('admin/bookings*', 'admin/payments*', 'owner/bookings*', 'owner/payments*');

                if (!$isAllowed) {
                    return response()->view('errors.restricted_group8', [], 200);
                }
            }
        }

        return $next($request);
    }
}
