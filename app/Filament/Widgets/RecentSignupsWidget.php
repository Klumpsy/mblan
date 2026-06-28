<?php

namespace App\Filament\Widgets;

use App\Models\Signup;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\Action;

class RecentSignupsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Signup::query()
                    ->with(['user', 'edition'])
                    ->latest('created_at')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('edition.name')
                    ->label('Edition')
                    ->badge()
                    ->color('primary'),

                IconColumn::make('confirmed')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),

                TextColumn::make('cost')
                    ->label('Cost')
                    ->getStateUsing(function (Signup $record) {
                        return '€' . number_format($record->calculateCost(), 2);
                    })
                    ->color('success'),

                TextColumn::make('created_at')
                    ->label('Signed Up')
                    ->dateTime('M j, H:i')
                    ->sortable(),
            ])
            ->actions([
                Action::make('confirm')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn(Signup $record) => $record->update(['confirmed' => true]))
                    ->visible(fn(Signup $record) => !$record->confirmed)
                    ->requiresConfirmation(),
            ])
            ->heading('Recent Signups')
            ->description('Latest 10 user registrations');
    }
}
