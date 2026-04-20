<?php

namespace App\Http\Controllers\v1;

use App\Helper\V1\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\freelancer\StoreFreelancerProfileRequest;
use App\Http\Requests\V1\freelancer\UpdateFreelancerProfileRequest;
use App\Http\Resources\v1\FreelancerProfileResource;
use App\Http\Resources\v1\UserResource;
use App\Models\FreelancerProfile;
use App\Services\v1\FreelancersService;
use Illuminate\Support\Facades\DB;

class FreelancerProfileController extends Controller
{

    public function __construct(private FreelancersService $freelancersService)
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = $this->freelancersService->getAllFreelancers()->get()->all();

        return ApiResponse::success(
            UserResource::collection($data)
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFreelancerProfileRequest $request)
    {
        $data = $request->validated();
        $profile = $this->freelancersService->storeFreelancerProfile($data);
        return ApiResponse::success(
            new FreelancerProfileResource($profile),
            'profile created successfully!',
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(FreelancerProfile $freelancerProfile)
    {
        /**
         * Solveing the problem :
         *  1- i used eager loading relation with `user` Model to get some columns and show it.
         *  2- using Resources to get a specific data with full controll to the output response.
         *  3 - I stored the (skills_summary & portfolio_urls ) in json fields to make it easer for reading
         *
         * ->  so in this steps i thing the problem is solved and i dont have N+1 problem
         */

        DB::enableQueryLog(); // تفعيل تسجيل الكويريز

        $profile = $this->freelancersService->getFreelancerProfile($freelancerProfile->id);

        $queryLog = DB::getQueryLog(); // جلب سجل الاستعلامات

        if ($profile === false)
            return ApiResponse::notFound();

        return ApiResponse::success(
            [
                'profile' => new FreelancerProfileResource($profile) ?? null,
                // 'data' => $profile,
                'db_debugger' => [
                    'count' => count($queryLog), // عدد الكويريز المنفذة
                    'queries' => $queryLog,
                ]
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFreelancerProfileRequest $request, FreelancerProfile $freelancerProfile)   
    {
        if (!$freelancerProfile)
            return ApiResponse::notFound();
        $data = $this->freelancersService->updateFreelancerProfile($freelancerProfile, $request->validated()) ?? null;

        return ApiResponse::success($data, 'profile updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FreelancerProfile $freelancerProfile)   
    {
        if (!$freelancerProfile)
            return ApiResponse::notFound();

        $this->freelancersService->deleteFreelancerProfile(auth()->user(), $freelancerProfile);
        return ApiResponse::success(null, 'profile deleted successfully!', 204);
    }


}
