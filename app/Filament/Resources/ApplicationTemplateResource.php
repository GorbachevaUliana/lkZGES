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

                                //Динамический блок
                                Forms\Components\Builder\Block::make('dynamic_input')
                                    ->label('Динамический список')
                                    ->icon('heroicon-o-arrows-pointing-out')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('key')
                                                    ->label('Ключ поля (для PDF)')
                                                    ->required()
                                                    ->helperText('Например: notification_delivery'),
                                                Forms\Components\TextInput::make('label')
                                                    ->label('Заголовок для пользователя')
                                                    ->required()
                                            ]),
                                        Forms\Components\Toggle::make('is_required')
                                            ->label('Обязательное')
                                            ->default(false),
                                        Forms\Components\Repeater::make('options')
                                            ->label('Варианты выбора')
                                            ->schema([
                                                Forms\Components\TextInput::make('value')
                                                    ->label('Текст варианта')
                                                    ->required()
                                                    ->columnSpanFull()
                                                    ->helperText('Например: по электронной почте'),
                                                Forms\Components\Select::make('input_type')
                                                    ->label('Тип доп.поля')
                                                    ->options([
                                                        'none' => 'Без доп.поля',
                                                        'email' => 'Электронная почта',
                                                        'phone' => 'Телефон',
                                                        'text' => 'Текст',
                                                    ])
                                                    ->default('none')
                                                    ->live()
                                                    ->helperText('Показать поле ввода при выборе этого варианта'),
                                                Forms\Components\TextInput::make('input_label')
                                                    ->label('Подпись поля ввода')
                                                    ->visible(fn (callable $get) => $get('input_type') && $get('input_type') !== 'none')
                                                    ->columnSpanFull(),
                                            ])
                                            ->reorderable(true)
                                            ->columnSpanFull()
                                            ->helperText('Каждый вариант может иметь связанное поле для ввода данных'),
                                        Forms\Components\Select::make('visibility')
                                            ->label('Показывать')
                                            ->options([
                                                'all' => 'Всем',
                                                'individual' => 'Только физлицам',
                                                'legal' => 'Только юрлицам',
                                            ])->default('all')
                                    ]),

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
                                        Forms\Components\Grid::make(4)
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
                                                        'range_numbers' => 'Диапазон чисел (0 - 0)',
                                                        'range_date' => 'Диапазон дат (ДД.ММ.ГГГГ - ДД.ММ.ГГГГ)',
                                                    ])->default('none'),
                                                Forms\Components\Toggle::make('is_required')
                                                    ->label('Обязательное')
                                                    ->default(false),
                                                Forms\Components\Toggle::make('is_readonly')
                                                    ->label('Только чтение')
                                                    ->default(false)
                                                    ->live()
                                                    ->helperText('Пользователь не сможет редактировать это поле'),
                                            ]),
                                        Forms\Components\TextInput::make('default_value')
                                            ->label('Значение по умолчанию')
                                            ->helperText('Значение для поля с правами на чтение')
                                            ->columnSpanFull()
                                            ->visible(fn (callable $get) => $get('is_readonly')),
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
                                        Forms\Components\Toggle::make('is_required')
                                            ->label('Обязательное')
                                            ->default(false),
                                        Forms\Components\Select::make('visibility')
                                            ->label('Показывать')
                                            ->options([
                                                'all' => 'Всем',
                                                'individual' => 'Только физлицам',
                                                'legal' => 'Только юрлицам',
                                            ])->default('all'),
                                    ]),

                                // Блок загрузки файлов
                                Forms\Components\Builder\Block::make('file_upload')
                                    ->label('📎 Загрузка файлов')
                                    ->icon('heroicon-o-paper-clip')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('key')
                                                    ->label('Ключ поля (для PDF)')
                                                    ->required()
                                                    ->helperText('Например: documents, passport_scan')
                                                    ->regex('/^[a-zA-Z0-9_а-яА-ЯёЁ\s]+$/'),
                                                Forms\Components\TextInput::make('label')
                                                    ->label('Заголовок для пользователя')
                                                    ->required()
                                                    ->helperText('Например: Документы, Скан паспорта'),
                                            ]),
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Toggle::make('is_required')
                                                    ->label('Обязательное')
                                                    ->default(false),
                                                Forms\Components\Toggle::make('allow_multiple')
                                                    ->label('Разрешить несколько файлов')
                                                    ->default(true)
                                                    ->helperText('Покажет кнопку "+" для добавления'),
                                            ]),
                                        Forms\Components\KeyValue::make('accepted_types')
                                            ->label('Разрешённые типы файлов')
                                            ->keyLabel('Тип')
                                            ->valueLabel('Описание')
                                            ->default([
                                                'pdf' => 'PDF документы',
                                                'jpg' => 'Изображения JPG',
                                                'png' => 'Изображения PNG',
                                                'doc' => 'Word документы',
                                                'docx' => 'Word документы',
                                            ])
                                            ->helperText('Оставьте пустым для разрешения всех типов')
                                            ->columnSpanFull(),
                                        Forms\Components\TextInput::make('max_size')
                                            ->label('Макс. размер файла (МБ)')
                                            ->numeric()
                                            ->default(10)
                                            ->minValue(1)
                                            ->maxValue(50),
                                        Forms\Components\TextInput::make('max_files')
                                            ->label('Макс. количество файлов')
                                            ->numeric()
                                            ->default(5)
                                            ->minValue(1)
                                            ->maxValue(20)
                                            ->visible(fn (callable $get) => $get('allow_multiple')),
                                        Forms\Components\Textarea::make('helper_text')
                                            ->label('Подсказка под полем')
                                            ->rows(2)
                                            ->placeholder('Например: Загрузите скан паспорта или фото документа')
                                            ->columnSpanFull(),
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
                                        <code>passport</code> - Серия и номер паспорта<br>
                                        <code>passport_issue</code> - Кем выдан паспорт<br>
                                        <code>passport_issue_date</code> - Дата выдачи
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