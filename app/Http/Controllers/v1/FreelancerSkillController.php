<?php

namespace App\Http\Controllers\v1;

use App\Helper\V1\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\skill\StoreSkillRequest;
use App\Http\Requests\v1\skill\UpdateSkillRequest;
use App\Http\Resources\v1\FreelancerProfileResource;
use App\Models\FreelancerProfile;
use App\Models\Skill;
use App\Services\v1\FreelancersService;

class FreelancerSkillController extends Controller
{
    public function __construct(private FreelancersService $freelancersService)
    {
    }

    /**
     * Attach one or more skills to the freelancer profile.
     */
    public function store(StoreSkillRequest $request, FreelancerProfile $freelancerProfile)
    {
        if (!$freelancerProfile) {
            return ApiResponse::notFound('Profile not found.');
        }

        $profile = $this->freelancersService->storeSkills($freelancerProfile, $request->validated('skills'));

        return ApiResponse::success(new FreelancerProfileResource($profile), 'Skills added successfully.', 201);
    }

    /**
     * Update the experience_years of a single attached skill.
     */
    public function update(UpdateSkillRequest $request, FreelancerProfile $freelancerProfile, Skill $skill)
    {
        if (!$freelancerProfile) {
            return ApiResponse::notFound('Profile not found.');
        }

        $profile = $this->freelancersService->updateSkill($freelancerProfile, $skill, $request->validated());

        if (!$profile) {
            return ApiResponse::notFound('This skill is not attached to the profile.');
        }

        return ApiResponse::success(new FreelancerProfileResource($profile), 'Skill updated successfully.');
    }

    /**
     * Detach a skill from the freelancer profile.
     */
    public function destroy(FreelancerProfile $freelancerProfile, Skill $skill)
    {
        if (!$freelancerProfile) {
            return ApiResponse::notFound('Profile not found.');
        }

        $result = $this->freelancersService->destroySkill($freelancerProfile, $skill);

        if (!$result) {
            return ApiResponse::notFound('This skill is not attached to the profile.');
        }

        return ApiResponse::success(null, 'Skill removed successfully.');
    }
}
