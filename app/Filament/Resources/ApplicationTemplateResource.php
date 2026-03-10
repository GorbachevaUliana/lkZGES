<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationTemplateResource\Pages;
use App\Filament\Resources\ApplicationTemplateResource\RelationManagers;
use App\Models\ApplicationTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use function PHPUnit\Framework\callback;

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
                    ->blocks([
                        Forms\Components\Builder\Block::make('text_block')
                            ->label('Текстовый блок (инструкция)')
                            ->schema([
                                Forms\Components\RichEditor::make('body')
                                    ->label('Содержание текста')
                                    ->required(),
                            ]),

                        Forms\Components\Builder\Block::make('input_field')
                            ->label('Поле формы')
                            ->schema([
                                Forms\Components\TextInput::make('label')
                                    ->label('Заголовок поля')
                                    ->required(),
                                Forms\Components\Select::make('type')
                                    ->label('Тип данных')
                                    ->options([
                                        'text' => 'Текст (одна строка)',
                                        'textarea' => 'Длинный текст',
                                        'number' => 'Число',
                                        'file' => 'Загрузка документа (PDF/JPG)',
                                        'date' => 'Дата',
                                    ])->required(),
                                Forms\Components\Toggle::make('is_required')
                                    ->label('Обязательно для заполнения'),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->collapsible()
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
