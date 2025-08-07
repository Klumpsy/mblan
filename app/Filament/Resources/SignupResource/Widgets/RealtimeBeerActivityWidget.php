<?php

namespace App\Filament\Resources\SignupResource\Widgets;

use Filament\Widgets\Widget;
use App\Models\Signup;
use App\Models\Edition;

class RealtimeBeerActivityWidget extends Widget
{
    protected static ?int $sort = 5;
    protected static ?string $pollingInterval = '5s';
    protected int | string | array $columnSpan = 'full';

    protected static string $view = 'filament.widgets.realtime-beer-activity';

    protected function getViewData(): array
    {
        $currentEdition = Edition::where('year', now()->year)->first();

        if (!$currentEdition) {
            return ['recentActivity' => collect(), 'edition' => null];
        }

        // Get recent beer activity (last 10 beers)
        $recentActivity = Signup::with('user')
            ->where('edition_id', $currentEdition->id)
            ->where('confirmed', true)
            ->where('beer_count', '>', 0)
            ->whereNotNull('last_beer_at')
            ->orderBy('last_beer_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($signup) {
                return [
                    'user_name' => $signup->user->name,
                    'beer_count' => $signup->beer_count,
                    'last_beer_at' => $signup->last_beer_at,
                    'time_ago' => $signup->last_beer_at->diffForHumans(),
                    'avatar' => $signup->user->profile_photo_path
                        ? asset('storage/' . $signup->user->profile_photo_path)
                        : 'https://ui-avatars.com/api/?name=' . urlencode($signup->user->name) . '&color=7F9CF5&background=EBF4FF',
                ];
            });

        return [
            'recentActivity' => $recentActivity,
            'edition' => $currentEdition,
        ];
    }
}
