<?php

namespace App\Filament\Resources\AchievementResource\Pages;

use App\Filament\Resources\AchievementResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAchievement extends CreateRecord
{
    protected static string $resource = AchievementResource::class;

    protected function afterCreate(): void
    {
        $data = $this->form->getState();

        if ($data['type'] === 'manual' && !empty($data['user_ids'])) {
            foreach ($data['user_ids'] as $userId) {
                $this->record->users()->attach($userId, [
                    'achieved_at' => now(),
                ]);
            }
        }
    }
}
