<?php

namespace App\Http\Middleware;

use App\Support\Merchant;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetMerchantLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Merchant::isMerchant()) {
            app()->setLocale('en');
        }

        return $next($request);
    }
}
