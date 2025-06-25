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
        $latestBlog = Blog::latest()->first();
        $latestEdition = Edition::latest()->select('slug', 'name')->first();

        return view('dashboard.index', compact('user', 'latestBlog', 'latestEdition'));
    }
}
