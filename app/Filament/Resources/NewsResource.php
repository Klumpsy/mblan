<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsResource\Pages;
use App\Models\News;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationLabel = 'Nieuws';

    protected static ?string $modelLabel = 'nieuwsbericht';

    protected static ?string $pluralModelLabel = 'nieuws';

    protected static array $textEditorSettings = [
        'blockquote',
        'bold',
        'bulletList',
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
                Section::make([
                    TextInput::make('title')
                        ->label('Titel')
                        ->required()
                        ->maxLength(255),
                    FileUpload::make('image')
                        ->label('Afbeelding')
                        ->disk('public')
                        ->directory('news')
                        ->visibility('public')
                        ->image()
                        ->imageResizeMode('cover')
                        ->imageCropAspectRatio('16:9')
                        // Downscale in the browser before upload so multi-megapixel
                        // phone photos stay well under the server's PHP upload limit.
                        ->imageResizeTargetWidth('1280')
                        ->imageResizeTargetHeight('720'),
                    Textarea::make('preview_text')
                        ->label('Korte samenvatting')
                        ->helperText('Wordt getoond in het overzicht. Laat leeg om automatisch af te leiden.')
                        ->rows(2)
                        ->maxLength(255),
                    RichEditor::make('content')
                        ->label('Inhoud')
                        ->required()
                        ->toolbarButtons(self::$textEditorSettings),
                ])->columns(1),
                Section::make([
                    Toggle::make('published')
                        ->label('Gepubliceerd')
                        ->default(true),
                    DateTimePicker::make('published_at')
                        ->label('Publicatiedatum')
                        ->default(now()),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Afbeelding')
                    ->disk('public')
                    ->height(40)
                    ->toggleable(),
                TextColumn::make('title')
                    ->label('Titel')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('author.name')
                    ->label('Auteur')
                    ->toggleable(),
                IconColumn::make('published')
                    ->label('Gepubliceerd')
                    ->boolean(),
                TextColumn::make('published_at')
                    ->label('Datum')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNews::route('/'),
            'create' => Pages\CreateNews::route('/create'),
            'edit' => Pages\EditNews::route('/{record}/edit'),
        ];
    }
}
