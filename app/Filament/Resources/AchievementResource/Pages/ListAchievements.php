<?php

namespace App\Filament\Resources\AchievementResource\Pages;

use App\Filament\Resources\AchievementResource;
use App\Jobs\ProcessAllAchievements;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListAchievements extends ListRecords
{
    protected static string $resource = AchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('processAchievements')
                ->label('Process All Achievements')
                ->icon('heroicon-o-play')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Process All Achievements')
                ->modalDescription('This will check all users for missed automatic achievements and award them. Manual achievements are not affected. This process runs in the background.')
                ->modalSubmitActionLabel('Start Processing')
                ->action(function () {
                    try {
                        ProcessAllAchievements::dispatch();

                        Notification::make()
                            ->title('Achievement Processing Started')
                            ->body('The achievement processing job has been queued and will run in the background. Check your logs for progress.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Failed to Start Processing')
                            ->body('There was an error starting the achievement processing: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
