<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\News;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NewsController extends Controller
{
    public function index(): View
    {
        $edition = Edition::current();

        $items = News::query()
            ->published()
            ->when($edition, fn ($q) => $q->forEdition($edition))
            ->with('author')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(9);

        return view('news.index', ['items' => $items]);
    }

    public function show(News $news): View
    {
        if (! $news->published) {
            throw new NotFoundHttpException();
        }

        $news->load('author');

        return view('news.show', ['news' => $news]);
    }
}
