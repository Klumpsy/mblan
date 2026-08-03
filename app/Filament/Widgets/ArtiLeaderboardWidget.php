<?php

namespace App\Filament\Widgets;

use App\Models\Edition;
use App\Models\GameResult;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * Fastest finishers of the Arti barn mini-game in the active edition: the
 * players who reached the barn, ranked by time.
 */
class ArtiLeaderboardWidget extends TableWidget
{
    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        $edition = Edition::current();

        return $table
            ->heading('Arti-spel toppers')
            ->query(
                GameResult::query()
                    ->with('user')
                    ->when($edition, fn ($q) => $q->forEdition($edition))
                    ->where('completed', true)
                    ->orderByRaw('score IS NULL')
                    ->orderByDesc('score')
                    ->orderByRaw('time_ms IS NULL')
                    ->orderBy('time_ms')
            )
            ->paginated([5, 10])
            ->columns([
                TextColumn::make('user.name')
                    ->label('Speler'),

                TextColumn::make('score')
                    ->label('Punten')
                    ->placeholder('-'),

                TextColumn::make('time_ms')
                    ->label('Tijd (maze)')
                    ->placeholder('-')
                    ->formatStateUsing(function (int $state): string {
                        $seconds = intdiv($state, 1000);

                        return intdiv($seconds, 60).':'.str_pad((string) ($seconds % 60), 2, '0', STR_PAD_LEFT);
                    }),
            ]);
    }
}
