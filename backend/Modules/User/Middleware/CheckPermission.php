<?php

namespace Modules\User\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);

        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $routeName = $request->route()?->getName();
        if (!in_array($routeName, $user->permissions ?? [])) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
