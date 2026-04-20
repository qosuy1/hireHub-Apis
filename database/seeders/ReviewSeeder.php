<?php

namespace Database\Seeders;
 
use App\Models\FreelancerProfile;
use App\Models\Project;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $freelancerProfiles = FreelancerProfile::all();
        $projects = Project::where('status', 'closed')->get();
        $users = User::where('type', 'client')->get();

        // Create reviews for freelancer profiles (polymorphic)
        $freelancerProfiles->each(function ($profile) use ($users) {
            // Create 1-5 reviews per freelancer
            $reviewsCount = rand(1, 5);

            for ($i = 0; $i < $reviewsCount; $i++) {
                $user = $users->random();

                Review::factory()
                    ->for($user)
                    ->for($profile, 'reviewable')
                    ->positive() // Mostly positive reviews
                    ->create();
            }

            // Update the average rating
            $averageRating = Review::where('reviewable_type', FreelancerProfile::class)
                ->where('reviewable_id', $profile->id)
                ->avg('rating');

            if ($averageRating) {
                $profile->update(['average_rating' => round($averageRating, 1)]);
            }
        });

        // Create reviews for projects (polymorphic)
        if ($projects->isNotEmpty() && $users->isNotEmpty()) {
            $projects->each(function ($project) use ($users) {
                if (rand(0, 1)) { // 50% chance
                    $user = $users->random();

                    Review::factory()
                        ->for($user)
                        ->for($project, 'reviewable')
                        ->positive()
                        ->create();
                }
            });
        }
    }
}
