<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    public function handle(Request $request, Closure $next)
    {
        if (in_array($request->getRequestUri(), $this->except)) {
            return $next($request);
        }

        return $next($request)->withHeaders([
            'Access-Control-Allow-Origin'=> '*',
            'Content-Type'=> 'application/json',
            'Access-Control-Allow-Headers'=> 'Content-Type,Authorization',
        ]);
    }

    protected array $except = ['/api'];
}
