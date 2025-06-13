<?php

namespace App\Filament\Resources;

use App\Enums\AchievementType as EnumsAchievementType;
use App\Filament\Resources\AchievementResource\Pages;
use App\Models\User;
use App\Models\Tournament;
use App\Models\Game;
use App\Models\Schedule;
use App\Models\Edition;
use App\Models\Signup;
use App\Models\Achievement;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Get;

class AchievementResource extends Resource
{
    protected static ?string $model = Achievement::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                Select::make('slug')
                    ->label('Achievement Type (these are predefined by the dev, if you need a custom one, please contact Bart)')
                    ->options(function () {
                        $all = EnumsAchievementType::availableSlugs();
                        $used = Achievement::pluck('slug')->toArray();
                        return array_diff($all, $used);
                    })
                    ->searchable()
                    ->required()
                    ->unique(ignoreRecord: true),
                Textarea::make('description'),
                FileUpload::make('icon_path')->directory('achievements')->image()->label("Use svg's from: https://www.svgrepo.com"),
                ColorPicker::make('color')->default('#10b981'),
                ColorPicker::make('grayed_color')->default('#6b7280'),
                Select::make('type')
                    ->options(['automatic' => 'Automatic', 'manual' => 'Manual'])
                    ->required()
                    ->live(), // Make it reactive
                Select::make('model_type')
                    ->label('Related Model')
                    ->options([
                        User::class => 'User',
                        Tournament::class => 'Tournament',
                        Game::class => 'Game',
                        Signup::class => 'Signup',
                        Schedule::class => 'Schedule',
                        Edition::class => 'Edition',
                    ])
                    ->searchable()
                    ->nullable()
                    ->helperText('Select the model this achievement is linked to'),
                TextInput::make('threshold')->numeric(),

                Select::make('user_ids')
                    ->label('Award to Users')
                    ->multiple()
                    ->searchable()
                    ->options(fn() => User::all()->pluck('name', 'id'))
                    ->helperText('Select users to award this achievement to')
                    ->visible(fn(Get $get): bool => $get('type') === 'manual')
                    ->reactive(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('slug'),
                TextColumn::make('type'),
                TextColumn::make('threshold'),
                TextColumn::make('users_count')
                    ->label('Awarded Users')
                    ->counts('users')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'automatic' => 'Automatic',
                        'manual' => 'Manual',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('manage_users')
                    ->label('Manage Users')
                    ->icon('heroicon-o-user-group')
                    ->visible(fn(Achievement $record): bool => $record->isManual())
                    ->form([
                        Select::make('user_ids')
                            ->label('Select Users')
                            ->multiple()
                            ->searchable()
                            ->options(User::all()->pluck('name', 'id'))
                            ->default(function (Achievement $record): array {
                                $record->refresh();
                                $record->load('users');
                                return $record->users->pluck('id')->toArray();
                            }),
                    ])
                    ->action(function (Achievement $record, array $data): void {
                        $record->refresh();
                        $record->load('users');
                        $currentUserIds = $record->users->pluck('id')->toArray();
                        $newUserIds = $data['user_ids'] ?? [];

                        $usersToAdd = array_diff($newUserIds, $currentUserIds);
                        $usersToRemove = array_diff($currentUserIds, $newUserIds);

                        foreach ($usersToAdd as $userId) {
                            $record->users()->attach($userId, [
                                'achieved_at' => now(),
                                'progress' => $record->threshold ?? 100,
                            ]);
                        }

                        if (!empty($usersToRemove)) {
                            $record->users()->detach($usersToRemove);
                        }
                        $record->refresh();
                        $record->load('users');
                    })
                    ->successNotificationTitle('Achievement users updated successfully')

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAchievements::route('/'),
            'create' => Pages\CreateAchievement::route('/create'),
            'edit' => Pages\EditAchievement::route('/{record}/edit'),
        ];
    }
}
