<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;

class AddVerifiedScope
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        User::addGlobalScope('verified', function ($builder) {
            $builder->whereNotNull('email_verified_at');
        });

        return $next($request);
    }
}
