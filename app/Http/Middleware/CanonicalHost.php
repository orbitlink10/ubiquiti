<?php

namespace App\Http\Middleware;

use App\Support\CanonicalUrl;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        if (in_array(strtolower($request->getHost()), ['ubiquityunifikenya.co.ke', 'www.ubiquityunifikenya.co.ke'], true)) {
            if (! $request->isSecure() || strtolower($request->getHost()) !== 'ubiquityunifikenya.co.ke') {
                return redirect()->away(CanonicalUrl::PRODUCTION_URL.$request->getRequestUri(), 301);
            }
        }

        return $next($request);
    }
}
