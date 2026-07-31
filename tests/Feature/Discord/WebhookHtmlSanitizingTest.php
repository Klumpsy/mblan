<?php

use App\Models\News;
use App\Models\Tournament;
use App\Services\DiscordWebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'discord.webhook_url' => 'https://discord.com/api/webhooks/MAIN/token',
        'discord.news_webhook_url' => null,
    ]);
    Http::fake(['discord.com/*' => Http::response('', 204)]);
});

function sentDescription(): string
{
    $description = null;
    Http::assertSent(function ($request) use (&$description) {
        $description = $request->data()['embeds'][0]['description'] ?? '';

        return true;
    });

    return $description;
}

test('HTML in a news preview is stripped from the Discord embed', function () {
    $news = News::factory()->create([
        'preview_text' => '<p>Eerste alinea.</p><p>Tweede alinea.</p>',
    ]);

    app(DiscordWebhookService::class)->announceNews($news);

    $description = sentDescription();
    expect($description)->not->toContain('<p>')
        ->and($description)->not->toContain('</p>')
        ->and($description)->toContain("Eerste alinea.\nTweede alinea.");
});

test('HTML entities are decoded and <br> becomes a newline', function () {
    $news = News::factory()->create([
        'preview_text' => 'Vrijdag &amp; zaterdag<br>Tot dan!',
    ]);

    app(DiscordWebhookService::class)->announceNews($news);

    expect(sentDescription())->toBe("Vrijdag & zaterdag\nTot dan!");
});

test('HTML in embed field values is stripped too', function () {
    $tournament = Tournament::factory()->create([
        'description' => '<p>Regels: <strong>fair play</strong>.</p>',
    ]);

    app(DiscordWebhookService::class)->announceTournament($tournament);

    Http::assertSent(function ($request) {
        $fields = $request->data()['embeds'][0]['fields'] ?? [];
        $toelichting = collect($fields)->firstWhere('name', 'Toelichting');

        return $toelichting
            && ! str_contains($toelichting['value'], '<')
            && str_contains($toelichting['value'], 'Regels: fair play.');
    });
});
