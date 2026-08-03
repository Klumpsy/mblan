<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Blog\CommentSection;
use App\Models\Blog;
use App\Models\BlogComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CommentSectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_successfully(): void
    {
        Livewire::test(CommentSection::class)
            ->assertStatus(200);
    }

    public function test_comment_section_renders_with_correct_comments(): void
    {
        $user = User::factory()->create();

        $blog = Blog::factory()->create([
            'author_id' => $user->id,
        ]);

        $comment = BlogComment::factory()->create([
            'author_id' => $user->id,
            'blog_id' => $blog->id,
        ]);

        Livewire::actingAs($user)
            ->test(CommentSection::class, ['blog' => $blog])
            ->assertSee($comment->comment)
            ->assertSee('author');
    }

    public function test_comment_section_renders_with_author_title_if_blog_author_is_comment_author(): void
    {
        $user = User::factory()->create();

        $blog = Blog::factory()->create([
            'author_id' => $user->id,
        ]);

        $comment = BlogComment::factory()->create([
            'author_id' => $user->id,
            'blog_id' => $blog->id,
        ]);

        Livewire::actingAs($user)
            ->test(CommentSection::class, ['blog' => $blog])
            ->assertSee($comment->comment)
            ->assertSee('author');
    }

    public function test_comment_section_renders_without_author_title_if_blog_author_is_not_comment_author(): void
    {
        $user = User::factory()->create();
        $userTwo = User::factory()->create();

        $blog = Blog::factory()->create([
            'author_id' => $user->id,
        ]);

        $comment = BlogComment::factory()->create([
            'author_id' => $userTwo->id,
            'blog_id' => $blog->id,
        ]);

        Livewire::actingAs($user)
            ->test(CommentSection::class, ['blog' => $blog])
            ->assertSee($comment->comment)
            ->assertDontSeeText('author');
    }


    public function test_user_can_add_comment_to_blog(): void
    {
        $user = User::factory()->create();
        $blog = Blog::factory()->create(['author_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(CommentSection::class, ['blog' => $blog])
            ->set('comment', 'This is a valid comment.')
            ->call('addComment')
            ->assertHasNoErrors()
            ->assertSet('comment', '');

        $this->assertDatabaseHas('blog_comments', [
            'comment' => 'This is a valid comment.',
            'blog_id' => $blog->id,
            'author_id' => $user->id,
        ]);
    }

    public function test_comment_is_required(): void
    {
        $user = User::factory()->create();
        $blog = Blog::factory()->create(['author_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(CommentSection::class, ['blog' => $blog])
            ->set('comment', '')
            ->call('addComment')
            ->assertHasErrors(['comment' => 'required']);
    }

    public function test_comment_cannot_exceed_200_chars(): void
    {
        $user = User::factory()->create();
        $blog = Blog::factory()->create(['author_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(CommentSection::class, ['blog' => $blog])
            ->set('comment', str_repeat('a', 201))
            ->call('addComment')
            ->assertHasErrors(['comment' => 'max:200']);
    }
}
