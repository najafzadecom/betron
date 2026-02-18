<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\VendorUser;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

abstract class BaseController extends Controller
{
    protected string $template = 'vendor';

    protected string $module = '';

    protected array $data = [];

    /**
     * Get the vendor ID from authenticated user
     * Returns vendor->id for Vendor, vendor_id for VendorUser
     */
    protected function getVendorId(): ?int
    {
        $user = Auth::guard('vendor')->user();

        if ($user instanceof Vendor) {
            return $user->id;
        }

        if ($user instanceof VendorUser) {
            return $user->vendor_id;
        }

        return null;
    }

    public function redirectSuccess(
        string $route = 'vendor.dashboard',
        string $message = ''
    ): RedirectResponse {
        return redirect()
            ->route($route)
            ->with([
                'success' => true,
                'message' => $message,
            ]);
    }

    public function redirectSuccessBack(
        string $message = ''
    ): RedirectResponse {
        return redirect()
            ->back()
            ->with([
                'success' => true,
                'message' => $message,
            ]);
    }

    public function redirectError(
        string $message = 'Unknown error'
    ): RedirectResponse {
        return redirect()
            ->back()
            ->with([
                'success' => false,
                'message' => $message,
            ]);
    }

    public function json($data = null, $code = 200): JsonResponse
    {
        return response()->json($data ?? $this->data, $code);
    }

    public function render($view = null): Renderable|JsonResponse
    {
        if (request()->expectsJson() || is_null($view)) {
            return $this->json();
        }

        return view($this->template . '/modules/' . $this->module . '/' . $view, $this->data);
    }
}
