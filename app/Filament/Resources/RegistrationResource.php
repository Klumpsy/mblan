<?php

namespace App\Filament\Resources;

use App\Models\Registration;
use App\Models\Edition;
use App\Models\User;
use App\Notifications\RegistrationApproved;
use App\Notifications\RegistrationRejected;
use Illuminate\Support\Facades\Notification;

use App\Filament\Resources\ScheduleResource\Pages;
use App\Filament\Resources\ScheduleResource\RelationManagers;
use App\Filament\Resources\ScheduleResource\RelationManagers\GamesRelationManager;
use App\Models\Schedule;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RegistrationDetailResource extends Resource
{
    protected static ?string $model = Registration::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Edition Registrations';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make([
                    Forms\Components\Select::make('edition_id')
                        ->label('Edition')
                        ->relationship('edition', 'name')
                        ->required()
                        ->searchable(),

                    Forms\Components\Select::make('user_id')
                        ->label('User')
                        ->relationship('user', 'name')
                        ->required()
                        ->searchable(),

                    Forms\Components\CheckboxList::make('attendance_days')
                        ->label('Attendance Days')
                        ->required()
                        ->options(function (callable $get) {
                            $editionId = $get('edition_id');
                            if (!$editionId) return [];

                            $edition = Edition::find($editionId);
                            if (!$edition) return [];

                            $scheduleDays = $edition->schedules()
                                ->selectRaw('DATE(start_time) as day')
                                ->distinct()
                                ->orderBy('day')
                                ->pluck('day')
                                ->toArray();

                            $days = [];
                            foreach ($scheduleDays as $day) {
                                $date = new \DateTime($day);
                                $dayName = strtolower($date->format('l'));
                                $formattedDate = $date->format('F j');
                                $days[$dayName] = "{$date->format('l')} ({$formattedDate})";
                            }

                            // If no schedules are set, provide default days
                            if (empty($days)) {
                                $days = [
                                    'friday' => 'Friday (Day 1)',
                                    'saturday' => 'Saturday (Day 2)',
                                    'sunday' => 'Sunday (Day 3)',
                                ];
                            }

                            return $days;
                        }),

                    Forms\Components\Checkbox::make('staying_for_camping')
                        ->label('Staying for Camping'),

                    Forms\Components\Textarea::make('dietary_requirements')
                        ->label('Dietary Requirements')
                        ->maxLength(500),

                    Forms\Components\Textarea::make('equipment')
                        ->label('Equipment')
                        ->maxLength(500),

                    Forms\Components\Textarea::make('additional_notes')
                        ->label('Additional Notes')
                        ->maxLength(1000),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->required()
                        ->options([
                            'pending' => 'Pending',
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                        ])
                        ->default('pending'),

                    Forms\Components\Checkbox::make('is_paid')
                        ->label('Is Paid')
                        ->disabled()
                        ->helperText('This is automatically updated when payment is received'),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('edition.name')
                    ->label('Edition')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),

                Tables\Columns\IconColumn::make('is_paid')
                    ->label('Paid')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('edition')
                    ->relationship('edition', 'name'),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),

                Tables\Filters\Filter::make('is_paid')
                    ->label('Paid Status')
                    ->form([
                        Forms\Components\Select::make('paid')
                            ->label('Paid Status')
                            ->options([
                                'paid' => 'Paid',
                                'unpaid' => 'Unpaid',
                            ])
                            ->placeholder('All'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['paid'] === 'paid', function (Builder $query): Builder {
                            return $query->where('is_paid', true);
                        })->when($data['paid'] === 'unpaid', function (Builder $query): Builder {
                            return $query->where('is_paid', false);
                        });
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'pending')
                    ->action(function (Registration $record) {
                        $record->status = 'approved';
                        $record->save();

                        $user = User::find($record->user_id);
                        $user->notify(new RegistrationApproved($record->edition));
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x')
                    ->color('danger')
                    ->visible(fn($record) => $record->status === 'pending')
                    ->action(function (Registration $record) {
                        $record->status = 'rejected';
                        $record->save();

                        $user = User::find($record->user_id);
                        $user->notify(new RegistrationRejected($record->edition));
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('approve')
                    ->label('Approve Selected')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(function (Tables\Actions\BulkAction $action, \Illuminate\Database\Eloquent\Collection $records) {
                        foreach ($records as $record) {
                            if ($record->status === 'pending') {
                                $record->status = 'approved';
                                $record->save();

                                $user = User::find($record->user_id);
                                $user->notify(new RegistrationApproved($record->edition));
                            }
                        }

                        $action->success();
                    }),
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
            'index' => Pages\ListRegistrationDetails::route('/'),
            'create' => Pages\CreateRegistrationDetail::route('/create'),
            'edit' => Pages\EditRegistrationDetail::route('/{record}/edit'),
        ];
    }
}
