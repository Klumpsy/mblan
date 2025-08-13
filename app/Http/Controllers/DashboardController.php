<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Edition;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $latestBlog = Blog::where('published', true)
            ->where('is_featured', true)
            ->first();

        if (!$latestBlog) {
            $latestBlog = Blog::where('published', true)
                ->orderBy('published_at', 'desc')
                ->first();
        }

        $latestEdition = Edition::latest()->select('slug', 'name')->first();

        return view('dashboard.index', compact('user', 'latestBlog', 'latestEdition'));
    }
}
