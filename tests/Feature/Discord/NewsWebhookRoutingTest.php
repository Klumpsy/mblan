<?php

use App\Models\News;
use App\Models\User;
use App\Services\DiscordWebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

const MAIN_HOOK = 'https://discord.com/api/webhooks/MAIN/token';
const NEWS_HOOK = 'https://discord.com/api/webhooks/NEWS/token';

test('news announcements go to the news webhook when one is configured', function () {
    config(['discord.webhook_url' => MAIN_HOOK, 'discord.news_webhook_url' => NEWS_HOOK]);
    Http::fake(['discord.com/*' => Http::response('', 204)]);

    $author = User::factory()->create();
    $news = News::factory()->create(['author_id' => $author->id, 'title' => 'Groot nieuws']);

    app(DiscordWebhookService::class)->announceNews($news);

    Http::assertSent(fn ($request) => $request->url() === NEWS_HOOK);
    Http::assertNotSent(fn ($request) => $request->url() === MAIN_HOOK);
});

test('news falls back to the main webhook when no news webhook is set', function () {
    config(['discord.webhook_url' => MAIN_HOOK, 'discord.news_webhook_url' => MAIN_HOOK]);
    Http::fake(['discord.com/*' => Http::response('', 204)]);

    $news = News::factory()->create(['title' => 'Fallback nieuws']);

    app(DiscordWebhookService::class)->announceNews($news);

    Http::assertSent(fn ($request) => $request->url() === MAIN_HOOK);
});

test('non-news announcements still use the main webhook', function () {
    config(['discord.webhook_url' => MAIN_HOOK, 'discord.news_webhook_url' => NEWS_HOOK]);
    Http::fake(['discord.com/*' => Http::response('', 204)]);

    app(DiscordWebhookService::class)->sendDailyDigest(now(), ['13:00 - Iets']);

    Http::assertSent(fn ($request) => $request->url() === MAIN_HOOK);
    Http::assertNotSent(fn ($request) => $request->url() === NEWS_HOOK);
});
