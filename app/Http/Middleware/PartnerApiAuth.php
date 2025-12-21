<?php

namespace App\Http\Middleware;

use App\Models\Partner;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PartnerApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key') ?? $request->query('api_key');

        if (!$apiKey) {
            return response()->json([
                'error' => 'API key is required',
                'code' => 'MISSING_API_KEY'
            ], 401);
        }

        $partner = Partner::where('api_key', $apiKey)
            ->where('is_active', true)
            ->first();

        if (!$partner) {
            return response()->json([
                'error' => 'Invalid API key',
                'code' => 'INVALID_API_KEY'
            ], 401);
        }

        // Attach partner to request for use in controllers
        $request->merge(['api_partner' => $partner]);
        $request->attributes->set('api_partner', $partner);

        return $next($request);
    }
}
