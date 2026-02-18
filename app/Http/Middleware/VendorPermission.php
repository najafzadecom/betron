<?php

namespace App\Http\Middleware;

use App\Models\Vendor;
use App\Models\VendorUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VendorPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = auth('vendor')->user();

        if (!$user) {
            abort(403, 'Giriş yapmalısınız');
        }

        // If user is a Vendor (main account), allow all access
        if ($user instanceof Vendor) {
            return $next($request);
        }

        // If user is a VendorUser, check permissions
        if ($user instanceof VendorUser) {
            // Split permission string by | for multiple permissions
            $permissions = explode('|', $permission);
            
            // Check if user has any of the permissions (explicitly use vendor guard)
            foreach ($permissions as $perm) {
                if ($user->hasPermissionTo(trim($perm), 'vendor')) {
                    return $next($request);
                }
            }

            abort(403, 'Bu əməliyyat üçün icazəniz yoxdur');
        }

        abort(403, 'Naməlum istifadəçi tipi');
    }
}

