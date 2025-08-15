<?php

namespace App\Filament\Resources\SignupResource\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Signup;
use App\Models\Edition;
use Illuminate\Database\Eloquent\Builder;

class PizzaOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected static ?string $pollingInterval = '30s';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('user.profile_photo_path')
                    ->label('Avatar')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl(function ($record) {
                        return 'https://ui-avatars.com/api/?name=' . urlencode($record->user->name) . '&color=FF8C00&background=FFF4E6';
                    }),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Name')
                    ->weight('semibold')
                    ->size('base')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pizza_order')
                    ->label('🍕 Pizza Order')
                    ->wrap()
                    ->lineClamp(3)
                    ->tooltip(fn($state) => $state)
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Pizza order copied!')
                    ->getStateUsing(function ($record) {
                        return $record->pizza_order ?: 'No order yet';
                    })
                    ->color(fn($state) => $state === 'No order yet' ? 'gray' : 'primary'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Order Updated')
                    ->dateTime('M j, g:i A')
                    ->since()
                    ->color('gray')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('user.discord_id')
                    ->label('Discord')
                    ->badge()
                    ->getStateUsing(fn($record) => $record->user->discord_id ? 'Connected' : 'Not Connected')
                    ->color(fn($state) => $state === 'Connected' ? 'success' : 'gray')
                    ->icon(fn($state) => $state === 'Connected' ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('order_length')
                    ->label('Order Length')
                    ->getStateUsing(fn($record) => $record->pizza_order ? strlen($record->pizza_order) . ' chars' : '-')
                    ->color('gray')
                    ->size('sm')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('has_order')
                    ->label('Order Status')
                    ->options([
                        'with_order' => 'Has Pizza Order',
                        'without_order' => 'No Pizza Order',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!$data['value']) {
                            return $query;
                        }

                        return match ($data['value']) {
                            'with_order' => $query->whereNotNull('pizza_order')->where('pizza_order', '!=', ''),
                            'without_order' => $query->where(function ($q) {
                                $q->whereNull('pizza_order')->orWhere('pizza_order', '=', '');
                            }),
                            default => $query,
                        };
                    }),

                Tables\Filters\SelectFilter::make('discord_connected')
                    ->label('Discord Status')
                    ->options([
                        'connected' => 'Discord Connected',
                        'not_connected' => 'Discord Not Connected',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!$data['value']) {
                            return $query;
                        }

                        return match ($data['value']) {
                            'connected' => $query->whereHas('user', fn($q) => $q->whereNotNull('discord_id')),
                            'not_connected' => $query->whereHas('user', fn($q) => $q->whereNull('discord_id')),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view_full_order')
                    ->label('View Full Order')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->modalHeading(fn($record) => $record->user->name . "'s Pizza Order")
                    ->modalContent(function ($record) {
                        if (!$record->pizza_order) {
                            return view('filament.widgets.no-pizza-order');
                        }

                        return view('filament.widgets.pizza-order-detail', [
                            'order' => $record->pizza_order,
                            'user' => $record->user,
                            'updated_at' => $record->updated_at
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->heading('🍕 Pizza Orders')
            ->description('Pizza orders from participants (ordered by most recent)')
            ->emptyStateHeading('No pizza orders yet!')
            ->emptyStateDescription('Users can submit pizza orders using the Discord bot with /pizza command')
            ->emptyStateIcon('heroicon-o-face-frown')
            ->striped()
            ->paginated([10, 25, 50, 'all'])
            ->poll('30s');
    }

    protected function getTableQuery(): Builder
    {
        $currentEdition = Edition::where('year', now()->year)->first();

        if (!$currentEdition) {
            return Signup::query()->whereRaw('1 = 0'); // Return empty query
        }

        return Signup::with(['user'])
            ->where('edition_id', $currentEdition->id)
            ->where('confirmed', true)
            ->orderBy('updated_at', 'desc');
    }
}
