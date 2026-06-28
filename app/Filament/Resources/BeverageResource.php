<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BeverageResource\Pages;
use App\Models\Beverage;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class BeverageResource extends Resource
{
    protected static ?string $model = Beverage::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    RichEditor::make('description')
                        ->required()
                        ->unique()
                        ->maxLength(250),
                    Toggle::make('contains_alcohol'),
                    FileUpload::make('image')
                        ->disk('public')
                        ->directory('beverages')
                        ->visibility('public')
                        ->required()
                        ->imageResizeMode('cover')
                        ->imageCropAspectRatio('16:9'),
                ])->columns(2),
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
                ToggleColumn::make('contains_alcohol')
                    ->sortable()
                    ->label('Contains alcohol')
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBeverages::route('/'),
            'create' => Pages\CreateBeverage::route('/create'),
            'edit' => Pages\EditBeverage::route('/{record}/edit'),
        ];
    }
}
