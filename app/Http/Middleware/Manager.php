<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Enum\UserRole;

class Manager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $allowedRoles = [UserRole::STAFF->value, UserRole::TECHNICIAN->value, UserRole::ADMIN->value];

        if (!in_array(Auth::user()->role->value, $allowedRoles)) {
            return redirect('/home');
        }

        return $next($request);
    }
}
