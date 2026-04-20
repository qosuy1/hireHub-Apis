<?php

namespace Database\Seeders;

use App\Enums\UserTypeEnum;
use App\Models\Freelancer;
use App\Models\FreelancerProfile;
use App\Models\Project;
use App\Models\Skill;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Step 1: Seed reference data
        $this->call([
            CountrySeeder::class,
            CitySeeder::class,
            SkillSeeder::class,
            TagSeeder::class,
        ]);

        $this->command->info('Reference data seeded successfully!');

        // Step 2: Create users
        $this->createUsers();

        // Step 3: Create freelancer profiles
        $this->createFreelancers();

        // Step 4: Create projects
        $this->createProjects();

        // Step 5: Create offers
        $this->call(OfferSeeder::class);

        // Step 6: Create reviews
        $this->call(ReviewSeeder::class);

        // Step 7: Create attachments
        $this->call(AttachmentSeeder::class);

        // Step 8: Attach skills to freelancer profiles
        $this->attachSkills();

        // Step 9: Attach tags to projects
        $this->attachTags();

        $this->command->info('Database seeding completed successfully! ✅✅');
    }

    /**
     * Create users with different roles.
     */
    private function createUsers(): void
    {
        $this->command->info('Creating users...');

        // Create admin user
        User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@hirehub.com',
            'username' => 'admin',
            'type' => UserTypeEnum::ADMIN->value,
            'is_active' => true,
            'verified_at' => now(),
        ]);

        // Create 20 freelancer users (active & verified)
        $freelancers = User::factory()
            ->count(20)
            ->freelancer()
            ->active()
            ->verified()
            ->create();
        // Create 10 freelancer users (active & unverified)
        $freelancers = User::factory()
            ->count(20)
            ->freelancer()
            ->active()
            ->unverified()
            ->create();

        // Create 15 client users
        $clients = User::factory()
            ->count(15)
            ->client()
            ->active()
            ->create();

        // Create 10 mixed users with random states
        User::factory()->count(10)->create();

        $this->command->info("Users Created Successfully!");
    }

    /**
     * Create freelancer profiles for freelancer users.
     */
    private function createFreelancers(): void
    {
        $this->command->info('Creating freelancer profiles...');

        $freelancers = User::where('type', UserTypeEnum::FREELANCER->value)->get();

        $freelancers->each(function ($user) {
            FreelancerProfile::factory()
                ->for($user)
                ->create([
                    'user_id' => $user->id,
                ]);
        });

        $this->command->info("Created {$freelancers->count()} freelancer profiles");
    }

    /**
     * Create projects with different statuses.
     */
    private function createProjects(): void
    {
        $this->command->info('Creating projects...');

        $clients = User::where('type', UserTypeEnum::CLIENT->value)->get();

        if ($clients->isEmpty()) {
            $this->command->warn('No clients found. Skipping project creation.');
            return;
        }

        // Create 10 open projects
        Project::factory()
            ->count(10)
            ->open()
            ->create(['user_id' => $clients->random()->id]);

        // Create 5 in-progress projects
        Project::factory()
            ->count(5)
            ->inProgress()
            ->create(['user_id' => $clients->random()->id]);

        // Create 5 closed projects
        Project::factory()
            ->count(5)
            ->closed()
            ->create(['user_id' => $clients->random()->id]);

        $this->command->info('Created: 10 open, 5 in-progress, 5 closed projects');
    }

    /**
     * Attach skills to freelancer profiles.
     */
    private function attachSkills(): void
    {
        $this->command->info('Attaching skills to freelancer profiles...');

        $freelancerProfiles = FreelancerProfile::all();
        $skills = Skill::all();

        if ($skills->isEmpty()) {
            $this->command->warn('No skills found. Skipping skill attachment.');
            return;
        }

        $freelancerProfiles->each(function ($profile) use ($skills) {
            // Attach 3-8 random skills to each freelancer
            $skillsToAttach = $skills->random(rand(3, 8));

            foreach ($skillsToAttach as $skill) {
                $profile->skills()->attach($skill->id, [
                    'experience_years' => rand(1, 10),
                ]);
            }
        });

        $this->command->info('Skills attached to freelancer profiles');
    }

    /**
     * Attach tags to projects.
     */
    private function attachTags(): void
    {
        $this->command->info('Attaching tags to projects...');

        $projects = Project::all();
        $tags = Tag::all();

        if ($tags->isEmpty()) {
            $this->command->warn('No tags found. Skipping tag attachment.');
            return;
        }

        $projects->each(function ($project) use ($tags) {
            // Attach 2-5 random tags to each project
            $tagsToAttach = $tags->random(rand(2, 5));
            $project->tags()->attach($tagsToAttach);
        });

        $this->command->info('✓ Tags attached to projects');
    }
}
