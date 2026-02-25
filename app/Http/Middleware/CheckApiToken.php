<?php

namespace App\Http\Middleware;

use App\Models\Site;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => __('Bearer token is required. Please provide a valid token in the Authorization header.'),
            ], 401);
        }

        $token = substr($authHeader, 7);

        if (empty(trim($token))) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => __('Token does not exist. Please provide a valid token.'),
            ], 401);
        }

        // Find site by API token (multi-site support)
        $site = Site::query()->where('token', $token)->first();

        if (!$site) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => __('Invalid token. Please check your token and try again.'),
            ], 401);
        }

        $request->merge(['site_id' => $site->id]);
        $request->merge(['site_name' => $site->name]);
        $request->merge(['transaction_fee' => $site->transaction_fee]);
        $request->merge(['withdrawal_fee' => $site->withdrawal_fee]);

        return $next($request);
    }
}
