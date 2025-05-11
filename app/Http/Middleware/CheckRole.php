<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @param  string  $role
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next, string $role): Response
	{
		if (!Auth::check()) {
			return response()->json([
				'error' => [
					'code' => 'UNAUTHORIZED',
					'description' => 'You must be logged in to access this resource.'
				]
			], Response::HTTP_UNAUTHORIZED);
		}

		if (Auth::user()->hasRole($role)) {
			return $next($request);
		}

		return response()->json([
			'error' => [
				'code' => 'INSUFFICIENT_ROLE',
				'description' => 'You are not authorized to access this resource.'
			]
		], Response::HTTP_FORBIDDEN);
	}
}
