<?php

namespace App\Http\Middleware\v1;

use App\Helper\V1\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFreelancerisVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Before Middlewar ->
        //  the middleware ensure that the freelancer user is valiad to make this action before $request execution

        $user = $request->user();
        if (!$user->has_profile)
            return ApiResponse::forbidden('this freelancer has no profile');
        if ($user->isClient() || ($user->isFreelancer() && !$user->isVerified()))
            return ApiResponse::forbidden('this action is allowed only for verified freelancers');

        return $next($request);
    }
}
