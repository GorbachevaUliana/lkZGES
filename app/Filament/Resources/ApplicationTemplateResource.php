<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationTemplateResource\Pages;
use App\Models\ApplicationTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ApplicationTemplateResource extends Resource
{
    protected static ?string $model = ApplicationTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Шаблоны заявок';

    protected static ?string $modelLabel = 'Шаблон заявки';

    protected static ?string $pluralModelLabel = 'Шаблоны заявок';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основные настройки')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Название шаблона')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->label('URL-идентификатор')
                            ->required()
                            ->helperText('Используется в ссылке: /new-application/{slug}'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Конструктор формы')
                    ->description('Добавляйте поля в том порядке, в котором они должны отображаться в форме заявки')
                    ->schema([
                        Forms\Components\Builder::make('content')
                            ->label('Поля формы')
                            ->blocks([
                                // Текстовый блок (инструкция)
                                Forms\Components\Builder\Block::make('text_block')
                                    ->label('📝 Текст/Инструкция')
                                    ->icon('heroicon-o-document-text')
                                    ->schema([
                                        Forms\Components\RichEditor::make('body')
                                            ->label('Текст')
                                            ->required()
                                            ->columnSpanFull(),
                                        Forms\Components\Select::make('visibility')
                                            ->label('Показывать')
                                            ->options([
                                                'all' => 'Всем',
                                                'individual' => 'Только физлицам',
                                                'legal' => 'Только юрлицам',
                                            ])->default('all'),
                                    ])->columns(2),

                                // Заголовок раздела
                                Forms\Components\Builder\Block::make('section_header')
                                    ->label('🏷️ Заголовок раздела')
                                    ->icon('heroicon-o-hashtag')
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->label('Текст заголовка')
                                            ->required(),
                                        Forms\Components\Select::make('level')
                                            ->label('Размер')
                                            ->options([
                                                'h2' => 'Большой',
                                                'h3' => 'Средний',
                                                'h4' => 'Маленький',
                                            ])->default('h2'),
                                        Forms\Components\Select::make('visibility')
                                            ->label('Показывать')
                                            ->options([
                                                'all' => 'Всем',
                                                'individual' => 'Только физлицам',
                                                'legal' => 'Только юрлицам',
                                            ])->default('all'),
                                    ])->columns(3),

                                // Текстовое поле
                                Forms\Components\Builder\Block::make('input_field')
                                    ->label('✏️ Текстовое поле')
                                    ->icon('heroicon-o-pencil-square')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('key')
                                                    ->label('Ключ поля (для PDF)')
                                                    ->required()
                                                    ->helperText('Например: last_name, phone, Регион')
                                                    ->regex('/^[a-zA-Z0-9_а-яА-ЯёЁ\s]+$/')
                                                    ->rule('required'),
                                                Forms\Components\TextInput::make('label')
                                                    ->label('Заголовок для пользователя')
                                                    ->required()
                                                    ->helperText('Например: Фамилия, Телефон'),
                                            ]),
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\Select::make('type')
                                                    ->label('Тип данных')
                                                    ->options([
                                                        'text' => 'Текст',
                                                        'number' => 'Число',
                                                        'date' => 'Дата',
                                                        'email' => 'Email',
                                                        'tel' => 'Телефон',
                                                    ])->default('text'),
                                                Forms\Components\Select::make('special_format')
                                                    ->label('Формат')
                                                    ->options([
                                                        'none' => 'Обычный',
                                                        'passport' => 'Паспорт (0000 000000)',
                                                        'phone' => 'Телефон (+7)',
                                                        'snils' => 'СНИЛС',
                                                    ])->default('none'),
                                                Forms\Components\Toggle::make('is_required')
                                                    ->label('Обязательное')
                                                    ->default(false),
                                            ]),
                                        Forms\Components\Select::make('visibility')
                                            ->label('Показывать')
                                            ->options([
                                                'all' => 'Всем',
                                                'individual' => 'Только физлицам',
                                                'legal' => 'Только юрлицам',
                                            ])->default('all'),
                                    ]),

                                // Выпадающий список
                                Forms\Components\Builder\Block::make('select_field')
                                    ->label('📋 Выпадающий список')
                                    ->icon('heroicon-o-list-bullet')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('key')
                                                    ->label('Ключ поля (для PDF)')
                                                    ->required()
                                                    ->helperText('Например: object_type'),
                                                Forms\Components\TextInput::make('label')
                                                    ->label('Заголовок для пользователя')
                                                    ->required(),
                                            ]),
                                        Forms\Components\Repeater::make('options')
                                            ->label('Варианты выбора')
                                            ->schema([
                                                Forms\Components\TextInput::make('value')->label('Вариант')->required(),
                                            ])
                                            ->reorderable(true)
                                            ->columnSpanFull(),
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Toggle::make('allow_custom')
                                                    ->label('Разрешить "Другое"'),
                                                Forms\Components\Toggle::make('is_required')
                                                    ->label('Обязательное'),
                                            ]),
                                        Forms\Components\Select::make('visibility')
                                            ->label('Показывать')
                                            ->options([
                                                'all' => 'Всем',
                                                'individual' => 'Только физлицам',
                                                'legal' => 'Только юрлицам',
                                            ])->default('all'),
                                    ]),

                                // Группа чекбоксов
                                Forms\Components\Builder\Block::make('checkbox_group')
                                    ->label('☑️ Группа чекбоксов')
                                    ->icon('heroicon-o-check-circle')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('key')
                                                    ->label('Ключ поля (для PDF)')
                                                    ->required()
                                                    ->helperText('Например: services'),
                                                Forms\Components\TextInput::make('label')
                                                    ->label('Заголовок для пользователя')
                                                    ->required(),
                                            ]),
                                        Forms\Components\Repeater::make('options')
                                            ->label('Варианты')
                                            ->schema([
                                                Forms\Components\TextInput::make('value')->label('Текст')->required(),
                                            ])
                                            ->reorderable(true)
                                            ->columnSpanFull(),
                                        Forms\Components\Toggle::make('allow_multiple_custom')
                                            ->label('Разрешить добавлять свои варианты')
                                            ->helperText('Покажет кнопку "+" для добавления'),
                                        Forms\Components\Select::make('visibility')
                                            ->label('Показывать')
                                            ->options([
                                                'all' => 'Всем',
                                                'individual' => 'Только физлицам',
                                                'legal' => 'Только юрлицам',
                                            ])->default('all'),
                                    ]),
                            ])
                            ->columnSpanFull()
                            ->collapsible()
                            ->reorderableWithButtons()
                            ->blockNumbers(false),
                    ]),

                Forms\Components\Section::make('Справка по ключам')
                    ->description('Рекомендуемые ключи для стандартных полей')
                    ->schema([
                        Forms\Components\Placeholder::make('keys_info')
                            ->content(new \Illuminate\Support\HtmlString('
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; font-size: 12px;">
                                    <div style="background: #f5f5f5; padding: 10px; border-radius: 8px;">
                                        <strong>👤 Физлицо:</strong><br>
                                        <code>last_name</code> - Фамилия<br>
                                        <code>first_name</code> - Имя<br>
                                        <code>middle_name</code> - Отчество<br>
                                        <code>phone</code> - Телефон<br>
                                        <code>passport_series</code> - Серия паспорта<br>
                                        <code>passport_number</code> - Номер паспорта<br>
                                        <code>snils</code> - СНИЛС
                                    </div>
                                    <div style="background: #f5f5f5; padding: 10px; border-radius: 8px;">
                                        <strong>🏢 Юрлицо:</strong><br>
                                        <code>company_name</code> - Название<br>
                                        <code>inn</code> - ИНН<br>
                                        <code>kpp</code> - КПП<br>
                                        <code>ogrn</code> - ОГРН<br>
                                        <code>legal_address</code> - Юр. адрес<br>
                                        <code>contact_person</code> - Контактное лицо
                                    </div>
                                    <div style="background: #f5f5f5; padding: 10px; border-radius: 8px;">
                                        <strong>📍 Адрес объекта:</strong><br>
                                        <code>Регион</code> - Регион<br>
                                        <code>Район</code> - Район<br>
                                        <code>Населенный пункт</code> - Город/село<br>
                                        <code>Улица</code> - Улица<br>
                                        <code>Дом</code> - Номер дома<br>
                                        <code>Корпус</code> - Корпус<br>
                                        <code>Квартира</code> - Квартира
                                    </div>
                                </div>
                            ')),
                    ])
                    ->collapsible()
                    ->collapsed(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('URL')
                    ->copyable()
                    ->copyMessage('Скопировано!'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активность'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('view_form')
                    ->label('Открыть форму')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (ApplicationTemplate $record): string => url('/new-application/'.$record->slug))
                    ->openUrlInNewTab(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApplicationTemplates::route('/'),
            'create' => Pages\CreateApplicationTemplate::route('/create'),
            'edit' => Pages\EditApplicationTemplate::route('/{record}/edit'),
        ];
    }
}