<?php

namespace App\Http\Controllers\v1;

use App\Helper\V1\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Auth\LoginRequest;
use App\Http\Requests\v1\Auth\RegisterRequest;
use App\Http\Resources\v1\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class AuthController extends Controller
{
    use HasApiTokens;

    public function register(RegisterRequest $request)
    {
        $user = User::create($request->validated());

        return ApiResponse::success(
            [
                new UserResource($user),
                'token' => $user->createToken("access-token")->plainTextToken,
            ]
            ,
            'User registered successfully',
            201
        );
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();
        if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password']]))
            return ApiResponse::notFound('the provided credentials are incorrect');
        $user = Auth::user();
        $token = $user->createToken("access-token")->plainTextToken;

        return ApiResponse::success(
            [
                'token' => $token,
                new UserResource($user),
            ],
            'User logged in successfully',
            200
        );


    }

    public function logout(Request $request)
    {
        if ($request->user()->currentAccessToken()->delete())
            return ApiResponse::success(null, 'User logged out successfully');
        return ApiResponse::error('User not logged out', 500);
    }
}
