<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Edition;
use App\Support\CurrentEdition;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(CurrentEdition $current): View
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
        // Follow the edition the user is currently viewing (navbar switcher).
        $latestEdition = $current->get();

        return view('dashboard.index', compact('user', 'latestBlog', 'latestEdition'));
    }
}
