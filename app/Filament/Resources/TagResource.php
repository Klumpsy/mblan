<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TagResource\Pages;
use App\Models\Tag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class TagResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Tag Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                if ($operation !== 'create') {
                                    return;
                                }
                                $set('slug', Str::slug($state));
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Tag::class, 'slug', ignoreRecord: true)
                            ->rules(['alpha_dash'])
                            ->helperText('Auto-generated from name, but can be customized'),

                        Forms\Components\Textarea::make('description')
                            ->maxLength(1000)
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Appearance & Scope')
                    ->schema([
                        Forms\Components\ColorPicker::make('color')
                            ->label('Tag Color')
                            ->helperText('Choose a color to represent this tag'),

                        Forms\Components\Select::make('model_type')
                            ->label('Model Restriction')
                            ->options([
                                'App\\Models\\Game' => 'Games Only',
                                'App\\Models\\Media' => 'Media Only',
                                // Add other model types as needed
                            ])
                            ->placeholder('Universal (can be used with any model)')
                            ->helperText('Leave empty to make this tag available for all models'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->limit(50),

                Tables\Columns\TextColumn::make('color'),

                Tables\Columns\TextColumn::make('model_type')
                    ->label('Scope')
                    ->formatStateUsing(function (?string $state): string {
                        if (!$state) {
                            return 'Universal';
                        }
                        return class_basename($state);
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tag $record) {
                        $gamesCount = $record->games()->count();
                        $mediaCount = $record->media()->count();

                        if ($gamesCount > 0 || $mediaCount > 0) {
                            throw new \Exception("Cannot delete tag that is being used by {$gamesCount} game(s) and {$mediaCount} media item(s).");
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                $gamesCount = $record->games()->count();
                                $mediaCount = $record->media()->count();

                                if ($gamesCount > 0 || $mediaCount > 0) {
                                    throw new \Exception("Cannot delete tag '{$record->name}' that is being used.");
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('name')
            ->striped();
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
            'index' => Pages\ListTags::route('/'),
            'create' => Pages\CreateTag::route('/create'),
            'edit' => Pages\EditTag::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'slug', 'description'];
    }
}
