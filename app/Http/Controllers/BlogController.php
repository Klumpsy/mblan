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
        return view('blog.show', [
            'blog' => Blog::where('slug', $slug)->firstOrFail()
        ]);
    }
}
