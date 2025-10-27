<?php

namespace App\Http\Middleware;

use Closure;

class EnsureRole {
    public function handle($request, Closure $next, ...$roles) {
        $user = $request->user();
        abort_unless($user && in_array($user->role, $roles), 403, 'Forbidden');
        return $next($request);
    }
}

