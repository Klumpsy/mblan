<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EditionResource\Pages;
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

            FileUpload::make('logo_path')
                ->label('Logo')
                ->disk('public')
                ->directory('editions')
                ->visibility('public')
                ->image(),

            FileUpload::make('hero_image_path')
                ->label('Hero-afbeelding')
                ->disk('public')
                ->directory('editions')
                ->visibility('public')
                ->image()
                ->imageResizeTargetWidth('1920')
                ->imageResizeTargetHeight('1080'),

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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEditions::route('/'),
            'create' => Pages\CreateEdition::route('/create'),
            'edit' => Pages\EditEdition::route('/{record}/edit'),
        ];
    }
}
