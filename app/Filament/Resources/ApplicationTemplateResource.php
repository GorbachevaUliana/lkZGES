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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),

                Forms\Components\TextInput::make('slug')->required(),

                Forms\Components\Builder::make('content')
                    ->label('Конструктор формы')
                    ->blocks([
                        // Блок 1: Инструкция
                        Forms\Components\Builder\Block::make('text_block')
                            ->label('Текстовый блок (инструкция)')
                            ->schema([
                                Forms\Components\RichEditor::make('body')->label('Содержание')->required(),
                                Forms\Components\Select::make('visibility')
                                    ->label('Видимость')
                                    ->options([
                                        'all' => 'Всем',
                                        'individual' => 'Физическим лицам',
                                        'legal' => 'Юридическим лицам',
                                    ])->default('all'),
                            ]),

                        // Блок 3: Заголовки разделов
                        Forms\Components\Builder\Block::make('section_header')
                            ->label('Заголовок раздела')
                            ->icon('heroicon-o-hashtag')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Текст заголовка')
                                    ->required(),
                                Forms\Components\Select::make('level')
                                    ->label('Уровень')
                                    ->options([
                                        'h2' => 'Главный заголовок',
                                        'h3' => 'Подзаголовок',
                                    ])->default('h2'),
                                Forms\Components\Select::make('visibility')
                                    ->label('Видимость')
                                    ->options([
                                        'all' => 'Всем',
                                        'individual' => 'Физическим лицам',
                                        'legal' => 'Юридическим лицам',
                                    ])->default('all'),
                            ]),

                        // Блок 2: Поле ввода
                        Forms\Components\Builder\Block::make('input_field')
                            ->label('Текстовое поле')
                            ->schema([
                                Forms\Components\TextInput::make('label')->label('Заголовок поля')->required(),
                                Forms\Components\Select::make('type')
                                    ->label('Тип данных')
                                    ->options([
                                        'text' => 'Текст',
                                        'number' => 'Число',
                                        'date' => 'Дата',
                                    ])->default('text'),
                                // Опция маски для паспорта
                                Forms\Components\Select::make('special_format')
                                    ->label('Специальный формат')
                                    ->options([
                                        'none' => 'Нет',
                                        'passport' => 'Серия и номер паспорта',
                                    ])->default('none'),
                                Forms\Components\Toggle::make('is_required')->label('Обязательное'),
                                Forms\Components\Select::make('visibility')
                                    ->label('Видимость')
                                    ->options([
                                        'all' => 'Всем',
                                        'individual' => 'Физическим лицам',
                                        'legal' => 'Юридическим лицам',
                                    ])->default('all'),
                            ]),

                        // Выпадающий список
                        Forms\Components\Builder\Block::make('select_field')
                            ->label('Выпадающий список')
                            ->icon('heroicon-o-list-bullet')
                            ->schema([
                                Forms\Components\TextInput::make('label')->label('Заголовок списка')->required(),
                                // Настройка опций
                                Forms\Components\Repeater::make('options')
                                    ->label('Варианты выбора')
                                    ->schema([
                                        Forms\Components\TextInput::make('value')->label('Значение')->required(),
                                    ])->reorderable(true),
                                // Флаг "Разрешить свой вариант"
                                Forms\Components\Toggle::make('allow_custom')
                                    ->label('Разрешить ввод своего варианта ("Другое")'),
                                Forms\Components\Toggle::make('is_required')->label('Обязательное'),
                                Forms\Components\Select::make('visibility')
                                    ->label('Видимость')
                                    ->options([
                                        'all' => 'Всем',
                                        'individual' => 'Физическим лицам',
                                        'legal' => 'Юридическим лицам',
                                    ])->default('all'),
                            ]),

                        // Чекбоксы с динамическим добавлением
                        Forms\Components\Builder\Block::make('checkbox_group')
                            ->label('Группа чекбоксов')
                            ->icon('heroicon-o-check-circle')
                            ->schema([
                                Forms\Components\TextInput::make('label')->label('Заголовок группы')->required(),
                                Forms\Components\Repeater::make('options')
                                    ->label('Предустановленные варианты')
                                    ->schema([
                                        Forms\Components\TextInput::make('value')->label('Текст чекбокса')->required(),
                                    ]),
                                // Флаг для включения кнопки "+" на фронте
                                Forms\Components\Toggle::make('allow_multiple_custom')
                                    ->label('Разрешить пользователю добавлять свои пункты (+)')
                                    ->helperText('Клиент сможет нажать "Ещё" и вписать свои значения'),
                                Forms\Components\Select::make('visibility')
                                    ->label('Видимость')
                                    ->options([
                                        'all' => 'Всем',
                                        'individual' => 'Физическим лицам',
                                        'legal' => 'Юридическим лицам',
                                    ])->default('all'),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Название')->searchable(),
                Tables\Columns\TextColumn::make('slug')->label('Ссылка'),
                Tables\Columns\IconColumn::make('is_active')->label('Активен')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->label('Создан')->dateTime(),
            ])
            ->filters([
                //
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

// Forms\Components\Builder::make('content')
//                     ->label('Конструктор формы')
//                     ->blocks([
//                         // Блок 1: Текст
//                         Forms\Components\Builder\Block::make('text_block')
//                             ->label('Текстовый блок (инструкция)')
//                             ->schema([
//                                 Forms\Components\RichEditor::make('body')
//                                     ->label('Содержание текста')
//                                     ->required(),
//                             ]),

//                         // Блок 2: Поле ввода
//                         Forms\Components\Builder\Block::make('input_field')
//                             ->label('Поле формы')
//                             ->schema([
//                                 Forms\Components\TextInput::make('label')
//                                     ->label('Заголовок поля')
//                                     ->required(),
//                                 Forms\Components\Select::make('type')
//                                     ->label('Тип данных')
//                                     ->options([
//                                         'text' => 'Текст',
//                                         'textarea' => 'Длинный текст',
//                                         'number' => 'Число',
//                                         'file' => 'Файл',
//                                         'date' => 'Дата',
//                                     ])->required(),
//                                 Forms\Components\Select::make('visibility')
//                                     ->label('Видимость')
//                                     ->options([
//                                         'all' => 'Всем',
//                                         'individual' => 'Физическим лицам',
//                                         'legal' => 'Юридическим лицам',
//                                     ])->default('all'),
//                                 Forms\Components\Toggle::make('is_required')
//                                     ->label('Обязательное'),
//                             ]),
//                     ])
