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
	 * @param  string  ...$roles
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next, string ...$roles): Response
	{
		if (!Auth::check()) {
			return redirect()->route('login');
		}

		foreach ($roles as $role) {
			if (Auth::user()->hasRole($role)) {
				return $next($request);
			}
		}

		return redirect('/')
			->with('error', 'You do not have permission to access this resource.');
	}
}
