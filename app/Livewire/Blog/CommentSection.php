<?php

namespace App\Livewire\Blog;

use App\Models\Blog;
use App\Models\BlogComment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use Livewire\Component;

class CommentSection extends Component
{
    public Blog $blog;

    #[Rule('required', message: 'You have to actually write a comment..')]
    #[Rule('max:200', message: "You're writing comment, not a book.. to long")]
    public string $comment = '';

    public function mount(Blog $blog): void
    {
        $this->blog = $blog;
    }

    public function addComment(): void
    {
        $this->validate();

        $comment = new BlogComment([
            'comment' => $this->comment,
            'author_id' => Auth::id()
        ]);
        $this->blog->comments()->save($comment);

        $this->reset('comment');
    }

    public function render()
    {
        $comments = $this->blog->comments()
            ->with('author')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.blog.comment-section', compact('comments'));
    }
}
