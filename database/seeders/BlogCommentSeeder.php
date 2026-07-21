<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\BlogComment;
use App\Models\User;
use Illuminate\Database\Seeder;

class BlogCommentSeeder extends Seeder
{
    public function run(): void
    {
        $lines = [
            'Counting down the days already.',
            'The barn setup last year was unreal. Cannot wait.',
            'Please tell me AoE is on the schedule again.',
            'Who is bringing the good speakers this time?',
            'Location secret again? Classic MBLAN.',
            'My APM is ready.',
        ];

        Blog::query()->get()->each(function (Blog $blog) use ($lines) {
            $authors = User::inRandomOrder()->take(random_int(2, 4))->get();
            foreach ($authors as $author) {
                BlogComment::create([
                    'blog_id' => $blog->id,
                    'author_id' => $author->id,
                    'comment' => $lines[array_rand($lines)],
                ]);
            }
        });
    }
}
