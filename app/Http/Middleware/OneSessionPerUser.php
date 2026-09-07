<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * When the authenticated user has an existing panel session (other than the
 * current one), kill it so only one panel session exists at a time. This is
 * used where concurrent sessions cause stale-context redirects.
 */
class OneSessionPerUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (Auth::check() && method_exists($request->session(), 'getSessionManager')) {
            $sessionManager = $request->session()->getSessionManager();
            $currentId = $request->session()->getId();

            if (method_exists($sessionManager, 'all')) {
                foreach ($sessionManager->all() as $id) {
                    if ($id === $currentId) {
                        continue;
                    }

                    $payload = $sessionManager->read($id);

                    if (! $payload || ($payload['login_user_id'] ?? null) !== Auth::id()) {
                        continue;
                    }

                    $sessionManager->destroy($id);
                }
            }
        }

        return $response;
    }
}
