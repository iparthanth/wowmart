<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authorization for the whole panel, in one place.
 *
 * Applied to the route group rather than per-route, so a new admin screen is
 * protected the moment it is added - there is no per-controller discipline to
 * forget. A signed-in customer who discovers /admin gets 403, not a redirect
 * loop and not a login form they could mistake for their own.
 *
 * Banned is checked on every request, not only at sign-in: an admin banned
 * while signed in, or holding an API token, loses access immediately.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless($user?->isAdmin() && ! $user->banned, 403);

        return $next($request);
    }
}
