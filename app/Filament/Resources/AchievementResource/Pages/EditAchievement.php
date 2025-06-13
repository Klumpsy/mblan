<?php

namespace App\Filament\Resources\AchievementResource\Pages;

use App\Filament\Resources\AchievementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditAchievement extends EditRecord
{
    protected static string $resource = AchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record->isManual()) {
            $this->record->refresh();
            $this->record->load('users');
            $data['user_ids'] = $this->record->users->pluck('id')->toArray();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $data = $this->form->getState();

        if ($data['type'] === 'manual') {
            $this->record->load('users');
            $currentUserIds = $this->record->users->pluck('id')->toArray();
            $newUserIds = $data['user_ids'] ?? [];

            $usersToAdd = array_diff($newUserIds, $currentUserIds);
            $usersToRemove = array_diff($currentUserIds, $newUserIds);

            foreach ($usersToAdd as $userId) {
                $this->record->users()->attach($userId, [
                    'achieved_at' => now(),
                    'progress' => $data['threshold'] ?? 100,
                ]);
            }

            if (!empty($usersToRemove)) {
                $this->record->users()->detach($usersToRemove);
            }

            if (!empty($usersToAdd) || !empty($usersToRemove)) {
                $addedCount = count($usersToAdd);
                $removedCount = count($usersToRemove);
                $message = [];

                if ($addedCount > 0) {
                    $message[] = "Added {$addedCount} user(s)";
                }
                if ($removedCount > 0) {
                    $message[] = "Removed {$removedCount} user(s)";
                }

                if (!empty($message)) {
                    Notification::make()
                        ->title('Users Updated')
                        ->body(implode(', ', $message))
                        ->success()
                        ->send();
                }
            }
        }
    }
}
