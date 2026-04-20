<?php

namespace App\Http\Middleware\v1;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ApiLoggerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // After Middlewar -> so the middleware execut after $request execution
        $startTime = microtime(true);
        $response = $next($request);

        $endTime = microtime(true);
        $durationInms = ($endTime - $startTime) * 1000;

        DB::table('api_request_logs')->insert([
            'user_id' => $request->user()->id ?? null,
            'method' => $request->method(),
            'url' => $request->url(),
            'duration_ms' => $durationInms,
            'status_code' => $response->getStatusCode(),
            'ip_address' => $request->ip(),
            'created_at' => now()
        ]);
        return $response;
    }
}
