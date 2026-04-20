<?php

namespace Database\Seeders;

use App\Models\Attachment;
use App\Models\Project;
use App\Models\Offer;
use Illuminate\Database\Seeder;

class AttachmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = Project::all();
        $offers = Offer::all();

        // Create attachments for projects
        $projects->each(function ($project) {
            // 30% chance to have attachments
            if (rand(0, 100) < 30) {
                $attachmentsCount = rand(1, 3);
                
                for ($i = 0; $i < $attachmentsCount; $i++) {
                    Attachment::factory()
                        ->for($project, 'attachable')
                        ->create();
                }
            }
        });

        // Create attachments for offers
        $offers->each(function ($offer) {
            // 20% chance to have attachments
            if (rand(0, 100) < 20) {
                Attachment::factory()
                    ->for($offer, 'attachable')
                    ->document()
                    ->create();
            }
        });
    }
}
