<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWalletIsConfigured
{
    /**
     * Handle an incoming request.
     * Redirect user to wallet setup if wallet is not configured.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Skip jika user belum login
        if (!$user) {
            return $next($request);
        }

        // Skip jika sudah di halaman wallet setup
        if ($request->routeIs('wallet.setup') || $request->routeIs('wallet.setup.store')) {
            return $next($request);
        }

        // Skip jika ini adalah route logout atau profile
        if ($request->routeIs('logout') || $request->routeIs('profile.*')) {
            return $next($request);
        }

        $wallet = $user->walletSetting;

        // Cek apakah wallet sudah dikonfigurasi
        $isConfigured = $wallet && ($wallet->monthly_allowance > 0 || $wallet->weekly_allowance > 0);

        if (!$isConfigured) {
            return redirect()->route('wallet.setup');
        }

        return $next($request);
    }
}
