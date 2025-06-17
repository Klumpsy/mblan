<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $latestBlog = Blog::latest()->first();

        return view('dashboard.index', compact('user', 'latestBlog'));
    }
}
