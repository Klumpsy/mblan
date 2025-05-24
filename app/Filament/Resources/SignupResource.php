<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SignupResource\Pages;
use App\Models\Signup;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Summarizer;

class SignupResource extends Resource
{
    protected static ?string $model = Signup::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('edition.name')
                    ->summarize(Count::make()->label('Total Signups'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('user.email')
                    ->sortable()
                    ->searchable(),

                ToggleColumn::make('stays_on_campsite')
                    ->label('Campsite')
                    ->summarize([
                        Summarizer::make()
                            ->label('Staying on Campsite')
                            ->using(function ($query) {
                                $total = Signup::count();
                                $count = Signup::where('stays_on_campsite', true)->count();
                                $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
                                return "{$count} of {$total} ({$percentage}%)";
                            }),
                    ]),

                ToggleColumn::make('joins_barbecue')
                    ->label('BBQ')
                    ->summarize([
                        Summarizer::make()
                            ->label('Joining BBQ')
                            ->using(function ($query) {
                                $total = Signup::count();
                                $count = Signup::where('joins_barbecue', true)->count();
                                $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
                                return "{$count} of {$total} ({$percentage}%)";
                            }),
                    ]),

                ToggleColumn::make('confirmed')
                    ->summarize([
                        Summarizer::make()
                            ->label('Confirmed')
                            ->using(function ($query) {
                                $total = Signup::count();
                                $count = Signup::where('confirmed', true)->count();
                                $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
                                return "{$count} of {$total} ({$percentage}%)";
                            }),
                    ]),

                TextColumn::make('beverages_list')
                    ->label('Beverages')
                    ->html()
                    ->getStateUsing(function ($record) {
                        $beverages = $record->beverages;

                        if ($beverages->isEmpty()) {
                            return '<span class="text-gray-400 italic">None</span>';
                        }

                        return $beverages->map(function ($beverage) {
                            $badgeClass = $beverage->contains_alcohol
                                ? 'bg-amber-100 text-amber-800'
                                : 'bg-blue-100 text-blue-800';

                            return "<span class='inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {$badgeClass} mr-1 mb-1'>"
                                . e($beverage->name) .
                                "</span>";
                        })->join(' ');
                    })
                    ->wrap()
                    ->summarize([
                        Summarizer::make()
                            ->label('Popular Beverages')
                            ->using(function ($query) {
                                $beverageCounts = [];
                                $signups = Signup::with('beverages')->get();

                                foreach ($signups as $signup) {
                                    foreach ($signup->beverages as $beverage) {
                                        $beverageCounts[$beverage->name] = ($beverageCounts[$beverage->name] ?? 0) + 1;
                                    }
                                }

                                if (empty($beverageCounts)) {
                                    return 'No beverages selected';
                                }

                                arsort($beverageCounts);
                                $top3 = array_slice($beverageCounts, 0, 3, true);

                                $summary = [];
                                foreach ($top3 as $name => $count) {
                                    $summary[] = "{$name}: {$count}";
                                }

                                return implode(' • ', $summary);
                            }),
                    ]),

                TextColumn::make('schedules_list')
                    ->label('Days')
                    ->html()
                    ->getStateUsing(function ($record) {
                        $schedules = $record->schedules;

                        if ($schedules->isEmpty()) {
                            return '<span class="text-gray-400 italic">None</span>';
                        }

                        return $schedules->map(function ($schedule) {
                            return "<span class='inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-1 mb-1'>"
                                . $schedule->name .
                                "</span>";
                        })->join(' ');
                    })
                    ->wrap(),

                TextColumn::make('created_at')
                    ->label('Signed Up')
                    ->dateTime('M j, Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('edition')
                    ->relationship('edition', 'name'),

                Tables\Filters\TernaryFilter::make('stays_on_campsite')
                    ->label('Staying on Campsite'),

                Tables\Filters\TernaryFilter::make('joins_barbecue')
                    ->label('Joining BBQ'),

                Tables\Filters\TernaryFilter::make('confirmed')
                    ->label('Confirmed'),
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
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['confirmed' => true]);
                            }
                        })
                        ->requiresConfirmation()
                        ->color('success'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListSignups::route('/'),
            'edit' => Pages\EditSignup::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('confirmed', false)->count();
    }
}
