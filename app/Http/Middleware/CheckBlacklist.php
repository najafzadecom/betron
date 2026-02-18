<?php

namespace App\Http\Middleware;

use App\Services\BlacklistService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckBlacklist
{
    public function __construct(private BlacklistService $blacklistService)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param \Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get user ID if authenticated
        $userId = null;
        if (Auth::check()) {
            $userId = Auth::id();
        } elseif ($request->has('user_id')) {
            $userId = (int)$request->get('user_id');
        }

        // Get client IP
        $clientIp = $request->ip();

        // Check if request should be blocked
        if ($this->blacklistService->shouldBlockRequest($userId, $clientIp)) {
            return response()->json([
                'success' => false,
                'message' => 'Giriş qadağandır. Bu istifadəçi və ya IP ünvanı qara siyahıdadır.',
                'error_code' => 'BLACKLISTED',
            ], 403);
        }

        return $next($request);
    }
}
