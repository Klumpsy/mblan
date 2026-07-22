<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EditionResource\Pages;
use App\Filament\Resources\EditionResource\RelationManager\MediaRelationManager;
use App\Models\Edition;
use App\Models\User;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class EditionResource extends Resource
{
    protected static ?string $model = Edition::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calendar-days';

    protected static array $textEditorSettings = [
        'attachFiles',
        'blockquote',
        'bold',
        'bulletList',
        'codeBlock',
        'h2',
        'h3',
        'italic',
        'link',
        'orderedList',
        'redo',
        'strike',
        'underline',
        'undo',
    ];

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Basis Informatie')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('slug')
                            ->required()
                            ->unique(
                                table: Edition::class,
                                ignoreRecord: true
                            )
                            ->maxLength(50),
                        Select::make('year')
                            ->label('Event Jaar')
                            ->options(
                                collect(range(2024, 2050))
                                    ->mapWithKeys(fn($year) => [$year => $year])
                                    ->toArray()
                            )
                            ->default(2025)
                            ->placeholder('Selecteer een jaar')
                            ->searchable()
                            ->required()
                            ->reactive(),
                        ColorPicker::make('color')
                            ->label('Thema Kleur')
                            ->helperText('Deze kleur bepaalt het accent van de hele site wanneer deze editie actief is.')
                            ->default(\App\Support\ThemeService::DEFAULT_COLOR)
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Is Actief')
                            ->helperText('Markeer deze editie als de actieve editie. Alleen één editie kan actief zijn tegelijk.')
                            ->default(false)
                            ->required()
                            ->afterStateUpdated(function ($state, $record) {
                                if ($state === true && $record) {
                                    Edition::where('id', '!=', $record->id)
                                        ->update(['is_active' => false]);
                                }
                            }),
                        RichEditor::make('description')
                            ->toolbarButtons(self::$textEditorSettings)
                            ->minLength(50)
                            ->required(),
                        FileUpload::make('logo')
                            ->disk('public')
                            ->directory('editions')
                            ->visibility('public')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9'),
                    ])->columns(2),

                Section::make('Exclusieve Toegang')
                    ->schema([
                        Toggle::make('is_exclusive')
                            ->label('Is Exclusief')
                            ->helperText('Markeer deze editie als exclusief voor geselecteerde gebruikers.')
                            ->default(false)
                            ->reactive(),
                        Select::make('exclusiveUsers')
                            ->label('Exclusieve Gebruikers')
                            ->helperText('Selecteer gebruikers die exclusieve toegang hebben tot deze editie.')
                            ->relationship('exclusiveUsers', 'name')
                            ->options(
                                User::all()->pluck('name', 'id')->toArray()
                            )
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->placeholder('Selecteer gebruikers voor exclusieve toegang')
                            ->visible(fn($get) => $get('is_exclusive') === true)
                            ->required(fn($get) => $get('is_exclusive') === true),
                    ])
                    ->collapsible()
                    ->collapsed(fn($record) => !$record?->is_exclusive),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->disk('public')
                    ->extraImgAttributes(['class' => 'object-cover'])
                    ->height(40)
                    ->visibility('public')
                    ->extraImgAttributes(['loading' => 'lazy'])
                    ->toggleable(),
                ToggleColumn::make('is_active')
                    ->label('Actief')
                    ->onIcon('heroicon-s-check-circle')
                    ->offIcon('heroicon-s-x-circle')
                    ->onColor('success')
                    ->offColor('danger')
                    ->sortable()
                    ->toggleable()
                    ->afterStateUpdated(function ($state, $record) {
                        if ($state === true) {
                            Edition::where('id', '!=', $record->id)
                                ->update(['is_active' => false]);
                        }
                    }),
                TextColumn::make('is_exclusive')
                    ->label('Exclusief')
                    ->badge()
                    ->color(fn($record) => $record->is_exclusive ? 'warning' : 'success')
                    ->formatStateUsing(fn($record) => $record->is_exclusive ? 'Ja' : 'Nee')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('slug')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('year')
                    ->sortable()
                    ->label('Edition year')
                    ->toggleable(),
                \Filament\Tables\Columns\ColorColumn::make('color')
                    ->label('Thema')
                    ->toggleable(),
                TextColumn::make('exclusive_status')
                    ->label('Toegang')
                    ->badge()
                    ->color(fn($record) => $record->is_exclusive ? 'warning' : 'success')
                    ->formatStateUsing(function ($record) {
                        if (!$record->is_exclusive) {
                            return 'Publiek';
                        }
                        $count = $record->exclusiveUsers()->count();
                        return $count > 0 ? "{$count} exclusief" : 'Exclusief (geen gebruikers)';
                    })
                    ->toggleable(),
            ])
            ->filters([
                //
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
            MediaRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEditions::route('/'),
            'create' => Pages\CreateEdition::route('/create'),
            'edit' => Pages\EditEdition::route('/{record}/edit'),
        ];
    }
}
