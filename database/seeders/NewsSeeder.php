<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\User;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::query()->first() ?? User::factory()->create();

        $items = [
            [
                'title' => 'MBLAN26 is aangekondigd',
                'preview_text' => 'Zet de datum vast: de nieuwe editie komt eraan.',
                'content' => '<p>Het is zover: <strong>MBLAN26</strong> staat op de kalender. Hou het speelschema in de gaten voor de eerste toernooien.</p>',
            ],
            [
                'title' => 'Speelschema staat online',
                'preview_text' => 'Bekijk welke games en toernooien er dit jaar op het programma staan.',
                'content' => '<p>Het volledige speelschema is nu te bekijken. Stem op je favoriete games en meld je aan voor de toernooien.</p>',
            ],
        ];

        // Seeding must never fire the publish-to-Discord observer.
        News::withoutEvents(function () use ($items, $author) {
            foreach ($items as $i => $data) {
                News::updateOrCreate(
                    ['slug' => \Illuminate\Support\Str::slug($data['title'])],
                    array_merge($data, [
                        'author_id' => $author->id,
                        'published' => true,
                        'published_at' => now()->subDays(count($items) - $i),
                    ]),
                );
            }
        });
    }
}
