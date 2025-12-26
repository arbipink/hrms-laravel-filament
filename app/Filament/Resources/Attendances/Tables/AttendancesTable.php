<?php

namespace App\Filament\Resources\Attendances\Tables;

use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('clock_in_time')
                    ->time()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('clock_out_time')
                    ->time()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('late_minutes')
                    ->numeric()
                    ->sortable()
                    ->color(fn($record) => $record->late_minutes > 0 && !$record->is_forgiven ? 'danger' : null),
                TextColumn::make('status')
                    ->searchable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'ABSENT' => 'danger',
                        'LATE' => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('ip_address')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                IconColumn::make('is_forgiven')
                    ->label('Forgiven')
                    ->boolean()
                    ->action(
                        Action::make('toggleForgiveness')
                            ->visible(fn() => Auth::user()?->isAdmin())
                            ->modalHeading(fn($record) => $record->is_forgiven 
                                ? 'Unmark as Forgiven' 
                                : 'Forgive Lateness'
                            )
                            ->requiresConfirmation(fn($record) => $record->is_forgiven)
                            ->schema(fn($record) => $record->is_forgiven ? [] : [
                                Textarea::make('forgive_reason')
                                    ->label('Reason for forgiveness')
                                    ->required()
                                    ->rows(3),
                            ])
                            ->action(function ($record, array $data) {
                                if ($record->is_forgiven) {
                                    $record->update([
                                        'is_forgiven' => false,
                                        'forgiven_by' => null,
                                        'forgive_reason' => null,
                                    ]);
                                } else {
                                    $record->update([
                                        'is_forgiven' => true,
                                        'forgiven_by' => Auth::id(),
                                        'forgive_reason' => $data['forgive_reason'],
                                    ]);
                                }
                            })
                    ),
                TextColumn::make('forgiven_by')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('period')
                    ->label('Period')
                    ->options([
                        'today' => 'Today',
                        '3_days' => 'Last 3 Days',
                        'week' => 'Last 7 Days',
                    ])
                    ->default('today')
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'];
                        if ($value === 'today') {
                            $query->whereDate('date', Carbon::today());
                        } elseif ($value === '3_days') {
                            $query->where('date', '>=', Carbon::now()->subDays(3));
                        } elseif ($value === 'week') {
                            $query->where('date', '>=', Carbon::now()->subWeek());
                        }
                    }),

                SelectFilter::make('status_filter')
                    ->label('Attendance Issues')
                    ->multiple()
                    ->options([
                        'LATE' => 'Late',
                        'ABSENT' => 'Absent',
                    ])
                    ->default(['LATE', 'ABSENT'])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['values'])) {
                            $query->whereIn('status', $data['values']);
                        }
                    }),
            ])
            ->persistFiltersInSession()
            ->recordActions([
                //
            ]);
    }
}