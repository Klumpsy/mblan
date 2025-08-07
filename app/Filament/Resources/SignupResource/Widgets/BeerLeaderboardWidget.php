<?php

namespace App\Filament\Resources\SignupResource\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Signup;
use App\Models\Edition;
use Illuminate\Database\Eloquent\Builder;

class BeerLeaderboardWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected static ?string $pollingInterval = '30s';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->defaultSort('beer_count', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('rank')
                    ->label('Rank')
                    ->getStateUsing(function ($record, $rowLoop) {
                        $rank = $rowLoop->index + 1;
                        return match ($rank) {
                            1 => '🥇 1st',
                            2 => '🥈 2nd',
                            3 => '🥉 3rd',
                            default => "#{$rank}"
                        };
                    })
                    ->sortable(false)
                    ->weight('bold')
                    ->size('lg'),

                Tables\Columns\ImageColumn::make('user.profile_photo_path')
                    ->label('Avatar')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl(function ($record) {
                        return 'https://ui-avatars.com/api/?name=' . urlencode($record->user->name) . '&color=7F9CF5&background=EBF4FF';
                    }),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Name')
                    ->weight('semibold')
                    ->size('base')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('beer_count')
                    ->label('🍺 Beers')
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state >= 50 => 'danger',
                        $state >= 25 => 'warning',
                        $state >= 10 => 'success',
                        $state >= 1 => 'info',
                        default => 'gray'
                    })
                    ->size('lg')
                    ->weight('bold')
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_beer_at')
                    ->label('Last Beer')
                    ->dateTime('M j, g:i A')
                    ->since()
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.discord_id')
                    ->label('Discord Connected')
                    ->badge()
                    ->getStateUsing(fn($record) => $record->user->discord_id ? 'Connected' : 'Not Connected')
                    ->color(fn($state) => $state === 'Connected' ? 'success' : 'gray')
                    ->icon(fn($state) => $state === 'Connected' ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'),
            ])
            ->heading('🍺 Beer Leaderboard')
            ->description('Top beer drinkers for the current edition')
            ->emptyStateHeading('No beers consumed yet!')
            ->emptyStateDescription('Be the first to add a beer using the Discord bot!')
            ->emptyStateIcon('heroicon-o-face-frown')
            ->striped()
            ->paginated([10, 25, 50]);
    }

    protected function getTableQuery(): Builder
    {
        $currentEdition = Edition::where('year', now()->year)->first();

        if (!$currentEdition) {
            return Signup::query()->whereRaw('1 = 0'); // Return empty query
        }

        return Signup::with(['user'])
            ->where('edition_id', $currentEdition->id)
            ->where('confirmed', true)
            ->where('beer_count', '>', 0)
            ->orderBy('beer_count', 'desc')
            ->orderBy('last_beer_at', 'asc'); // Tie breaker
    }
}
