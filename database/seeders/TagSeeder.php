<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            'Web Development',
            'Mobile Development',
            'UI/UX Design',
            'Graphic Design',
            'Backend Development',
            'Frontend Development',
            'Full Stack Development',
            'DevOps',
            'Machine Learning',
            'Artificial Intelligence',
            'Data Science',
            'Database Administration',
            'Cloud Computing',
            'Cybersecurity',
            'Blockchain',
            'E-commerce',
            'WordPress',
            'SEO',
            'Content Writing',
            'Copywriting',
            'Video Editing',
            'Animation',
            '3D Modeling',
            'Illustration',
            'Logo Design',
            'Branding',
            'Marketing',
            'Social Media Marketing',
            'Email Marketing',
            'Project Management',
        ];

        foreach ($tags as $tag) {
            Tag::firstOrCreate(
                ['name' => $tag],
                ['name' => $tag]
            );
        }
    }
}
