<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EditionResource\Pages;
use App\Filament\Resources\EditionResource\RelationManagers;
use App\Models\Edition;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EditionResource extends Resource
{
    protected static ?string $model = Edition::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-archive-box';

    protected static string | \UnitEnum | null $navigationGroup = 'Archief';

    protected static ?string $navigationLabel = 'Edities';

    protected static ?string $modelLabel = 'editie';

    protected static ?string $pluralModelLabel = 'edities';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            TextInput::make('name')
                ->label('Naam')
                ->required()
                ->maxLength(255)
                ->helperText('Bijv. "MBLAN27".'),

            TextInput::make('year')
                ->label('Jaar')
                ->numeric()
                ->required()
                ->minValue(2000)
                ->maxValue(2999),

            TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->alphaDash()
                ->unique(ignoreRecord: true)
                ->helperText('Voor de URL, bijv. "mblan27" wordt /edities/mblan27.'),

            TextInput::make('tagline')
                ->label('Tagline')
                ->maxLength(255),

            ColorPicker::make('primary_color')
                ->label('Hoofdkleur')
                ->required()
                ->helperText('De accentkleur waaruit het hele kleurenpalet van deze editie wordt afgeleid.'),

            \Filament\Forms\Components\Select::make('scenery_set')
                ->label('Achtergrond-sprites')
                ->options(\App\Support\ScenerySets::options())
                ->default(\App\Support\ScenerySets::DEFAULT)
                ->required()
                ->helperText('De ingebouwde pixel-sprites voor de zijkanten van elke pagina. Een geüpload spritepakket hieronder gaat vóór deze keuze.'),

            FileUpload::make('scenery_sprites')
                ->label('Eigen spritepakket')
                ->multiple()
                ->image()
                ->acceptedFileTypes(['image/png'])
                ->maxSize(512)
                ->disk('public')
                ->directory('editions/scenery')
                ->visibility('public')
                ->reorderable()
                ->helperText('Losse PNG-sprites: pixel-art met transparante achtergrond, klein formaat (± 16–64 px, de site schaalt ze scherp op), max 512 KB per stuk. Upload er minimaal 6 voor variatie. Zodra er sprites zijn geüpload gebruikt deze editie dit pakket overal: achtergronden, het speelschema, de tijdlijn-achtervolging, het menu en de recap-pagina. De volgorde bepaalt de rollen — versleep om te wijzigen: sprite 1 = karakter (boer/astronaut, altijd zichtbaar), sprite 2 = mascotte (Arti/alien, o.a. bij uploads en de tijdlijn), sprite 3 = landmark (schuur/planeet, in het menu), de rest is decoratie.'),

            FileUpload::make('logo_path')
                ->label('Logo')
                ->disk('public')
                ->directory('editions')
                ->visibility('public')
                ->image()
                ->acceptedFileTypes(['image/png', 'image/svg+xml', 'image/webp'])
                ->maxSize(2048)
                ->helperText('PNG, SVG of WebP met transparante achtergrond, max 2 MB. Wordt getoond in de hero van de recap-pagina.'),

            FileUpload::make('hero_image_path')
                ->label('Banner')
                ->disk('public')
                ->directory('editions')
                ->visibility('public')
                ->image()
                ->maxSize(4096)
                ->imageResizeTargetWidth('1920')
                ->imageResizeTargetHeight('1080')
                ->helperText('JPG of PNG, liggend (16:9, ± 1920×1080 — groter wordt automatisch verkleind), max 4 MB. Wordt als banner getoond bovenaan het speelschema zolang de editie actief is, op de editie-kaart en op de recap-pagina.'),

            DatePicker::make('starts_at')
                ->label('Start')
                ->native(false)
                ->displayFormat('d-m-Y'),

            DatePicker::make('ends_at')
                ->label('Einde')
                ->native(false)
                ->displayFormat('d-m-Y'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Naam')->searchable()->sortable(),
                TextColumn::make('year')->label('Jaar')->sortable(),
                ColorColumn::make('primary_color')->label('Kleur'),
                IconColumn::make('is_active')->label('Actief')->boolean(),
                \Filament\Tables\Columns\ImageColumn::make('scenery_sprites')
                    ->label('Spritepakket')
                    ->disk('public')
                    ->stacked()
                    ->limit(5)
                    ->imageHeight(32)
                    ->extraImgAttributes(['class' => 'pixel'])
                    ->placeholder('Ingebouwde set'),
                TextColumn::make('schedules_count')->label('Speeldagen')->counts('schedules'),
                TextColumn::make('tournaments_count')->label('Toernooien')->counts('tournaments'),
                TextColumn::make('news_count')->label('Nieuws')->counts('news'),
                TextColumn::make('photos_count')->label("Foto's")->counts('photos'),
                TextColumn::make('signups_count')->label('Aanmeldingen')->counts('signups'),
                TextColumn::make('participants_count')->label('Deelnemers')->counts('participants'),
            ])
            ->defaultSort('year', 'desc')
            ->actions([
                Action::make('activate')
                    ->label('Maak actief')
                    ->icon('heroicon-o-star')
                    ->requiresConfirmation()
                    ->modalHeading('Editie activeren')
                    ->modalDescription('De site toont vanaf dat moment deze editie; de vorige editie wordt een archief met eigen recap-pagina.')
                    ->hidden(fn (Edition $record) => $record->is_active)
                    ->action(fn (Edition $record) => $record->activate()),
                EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ParticipantsRelationManager::class,
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
