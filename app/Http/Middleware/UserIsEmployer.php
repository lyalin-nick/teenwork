<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UserIsEmployer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && !$request->user()->isEmployer()) {
            $response = [
                'success' => false,
                'message' => 'Action not available.'
            ];

            return response()->json($response, 403);
        }
        return $next($request);
    }
}
