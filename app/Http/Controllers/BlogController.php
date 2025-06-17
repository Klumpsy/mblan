<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::all()->withRelationshipAutoloading();
        return view('blog.index', compact('blogs'));
    }

    public function show(string $slug): View
    {
        $blog = Blog::where('slug', $slug)
            ->withCount('comments')
            ->firstOrFail()
            ->withRelationshipAutoloading();

        return view('blog.show', [
            'blog' => $blog,
        ]);
    }
}
