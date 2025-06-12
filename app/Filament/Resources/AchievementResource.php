<?php

namespace App\Filament\Resources;

use App\AchievementType;
use App\Enums\AchievementType as EnumsAchievementType;
use App\Filament\Resources\AchievementResource\Pages;
use App\Filament\Resources\AchievementResource\RelationManagers;
use App\Models\User;
use App\Models\Tournament;
use App\Models\Game;
use App\Models\Schedule;
use App\Models\Edition;
use App\Models\Signup;
use App\Models\Achievement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;

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
                FileUpload::make('icon_path')->directory('achievements')->image(),
                ColorPicker::make('color')->default('#10b981'),
                ColorPicker::make('grayed_color')->default('#6b7280'),
                Select::make('type')
                    ->options(['automatic' => 'Automatic', 'manual' => 'Manual'])
                    ->required(),
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
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
