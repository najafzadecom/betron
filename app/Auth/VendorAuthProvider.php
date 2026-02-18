<?php

namespace App\Auth;

use App\Models\Vendor;
use App\Models\VendorUser;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Support\Facades\Session;

class VendorAuthProvider extends EloquentUserProvider
{
    private const SESSION_USER_TYPE_KEY = 'vendor_auth_user_type';

    /**
     * Create a new database user provider.
     */
    public function __construct(HasherContract $hasher)
    {
        $this->hasher = $hasher;
    }

    /**
     * Retrieve a user by the given credentials.
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (empty($credentials) ||
            (count($credentials) === 1 && array_key_exists('password', $credentials))) {
            return null;
        }

        // Try to find in Vendor first
        $query = Vendor::query();
        foreach ($credentials as $key => $value) {
            if (!str_contains($key, 'password')) {
                $query->where($key, $value);
            }
        }
        $user = $query->first();

        if ($user) {
            Session::put(self::SESSION_USER_TYPE_KEY, 'vendor');
            return $user;
        }

        // Try to find in VendorUser
        $query = VendorUser::query();
        foreach ($credentials as $key => $value) {
            if (!str_contains($key, 'password')) {
                $query->where($key, $value);
            }
        }

        $user = $query->first();
        if ($user) {
            Session::put(self::SESSION_USER_TYPE_KEY, 'vendor_user');
        }

        return $user;
    }

    /**
     * Retrieve a user by their unique identifier.
     */
    public function retrieveById($identifier): ?Authenticatable
    {
        $userType = Session::get(self::SESSION_USER_TYPE_KEY);

        if ($userType === 'vendor_user') {
            return VendorUser::find($identifier);
        }

        if ($userType === 'vendor') {
            return Vendor::find($identifier);
        }

        // Fallback for old sessions without user type - try Vendor first
        $user = Vendor::find($identifier);
        if ($user) {
            return $user;
        }

        return VendorUser::find($identifier);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     */
    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        $userType = Session::get(self::SESSION_USER_TYPE_KEY);

        if ($userType === 'vendor_user') {
            return VendorUser::where('id', $identifier)
                ->where('remember_token', $token)
                ->first();
        }

        if ($userType === 'vendor') {
            return Vendor::where('id', $identifier)
                ->where('remember_token', $token)
                ->first();
        }

        // Fallback for old sessions
        $user = Vendor::where('id', $identifier)
            ->where('remember_token', $token)
            ->first();

        if ($user) {
            return $user;
        }

        return VendorUser::where('id', $identifier)
            ->where('remember_token', $token)
            ->first();
    }

    /**
     * Update the "remember me" token for the given user in storage.
     */
    public function updateRememberToken(Authenticatable $user, $token): void
    {
        $user->setRememberToken($token);
        $user->save();
    }
}
