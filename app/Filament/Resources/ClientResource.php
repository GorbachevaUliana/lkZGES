<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Client;
use App\Models\Property;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Потребители';

    protected static ?string $modelLabel = 'Потребитель';

    protected static ?string $pluralModelLabel = 'Потребители';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Данные потребителя')
                    ->schema([
                        Forms\Components\Select::make('client_type')
                            ->label('Тип клиента')
                            ->options([
                                'individual' => 'Физическое лицо',
                                'legal' => 'Юридическое лицо',
                            ])
                            ->default('individual')
                            ->required()
                            ->live(),

                        Forms\Components\TextInput::make('last_name')
                            ->label('Фамилия')
                            ->required(fn (callable $get) => $get('client_type') === 'individual')
                            ->visible(fn (callable $get) => $get('client_type') === 'individual'),

                        Forms\Components\TextInput::make('first_name')
                            ->label('Имя')
                            ->required(fn (callable $get) => $get('client_type') === 'individual')
                            ->visible(fn (callable $get) => $get('client_type') === 'individual'),

                        Forms\Components\TextInput::make('middle_name')
                            ->label('Отчество')
                            ->visible(fn (callable $get) => $get('client_type') === 'individual'),

                        Forms\Components\TextInput::make('company_name')
                            ->label('Название организации')
                            ->required(fn (callable $get) => $get('client_type') === 'legal')
                            ->visible(fn (callable $get) => $get('client_type') === 'legal'),

                        Forms\Components\TextInput::make('inn')
                            ->label('ИНН')
                            ->visible(fn (callable $get) => $get('client_type') === 'legal'),

                        Forms\Components\TextInput::make('phone')
                            ->label('Телефон')
                            ->tel()
                            ->required(),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email(),
                    ])->columns(2),

                Forms\Components\Section::make('Тариф')
                    ->schema([
                        Forms\Components\Select::make('tariff_category')
                            ->label('Категория тарифа')
                            ->options(\App\Models\Tariff::pluck('name', 'name')->toArray())
                            ->searchable()
                            ->preload(),
                    ]),

                // Показываем связанные объекты (properties)
                Forms\Components\Section::make('Объекты потребления (Лицевые счета)')
                    ->schema([
                        Forms\Components\Repeater::make('properties')
                            ->relationship('properties')
                            ->schema([
                                Forms\Components\TextInput::make('account_number')
                                    ->label('Лицевой счёт')
                                    ->unique(ignoreRecord: true),

                                Forms\Components\Textarea::make('address')
                                    ->label('Адрес')
                                    ->rows(2)
                                    ->columnSpan(2),

                                Forms\Components\Select::make('status')
                                    ->label('Статус')
                                    ->options([
                                        'pending' => 'Ожидает активации',
                                        'active' => 'Активен',
                                        'inactive' => 'Неактивен',
                                    ])
                                    ->default('pending'),
                            ])
                            ->columns(3)
                            ->disabled() // Только для просмотра, редактирование через заявки
                            ->deletable(false)
                            ->addable(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('display_name')
                    ->label('Потребитель')
                    ->getStateUsing(function (Client $record): string {
                        if ($record->client_type === 'legal') {
                            return $record->company_name ?? '—';
                        }
                        return trim(($record->last_name ?? '') . ' ' . ($record->first_name ?? '') . ' ' . ($record->middle_name ?? ''));
                    })
                    ->searchable(['last_name', 'first_name', 'middle_name', 'company_name']),

                Tables\Columns\TextColumn::make('client_type')
                    ->label('Тип')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'individual' => 'Физ. лицо',
                        'legal' => 'Юр. лицо',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'individual' => 'success',
                        'legal' => 'info',
                        default => 'gray',
                    }),

                // ИСПРАВЛЕНО: Адрес из properties
                Tables\Columns\TextColumn::make('address')
                    ->label('Адрес')
                    ->getStateUsing(function (Client $record): string {
                        $property = $record->properties()->where('status', 'active')->first();
                        return $property?->address ?? 'Не указан';
                    })
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) > 30) {
                            return $state;
                        }
                        return null;
                    }),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable(),

                // ИСПРАВЛЕНО: Л/С из properties
                Tables\Columns\TextColumn::make('account_numbers')
                    ->label('Лицевые счета')
                    ->getStateUsing(function (Client $record): string {
                        $accounts = $record->properties()
                            ->whereNotNull('account_number')
                            ->where('account_number', '!=', '')
                            ->pluck('account_number')
                            ->join(', ');
                        return $accounts ?: 'Не присвоен';
                    }),

                // ИСПРАВЛЕНО: Статус из properties
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->getStateUsing(function (Client $record): string {
                        $hasActive = $record->properties()->where('status', 'active')
                            ->whereNotNull('account_number')
                            ->where('account_number', '!=', '')
                            ->exists();

                        if ($hasActive) {
                            return 'Активен';
                        }

                        $hasPending = $record->properties()->where('status', 'pending')->exists();
                        return $hasPending ? 'Ожидает' : 'Неактивен';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Активен' => 'success',
                        'Ожидает' => 'warning',
                        'Неактивен' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('client_type')
                    ->label('Тип клиента')
                    ->options([
                        'individual' => 'Физическое лицо',
                        'legal' => 'Юридическое лицо',
                    ]),

                Tables\Filters\Filter::make('active')
                    ->label('Активные')
                    ->query(fn (Builder $query): Builder => $query->whereHas('properties', fn ($q) => $q->where('status', 'active'))),

                Tables\Filters\Filter::make('pending')
                    ->label('Ожидают активации')
                    ->query(fn (Builder $query): Builder => $query->whereHas('properties', fn ($q) => $q->where('status', 'pending'))),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with('properties'));
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
