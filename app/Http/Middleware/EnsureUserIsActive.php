<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Reject requests from deactivated (and, optionally, unverified) accounts.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if (! $user->isActive()) {
            Auth::guard()->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->
                withErrors(['email' => 'Your account has been deactivated. Contact your administrator.']);
        }

        if (config('medtrack.require_email_verification') && is_null($user->email_verified_at) && $user instanceof MustVerifyEmail) {
            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
