<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GameResource\Pages;
use App\Filament\Resources\GameResource\RelationManagers;
use App\Models\Game;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GameResource extends Resource
{
    protected static ?string $model = Game::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    DatePicker::make('yearOfRelease')
                        ->label('Release date')
                        ->displayFormat('d-m-Y')
                        ->format('Y-m-d')
                        ->closeOnDateSelection()
                        ->required(),
                    TextInput::make('linkToWebsite')
                        ->url()
                        ->required()
                        ->label('Website URL')
                        ->maxLength(255),
                    TextInput::make('linkToYoutube')
                        ->url()
                        ->required()
                        ->label('YouTube URL')
                        ->maxLength(255),
                ])->columns(2),
                Section::make([
                    FileUpload::make('image')
                        ->disk('public')
                        ->directory('games')
                        ->visibility('public')
                        ->required()
                        ->imageResizeMode('cover')
                        ->imageCropAspectRatio('16:9'),
                    RichEditor::make('shortDescription')
                        ->required()
                        ->toolbarButtons(self::$textEditorSettings),
                ])->columns(2),
                Section::make([
                    RichEditor::make('textBlockOne')
                        ->toolbarButtons(self::$textEditorSettings)
                        ->minLength(50)
                        ->required(),
                    RichEditor::make('textBlockTwo')
                        ->toolbarButtons(self::$textEditorSettings)
                        ->minLength(50),
                    RichEditor::make('textBlockThree')
                        ->toolbarButtons(self::$textEditorSettings)
                        ->minLength(50),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->disk('public')
                    ->extraImgAttributes(['class' => 'object-cover'])
                    ->height(40)
                    ->visibility('public')
                    ->extraImgAttributes(['loading' => 'lazy'])
                    ->toggleable(),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('yearOfRelease')
                    ->sortable()
                    ->label('Year of Release')
                    ->toggleable(),
                TextColumn::make('likes_count')
                    ->label('Likes')
                    ->toggleable()
                    ->getStateUsing(fn(Game $record): int => $record->getLikesCountAttribute()),
                TextColumn::make('linkToWebsite')
                    ->label('Website URL')
                    ->toggleable()
                    ->url(fn(Game $record): ?string => $record->linkToWebsite)
                    ->openUrlInNewTab(),
                TextColumn::make('linkToYoutube')
                    ->label('YouTube URL')
                    ->toggleable()
                    ->url(fn(Game $record): ?string => $record->linkToYoutube)
                    ->openUrlInNewTab(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListGames::route('/'),
            'create' => Pages\CreateGame::route('/create'),
            'edit' => Pages\EditGame::route('/{record}/edit'),
        ];
    }
}
