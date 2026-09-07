<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePhoneIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && ! $request->user()->isPhoneVerified()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'ስልክ ቁጥርዎ አልተረጋገጠም።'], 403)
                : redirect()->route('phone.verify')->with('status', 'እድር ከመፍጠርዎ በፊት ስልክ ቁጥርዎን ማረጋገጥ አለብዎት።');
        }

        return $next($request);
    }
}
