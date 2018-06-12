<?php

namespace App\Http\Middleware;

use Closure;

class CheckRole
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @param  string                   $role
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        if (\Auth::check() && \Auth::user()->hasRole($role)) {
            return $next($request);
        }

        return redirect('home');
    }
}
