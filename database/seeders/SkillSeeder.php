<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skills = [
            'PHP',
            'JavaScript',
            'TypeScript',
            'Python',
            'Java',
            'C#',
            'Ruby',
            'Go',
            'Rust',
            'Swift',
            'Kotlin',
            'Dart',
            'React',
            'Vue.js',
            'Angular',
            'Next.js',
            'Nuxt.js',
            'Node.js',
            'Laravel',
            'Django',
            'Flask',
            'Express.js',
            'Spring Boot',
            'Flutter',
            'React Native',
            'AWS',
            'Azure',
            'Google Cloud',
            'Docker',
            'Kubernetes',
            'Jenkins',
            'Git',
            'MySQL',
            'PostgreSQL',
            'MongoDB',
            'Redis',
            'Elasticsearch',
            'GraphQL',
            'REST API',
            'HTML',
            'CSS',
            'SASS',
            'Tailwind CSS',
            'Bootstrap',
            'Photoshop',
            'Illustrator',
            'Figma',
            'Adobe XD',
            'UI/UX Design',
            'Responsive Design',
        ];

        foreach ($skills as $skill) {
            Skill::firstOrCreate(
                ['name' => $skill],
                ['name' => $skill]
            );
        }
    }
}
