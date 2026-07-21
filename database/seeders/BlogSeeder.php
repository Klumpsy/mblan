<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('role', 'admin')->first() ?? User::first();

        $posts = [
            [
                'title' => 'Forged in the Barn - MBLAN26 is coming',
                'preview_text' => 'The Barn returns for a third edition. Here is everything we can tell you (and a few things we can\'t).',
                'content' => '<p>It\'s official: <strong>MBLAN26 - The Barn III</strong> is happening. Once again the barn becomes a digital smithy where high tech meets old wood, and where legends are hammered out over a single unforgettable weekend.</p><p>Expect faster fibre, bigger screens and the tournaments you\'ve been asking for. The location? Still a secret. The date? Coming soon. The vibe? Forged in the barn.</p><p><em>This isn\'t just a LAN party. This is MBLAN.</em></p>',
                'is_featured' => true,
            ],
            [
                'title' => 'The tournament line-up takes shape',
                'preview_text' => 'From Warcraft III to Trackmania - a first look at the games that will define The Barn III.',
                'content' => '<p>We\'re locking in the roster. Classics return alongside a few surprises, and the finals-day bracket is going to be brutal in the best way. Sharpen your aim and warm up those APMs.</p><p>Full schedule drops closer to the event - keep an eye on the Editions page.</p>',
                'is_featured' => false,
            ],
            [
                'title' => 'Barn upgrades: power, fibre and a lot of cable',
                'preview_text' => 'Behind the scenes of turning a wooden barn into a tournament-grade LAN venue.',
                'content' => '<p>Running a high-tech event in a century-old barn takes preparation. This year we\'ve upgraded the power distribution, pulled fresh fibre and pre-labelled every single cable (you\'re welcome).</p><p>The result: rock-solid pings and zero brownouts when the finals heat up.</p>',
                'is_featured' => false,
            ],
        ];

        foreach ($posts as $i => $post) {
            Blog::updateOrCreate(
                ['slug' => Str::slug($post['title'])],
                array_merge($post, [
                    'author_id' => $author?->id,
                    'slug' => Str::slug($post['title']),
                    'published' => true,
                    'published_at' => now()->subDays(count($posts) - $i),
                    'image' => null,
                ])
            );
        }
    }
}
