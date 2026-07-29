<?php

use App\Models\Achievement;
use App\Models\User;
use App\Services\AchievementEvaluator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('grantMany awards the achievement to every new user and skips those who had it', function () {
    config(['discord.webhook_url' => 'https://discord.com/api/webhooks/X/Y', 'discord.news_webhook_url' => 'https://discord.com/api/webhooks/X/Y']);
    Http::fake(['discord.com/*' => Http::response('', 204)]);

    $achievement = Achievement::where('slug', 'editie-2024')->first();
    $a = User::factory()->create();
    $b = User::factory()->create();
    $c = User::factory()->create();

    $evaluator = app(AchievementEvaluator::class);
    $evaluator->grant($c, $achievement); // c already has it (1 message)

    $granted = $evaluator->grantMany([$a, $b, $c], $achievement);

    expect(collect($granted)->pluck('id')->all())->toEqualCanonicalizing([$a->id, $b->id]);
    expect($a->achievements()->wherePivotNotNull('achieved_at')->where('achievements.id', $achievement->id)->exists())->toBeTrue();
    expect($b->achievements()->wherePivotNotNull('achieved_at')->where('achievements.id', $achievement->id)->exists())->toBeTrue();
});

test('a bulk grant sends exactly one combined Discord message with the names', function () {
    config(['discord.webhook_url' => 'https://discord.com/api/webhooks/X/Y', 'discord.news_webhook_url' => 'https://discord.com/api/webhooks/X/Y']);
    Http::fake(['discord.com/*' => Http::response('', 204)]);

    $achievement = Achievement::where('slug', 'editie-2025')->first();
    $piet = User::factory()->create(['name' => 'Piet']);
    $klaas = User::factory()->create(['name' => 'Klaas']);

    app(AchievementEvaluator::class)->grantMany([$piet, $klaas], $achievement);

    Http::assertSentCount(1);
    Http::assertSent(function ($request) {
        $body = json_encode($request->data());

        return str_contains($body, 'Piet') && str_contains($body, 'Klaas');
    });
});

test('a bulk grant where everyone already has it sends no message', function () {
    config(['discord.webhook_url' => 'https://discord.com/api/webhooks/X/Y', 'discord.news_webhook_url' => 'https://discord.com/api/webhooks/X/Y']);
    Http::fake(['discord.com/*' => Http::response('', 204)]);

    $achievement = Achievement::where('slug', 'editie-2024')->first();
    $u = User::factory()->create();
    app(AchievementEvaluator::class)->grant($u, $achievement, notify: false);

    app(AchievementEvaluator::class)->grantMany([$u], $achievement);

    Http::assertNothingSent();
});
