<?php

namespace App\Policies;

use App\Models\BlogComment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BlogCommentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BlogComment $blogComment): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BlogComment $blogComment): bool
    {
        return $user === $blogComment->author();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BlogComment $blogComment): bool
    {
        return $user === $blogComment->author() || $user->role === 'admin';
    }
}
