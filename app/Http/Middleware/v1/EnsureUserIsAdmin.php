<?php

namespace App\Http\Middleware\v1;

use App\Enums\UserTypeEnum;
use App\Helper\V1\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user->type !== UserTypeEnum::ADMIN->value)
            return ApiResponse::forbidden('this user has no access on this route');
        
        return $next($request);
    }
}
