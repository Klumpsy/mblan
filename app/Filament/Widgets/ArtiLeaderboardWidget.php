<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * Fastest finishers of the Arti barn mini-game: the players who reached the
 * barn, ranked by time.
 */
class ArtiLeaderboardWidget extends TableWidget
{
    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Arti-spel toppers')
            ->query(
                User::query()
                    ->where('barn_completed', true)
                    ->whereNotNull('barn_time_ms')
                    ->orderBy('barn_time_ms')
            )
            ->paginated([5, 10])
            ->columns([
                TextColumn::make('name')
                    ->label('Speler'),

                TextColumn::make('barn_time_ms')
                    ->label('Tijd')
                    ->formatStateUsing(function (int $state): string {
                        $seconds = intdiv($state, 1000);

                        return intdiv($seconds, 60).':'.str_pad((string) ($seconds % 60), 2, '0', STR_PAD_LEFT);
                    }),

                TextColumn::make('barn_catches')
                    ->label('Keer gepakt'),
            ]);
    }
}
