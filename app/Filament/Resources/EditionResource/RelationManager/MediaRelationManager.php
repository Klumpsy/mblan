<?php

namespace App\Filament\Resources\EditionResource\RelationManager;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Columns\ImageColumn;

class MediaRelationManager extends RelationManager
{
    protected static string $relationship = 'media';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('file_path')
                    ->label('Media Files')
                    ->multiple()
                    ->image()
                    ->directory('editions/media')
                    ->preserveFilenames()
                    ->required(),

                Select::make('type')
                    ->label('Type')
                    ->options([
                        'image' => 'Image',
                        'video' => 'Video',
                    ])
                    ->default('image')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('file_path')
            ->columns([
                ImageColumn::make('file_path')
                    ->label('Preview')
                    ->disk('public')
                    ->circular()
                    ->height(40),

                Tables\Columns\TextColumn::make('type')->label('Type'),
                Tables\Columns\TextColumn::make('created_at')->since()->label('Uploaded'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Upload Media')
                    ->form([
                        FileUpload::make('file_path')
                            ->label('Media Files')
                            ->multiple()
                            ->image()
                            ->directory('editions/media')
                            ->preserveFilenames()
                            ->required(),

                        Select::make('type')
                            ->label('Type')
                            ->options([
                                'image' => 'Image',
                                'video' => 'Video',
                            ])
                            ->default('image')
                            ->required(),
                    ])
                    ->action(function (array $data, RelationManager $livewire) {
                        foreach ($data['file_path'] as $filePath) {
                            $livewire->getRelationship()->create([
                                'file_path' => $filePath,
                                'type' => $data['type'],
                            ]);
                        }
                    }),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
