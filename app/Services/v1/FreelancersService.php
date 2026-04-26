<?php
namespace App\Services\v1;

use App\Enums\AvailabilityStatusEnum;
use App\Helper\V1\ApiResponse;
use App\Models\FreelancerProfile;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FreelancersService
{

    public function getAllFreelancers($filters = [])
    {
        $query = User::activeVerifiedFreelancers()
            ->with([
                'freelancerProfile' => function ($query) {
                    $query->with(['skills'])
                        ->withCount(['offers', 'reviews']);
                }
            ]);


        if ($filters['available_now'] ?? false)
            $query->whereHas('freelancerProfile', function ($q) {
                $q->avaliability(AvailabilityStatusEnum::AVAILABLE->value);
            });
        if ($filters['best_rated'] ?? false) {
            $query->orderBy(
                FreelancerProfile::select('average_rating')->whereColumn('user_id', 'users.id')->limit(1),
                'desc'
            );
        }

        $freelancers = $query->paginate(15);
        return $freelancers;
    }

    public function getFreelancerProfile($profile_id)
    {
        $profile = FreelancerProfile::withCount('reviews', 'offers', 'acceptedProjects')
            ->with(['offers', 'skills', 'user', 'user.city', 'user.country', 'acceptedProjects'])
            ->where('id', $profile_id)
            ->first();

        if (!$profile)
            return false;

        return $profile;
        /**
        return
            [
                'id' => $profile->id,
                'user_id' => $profile->user?->id,
                'name' => $profile->user?->full_name,
                'email' => $profile->user?->email,
                'Location' => $profile->user?->country?->name . " , " . $profile->user?->city?->name,
                'phone' => ($profile->user?->country?->phone_code ?? '') . $profile->phone,

                'bio' => $profile->bio,
                'hourly_rate' => $profile->hourly_rate,
                'avatar' => $profile->avatar_url,
                'portfolio_links' => $profile->portfolio_links,
                // 'skills_summary' => $profile->skills_summary,
                'skills' => $skills,
                'availability' => $profile->availability_status,
                'rating' => $profile->display_rating,

                'offers' => $profile->offers
            ]
        ;
         */

    }

    public function storeFreelancerProfile(array $data)
    {
        $freelancerProfile = new FreelancerProfile();
        $freelancerProfile->user_id = auth()->user()->id;
        $freelancerProfile->bio = $data['bio'];
        $freelancerProfile->phone = $data['phone'];
        $freelancerProfile->hourly_rate = $data['hourly_rate'];
        $freelancerProfile->availability_status = $data['availability_status'];
        $freelancerProfile->portfolio_links = $data['portfolio_links'] ?? [];

        if (isset($data['avatar'])) {
            // store() returns the saved path 
            $freelancerProfile->avatar = $data['avatar']->store('freelancer_profiles/avatars', 'public');
        }


        $freelancerProfile->save();

        if (!empty($data['skills'])) {
            $pivotData = collect($data['skills'])->mapWithKeys(
                fn($skill) => [$skill['id'] => ['experience_years' => $skill['experience_years']]]
            )->all();
            $freelancerProfile->skills()->attach($pivotData);
        }

        return $freelancerProfile->load('user', 'skills');
    }

    public function updateFreelancerProfile(FreelancerProfile $profile, array $data)
    {
        // Only pass scalar fillable fields
        $profile->update(array_filter([
            'bio' => $data['bio'] ?? null,
            'phone' => $data['phone'] ?? null,
            'hourly_rate' => $data['hourly_rate'] ?? null,
            'availability_status' => $data['availability_status'] ?? null,
            'portfolio_links' => $data['portfolio_links'] ?? null,
        ], fn($v) => !is_null($v)));

        if (isset($data['avatar'])) {
            // Delete old avatar file before storing the new one.
            if ($profile->avatar && Storage::disk('public')->exists($profile->avatar)) {
                Storage::disk('public')->delete($profile->avatar);
            }
            $profile->avatar = $data['avatar']->store('freelancer_profiles/avatars', 'public');
            $profile->save();
        }

        if (isset($data['skills']) && !empty($data['skills'])) {
            $pivotData = collect($data['skills'])->mapWithKeys(
                fn($skill) => [$skill['id'] => ['experience_years' => $skill['experience_years']]]
            )->all();
            // sync() detaches+ attaches in one query 
            $profile->skills()->sync($pivotData);
        }

        return $profile->load('user', 'skills');
    }

    public function deleteFreelancerProfile(User $user, FreelancerProfile $freelancerProfile)
    {
        if ($user->id !== $freelancerProfile->user_id)
            return false;
        if (Storage::disk('public')->exists($freelancerProfile->avatar))
            Storage::disk('public')->delete($freelancerProfile->avatar);
        $freelancerProfile->skills()->detach();
        $freelancerProfile->delete();
        return true;
    }


    // Skills
    public function storeSkills(FreelancerProfile $profile, array $data)
    {
        $pivotData = collect($data)->mapWithKeys(
            fn($skill)
            => [$skill['id'] => ['experience_years' => $skill['experience_years']]]
        )->all();
        $profile->skills()->attach($pivotData);
        return $profile->load('skills');
    }
    public function updateSkill(FreelancerProfile $profile, Skill $skill, array $data)
    {
        if (!$profile->skills()->where('id', $skill->id)->exists()) {
            return false;
        }
        $profile->skills()->updateExistingPivot($skill->id, ['experience_years' => $data['experience_years']]);
        return $profile->load('skills');
    }

    public function destroySkill(FreelancerProfile $profile, Skill $skill): bool
    {
        if (!$profile->skills()->where('id', $skill->id)->exists()) {
            return false;
        }
        $profile->skills()->detach($skill->id);
        return true;
    }
}
