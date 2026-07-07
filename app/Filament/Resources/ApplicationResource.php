<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationResource\Pages;
use App\Models\Application;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Данные заявки')
                    ->schema([
                        Forms\Components\KeyValue::make('data')
                            ->label('Заполненные поля')
                            ->disabled(),
                    ]),
                Forms\Components\Section::make('Обработка')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'new'        => 'Новая',
                                'processing' => 'В работе',
                                'approved'   => 'Одобрена',
                                'rejected'   => 'Отклонена',
                            ])->required(),
                        Forms\Components\Textarea::make('admin_comment')
                            ->label('Комментарий для потребителя'),
                    ]),
                Forms\Components\Section::make('Обработка')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'new'        => 'Новая',
                                'processing' => 'В работе',
                                'approved'   => 'Одобрена',
                                'rejected'   => 'Отклонена',
                            ])->required(),

                        // Выбор тарифа
                        Forms\Components\Select::make('tariff_id')
                            ->label('Назначить тариф')
                            ->options(\App\Models\Tariff::all()->pluck('name', 'id'))
                            ->searchable()
                            ->helperText('Выберите тариф, который будет закреплен за потребителем при одобрении'),

                        Forms\Components\Textarea::make('admin_comment')
                            ->label('Комментарий для потребителя'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Потребитель'),
                Tables\Columns\TextColumn::make('template.title')->label('Тип заявки'),
                Tables\Columns\SelectColumn::make('status')
                    ->label('Статус')
                    ->options([
                        'new'        => 'Новая',
                        'processing' => 'В работе',
                        'approved'   => 'Одобрена',
                        'rejected'   => 'Отклонена',
                    ]),
                Tables\Columns\TextColumn::make('created_at')->label('Дата подачи заявления')->dateTime(),
            ])
            ->filters([
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListApplications ::route('/'),
            'create' => Pages\CreateApplication::route('/create'),
            'edit'   => Pages\EditApplication  ::route('/{record}/edit'),
        ];
    }
}
