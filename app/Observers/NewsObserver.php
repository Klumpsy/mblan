<?php

namespace App\Observers;

use App\Models\News;
use App\Services\DiscordWebhookService;

class NewsObserver
{
    public function __construct(
        private DiscordWebhookService $discord
    ) {}

    public function created(News $news): void
    {
        if ($news->published) {
            $this->discord->announceNews($news);
        }
    }

    public function updated(News $news): void
    {
        // Only announce on the transition into "published", so editing an
        // already-published item doesn't repost it.
        if ($news->wasChanged('published') && $news->published) {
            $this->discord->announceNews($news);
        }
    }
}
