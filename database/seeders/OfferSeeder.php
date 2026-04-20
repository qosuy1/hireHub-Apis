<?php

namespace Database\Seeders;

use App\Models\Freelancer;
use App\Models\Offer;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class OfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = Project::where('status', 'open')->get();
        $freelancers = User::where('type', 'freelancer')
            ->where('is_active', true)
            ->whereNotNull('verified_at')
            ->get();

        if ($projects->isEmpty() || $freelancers->isEmpty()) {
            return;
        }

        // Create 3-5 offers per open project
        $projects->each(function ($project) use ($freelancers) {
            $offersCount = rand(3, 5);
            $selectedFreelancers = $freelancers->random(min($offersCount, $freelancers->count()));

            foreach ($selectedFreelancers as $freelancer) {
                // Check if freelancer already made an offer
                $existingOffer = Offer::where('project_id', $project->id)
                    ->where('freelancer_id', $freelancer->id)
                    ->first();

                if (!$existingOffer) {
                    Offer::factory()
                        ->for($project)
                        ->for($freelancer, 'freelancer')
                        ->create([
                            'amount' => fake()->randomFloat(2, $project->budget * 0.8, $project->budget * 1.2),
                            'delevery_time' => rand(7, 60),
                        ]);
                }
            }
        });
    }
}
