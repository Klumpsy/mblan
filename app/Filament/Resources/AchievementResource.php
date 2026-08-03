<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AchievementResource\Pages;
use App\Filament\Resources\AchievementResource\RelationManagers\UsersRelationManager;
use App\Models\Achievement;
use App\Support\AchievementMetrics;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class AchievementResource extends Resource
{
    protected static ?string $model = Achievement::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-trophy';

    protected static string | \UnitEnum | null $navigationGroup = 'Beheer';

    protected static ?string $navigationLabel = 'Achievements';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, Set $set, $livewire) {
                    // Suggest a slug from the name only while creating a new one.
                    if (! $livewire->record && filled($state)) {
                        $set('slug', Str::slug($state));
                    }
                }),

            TextInput::make('slug')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->helperText('Unieke, vaste sleutel in kebab-case. Verander deze niet zomaar.'),

            Textarea::make('description')
                ->rows(2)
                ->maxLength(500),

            Select::make('type')
                ->options(['automatic' => 'Automatisch (door het systeem)', 'manual' => 'Handmatig (door een beheerder)'])
                ->default('automatic')
                ->required()
                ->live(),

            Select::make('metric')
                ->label('Meetwaarde')
                ->options(AchievementMetrics::options())
                ->helperText('Welke waarde het systeem meet om deze achievement toe te kennen.')
                ->required(fn (Get $get) => $get('type') === 'automatic')
                ->visible(fn (Get $get) => $get('type') === 'automatic'),

            TextInput::make('threshold')
                ->label('Drempel')
                ->numeric()
                ->minValue(1)
                ->default(1)
                ->helperText('Bij welke waarde de achievement behaald is (bijv. 5 biertjes).')
                ->required(fn (Get $get) => $get('type') === 'automatic')
                ->visible(fn (Get $get) => $get('type') === 'automatic'),

            TextInput::make('icon_path')
                ->label('Icoon-pad')
                ->default('images/farm/tile_0000.png')
                ->helperText('Pad in public/, bijv. images/farm/tile_0072.png'),

            ColorPicker::make('color')->default('#65e59a'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state) => $state === 'automatic' ? 'success' : 'warning'),
                TextColumn::make('metric')->label('Meetwaarde')->placeholder('-'),
                TextColumn::make('threshold')->label('Drempel')->placeholder('-'),
                TextColumn::make('users_count')
                    ->label('Behaald door')
                    ->counts(['users as users_count' => fn ($q) => $q->whereNotNull('achievement_user.achieved_at')]),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            UsersRelationManager::class,
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
