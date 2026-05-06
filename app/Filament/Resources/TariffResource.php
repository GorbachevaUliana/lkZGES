<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TariffResource\Pages;
use App\Models\Tariff;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TariffResource extends Resource
{
    protected static ?string $model = Tariff::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название тарифа (по договору)')
                            ->placeholder('например, Сельское население')
                            ->required()
                            ->columnSpan(2),

                        Forms\Components\DatePicker::make('starts_at')
                            ->label('Дата начала действия')
                            ->required(),

                        Forms\Components\DatePicker::make('ends_at')
                            ->label('Дата окончания (если есть)'),
                    ])->columns(2),

                Forms\Components\Section::make('Стоимость (руб/кВт*ч)')
                    ->description('Цены для разных диапазонов потребления')
                    ->schema([
                        Forms\Components\TextInput::make('price_1')
                            ->label('1 диапазон (до 3900)')
                            ->numeric()
                            ->prefix('₽')
                            ->required(),

                        Forms\Components\TextInput::make('price_2')
                            ->label('2 диапазон (3901 - 6000)')
                            ->numeric()
                            ->prefix('₽')
                            ->required(),

                        Forms\Components\TextInput::make('price_3')
                            ->label('3 диапазон (свыше 6000)')
                            ->numeric()
                            ->prefix('₽')
                            ->required(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Тариф')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('price_1')
                    ->label('Диапазон 1')
                    ->money('RUB', locale: 'ru'),

                Tables\Columns\TextColumn::make('price_2')
                    ->label('Диапазон 2')
                    ->money('RUB', locale: 'ru'),

                Tables\Columns\TextColumn::make('price_3')
                    ->label('Диапазон 3')
                    ->money('RUB', locale:'ru'),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('С даты')
                    ->date('d.m.Y')
                    ->sortable(),
            ])
            ->filters([
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
            'index' => Pages\ListTariffs::route('/'),
            'create' => Pages\CreateTariff::route('/create'),
            'edit' => Pages\EditTariff::route('/{record}/edit'),
        ];
    }
}
