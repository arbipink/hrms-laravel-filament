<?php

namespace App\Filament\Resources\Attendances\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                DatePicker::make('date')
                    ->required(),
                TimePicker::make('clock_in_time'),
                TimePicker::make('clock_out_time'),
                TextInput::make('late_minutes')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('status')
                    ->required()
                    ->default('PRESENT'),
                TextInput::make('ip_address'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                Toggle::make('is_forgiven')
                    ->required(),
                TextInput::make('forgiven_by')
                    ->numeric(),
                Textarea::make('forgive_reason')
                    ->columnSpanFull(),
            ]);
    }
}
