<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::where('published', true)
            ->with(['author', 'tags'])
            ->orderBy('published_at', 'desc')
            ->get();

        return view('blog.index', compact('blogs'));
    }

    public function show(Blog $blog): View
    {
        $blog->loadCount('comments')->withRelationshipAutoloading();

        return view('blog.show', [
            'blog' => $blog,
        ]);
    }
}
