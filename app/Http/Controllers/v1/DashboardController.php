<?php

namespace App\Http\Controllers\v1;

use App\Helper\V1\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\FreelancerProfile;
use App\Models\Offer;
use App\Models\Project;
use App\Models\Review;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        return ApiResponse::success([
            'users_count' => User::count('id'),
            'projects_count' => Project::count('id'),
            'offers_count' => Offer::count('id'),
            'reviews_count' => Review::count('id'),
            'offers_value' => Offer::sum('amount') . ' $',
            'projects_value' => Project::sum('budget') . ' $',
        ]);
    }

    public function verifyUser(User $user)
    {
        $user->verified_at = now();
        $user->save();
        return ApiResponse::success([], 'User verified successfully ');
    }
}
