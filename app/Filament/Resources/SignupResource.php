<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SignupResource\Pages;
use App\Models\Signup;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class SignupResource extends Resource
{
    protected static ?string $model = Signup::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('edition_id')
                ->relationship('edition', 'name')
                ->required(),

            Select::make('user_id')
                ->relationship('user', 'email')
                ->required(),

            Select::make('schedules')
                ->multiple()
                ->relationship('schedules', 'name')
                ->label('Schedules')
                ->required()
                ->preload(),

            Checkbox::make('stays_on_campsite')->label('Staying on Campsite'),
            Checkbox::make('joins_barbecue')->label('Joining BBQ'),
            Checkbox::make('joins_pizza')->label('Joining Pizza'),
            Checkbox::make('is_vegan')->label('Vegan (for BBQ)'),
            Checkbox::make('wants_tshirt')->label('Wants T-Shirt'),

            Select::make('tshirt_size')
                ->options([
                    'S' => 'Small',
                    'M' => 'Medium',
                    'L' => 'Large',
                    'XL' => 'X-Large',
                    'XXL' => 'XX-Large',
                ])
                ->visible(fn($get) => $get('wants_tshirt')),

            TextInput::make('tshirt_text')
                ->visible(fn($get) => $get('wants_tshirt')),

            Checkbox::make('confirmed')->label('Confirmed'),
        ]);
    }

    public static function table(Table $table): Table
    {
        $totalSignups = Signup::count();
        return $table
            ->columns([
                TextColumn::make('cost')
                    ->label('Total Cost (€)')
                    ->sortable()
                    ->getStateUsing(function (Signup $record) use ($totalSignups) {
                        $cost = $record->calculateCost($totalSignups);
                        return '€' . number_format($cost, 2);
                    }),
                TextColumn::make('edition.name')
                    ->label('Edition')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('user.email')->label('User')->sortable()->searchable(),
                ToggleColumn::make('stays_on_campsite')->label('🏕️')->sortable(),
                ToggleColumn::make('joins_barbecue')->label('🍖')->sortable(),
                ToggleColumn::make('joins_pizza')->label('🍕')->sortable(),
                ToggleColumn::make('is_vegan')->label('🌱')->sortable(),
                ToggleColumn::make('wants_tshirt')->label('👕')->sortable(),
                TextColumn::make('tshirt_size')->label('Size')->sortable(),
                TextColumn::make('tshirt_text')->label('T-Shirt Text')->wrap()->limit(30),
                ToggleColumn::make('confirmed')->label('✅')->sortable(),
                TextColumn::make('created_at')->label('Signed Up')->dateTime('M j, Y H:i')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('edition')->relationship('edition', 'name'),
                Tables\Filters\TernaryFilter::make('stays_on_campsite')->label('Campsite'),
                Tables\Filters\TernaryFilter::make('joins_barbecue')->label('BBQ'),
                Tables\Filters\TernaryFilter::make('is_vegan')->label('Vegan'),
                Tables\Filters\TernaryFilter::make('wants_tshirt')->label('T-Shirt'),
                Tables\Filters\TernaryFilter::make('confirmed')->label('Confirmed'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('confirm_signups')
                        ->label('Confirm Selected')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn($records) => $records->each->update(['confirmed' => true]))
                        ->requiresConfirmation()
                        ->color('success'),
                ]),
            ])
            ->headerActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSignups::route('/'),
            'edit' => Pages\EditSignup::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('confirmed', false)->count();
    }
}
