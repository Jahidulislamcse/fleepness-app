<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Container\Attributes\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function __construct(#[Auth] private Guard $auth)
    {
        //
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        if ($this->auth->check() && $this->auth->user()->role === $role) {
            return $next($request);
        }

        // Different response for API requests
        if ($request->expectsJson()) {
            response()->json(['message' => 'Access denied. Admins only.'], Response::HTTP_FORBIDDEN)->throwResponse();
        }

        return redirect('/');
    }
}
