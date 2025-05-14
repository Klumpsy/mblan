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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                    ->label('Website URL')
                    ->maxLength(255),    
                TextInput::make('linkToYoutube')
                    ->url()
                    ->label('YouTube URL')
                    ->maxLength(255),
                FileUpload::make('image')
                    ->disk('public')
                    ->directory('games')
                    ->visibility('public')
                    ->imageResizeMode('cover')
                    ->imageCropAspectRatio('16:9'),
                RichEditor::make('shortDescription')
                    ->toolbarButtons(self::$textEditorSettings),
                RichEditor::make('textBlockOne')
                    ->toolbarButtons(self::$textEditorSettings),
                RichEditor::make('textBlockTwo')
                    ->toolbarButtons(self::$textEditorSettings),
                RichEditor::make('textBlockThree')
                    ->toolbarButtons(self::$textEditorSettings),
                
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
                        ->extraImgAttributes(['loading' => 'lazy']),
                    TextColumn::make('name'),
                    TextColumn::make('yearOfRelease')
                        ->label('Year of Release'),
                    TextColumn::make('likes_count')
                        ->label('Likes')
                        ->getStateUsing(fn (Game $record): int => $record->getLikesCountAttribute()),
                    TextColumn::make('linkToWebsite')
                        ->label('Website URL')
                        ->url(fn (Game $record): ?string => $record->linkToWebsite)
                        ->openUrlInNewTab(),
                    TextColumn::make('linkToYoutube')
                        ->label('YouTube URL')
                        ->url(fn (Game $record): ?string => $record->linkToYoutube)
                        ->openUrlInNewTab(),
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
            'index' => Pages\ListGames::route('/'),
            'create' => Pages\CreateGame::route('/create'),
            'edit' => Pages\EditGame::route('/{record}/edit'),
        ];
    }
}
