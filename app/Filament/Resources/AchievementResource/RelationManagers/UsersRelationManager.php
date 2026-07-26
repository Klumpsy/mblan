<?php

namespace App\Filament\Resources\AchievementResource\RelationManagers;

use App\Models\User;
use App\Services\AchievementEvaluator;
use Filament\Actions\Action;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Lets admins grant this achievement to specific users (backfill, e.g. who
 * attended the 2024/2025 edition). Granting marks it achieved and posts the
 * Discord notification via the shared evaluator.
 */
class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Deelnemers';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                IconColumn::make('pivot.achieved_at')
                    ->label('Behaald')
                    ->boolean()
                    ->state(fn ($record) => (bool) $record->pivot->achieved_at),
                TextColumn::make('pivot.achieved_at')
                    ->label('Behaald op')
                    ->dateTime('d-m-Y')
                    ->placeholder('In uitvoering'),
                TextColumn::make('pivot.progress')->label('Voortgang')->placeholder('-'),
            ])
            ->headerActions([
                Action::make('grant')
                    ->label('Toekennen aan speler')
                    ->icon('heroicon-o-plus')
                    ->form([
                        Select::make('user_id')
                            ->label('Speler')
                            ->options(fn () => User::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $user = User::find($data['user_id']);
                        if (! $user) {
                            return;
                        }

                        $granted = app(AchievementEvaluator::class)->grant($user, $this->getOwnerRecord());

                        Notification::make()
                            ->title($granted ? 'Achievement toegekend' : 'Speler had deze al')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                DetachAction::make()->label('Intrekken'),
            ])
            ->bulkActions([
                DetachBulkAction::make(),
            ]);
    }
}
