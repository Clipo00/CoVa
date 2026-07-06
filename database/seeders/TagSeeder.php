<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            'PHP', 'Laravel', 'JavaScript', 'TypeScript', 'React',
            'Vue', 'Node.js', 'Python', 'Go', 'Docker',
            'PostgreSQL', 'MySQL', 'Redis', 'Tailwind CSS', 'Inertia',
            'Livewire', 'Alpine.js', 'REST API', 'GraphQL', 'Next.js',
        ];

        foreach ($tags as $name) {
            Tag::findOrCreate($name);
        }
    }
}
