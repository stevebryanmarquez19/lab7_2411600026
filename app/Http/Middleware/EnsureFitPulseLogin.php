<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureFitPulseLogin
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->get('fitpulse_authenticated')) {
            return redirect()->route('login')->with('login_notice', 'Please sign in to continue.');
        }
        return $next($request);
    }
}
