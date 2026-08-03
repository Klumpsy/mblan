<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhotoResource\Pages;
use App\Models\Photo;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Beheer van de foto-tijdlijn, vooral om oudere edities te kunnen backfillen:
 * kies een editie en een datum en de foto verschijnt in dat jaar (en op de
 * recap-pagina). Spelers posten zelf gewoon via de site.
 */
class PhotoResource extends Resource
{
    protected static ?string $model = Photo::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-photo';

    protected static string | \UnitEnum | null $navigationGroup = 'Deze editie';

    protected static ?string $navigationLabel = 'Tijdlijn';

    protected static ?string $modelLabel = 'tijdlijnfoto';

    protected static ?string $pluralModelLabel = "tijdlijnfoto's";

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            FileUpload::make('image')
                ->label('Foto')
                ->required()
                ->image()
                ->disk('public')
                ->directory('timeline')
                ->visibility('public')
                ->imageResizeMode('contain')
                ->imageResizeTargetWidth('1600')
                ->imageResizeTargetHeight('1600')
                ->maxSize(12288)
                ->helperText('JPG of PNG, max 12 MB (grote foto\'s worden automatisch verkleind).'),

            Textarea::make('story')
                ->label('Verhaal')
                ->required()
                ->rows(3)
                ->maxLength(1000)
                ->helperText('Het korte verhaaltje bij de foto, zoals spelers dat zelf ook schrijven.'),

            Select::make('user_id')
                ->label('Speler')
                ->relationship('user', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->helperText('Onder wiens naam de foto op de tijdlijn staat.'),

            Select::make('edition_id')
                ->label('Editie')
                ->relationship('edition', 'name')
                ->default(fn () => \App\Models\Edition::current()?->id)
                ->required()
                ->helperText('Kies een oudere editie om het archief te backfillen; de foto verschijnt dan op de recap van dat jaar.'),

            DateTimePicker::make('created_at')
                ->label('Datum')
                ->seconds(false)
                ->default(now())
                ->helperText('Bij backfillen: de oorspronkelijke datum van de foto.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Foto')
                    ->disk('public')
                    ->height(40),
                TextColumn::make('story')
                    ->label('Verhaal')
                    ->limit(60)
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Speler')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Datum')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
                TextColumn::make('edition.name')
                    ->label('Editie')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('edition_id')
                    ->label('Editie')
                    ->relationship('edition', 'name')
                    // Standaard zie je de actieve editie; kies een jaar voor het archief.
                    ->default(\App\Models\Edition::current()?->id),
            ])
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
            'index' => Pages\ListPhotos::route('/'),
            'create' => Pages\CreatePhoto::route('/create'),
            'edit' => Pages\EditPhoto::route('/{record}/edit'),
        ];
    }
}
