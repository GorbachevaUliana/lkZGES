<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PdfTemplateResource\Pages;
use App\Models\PdfTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PdfTemplateResource extends Resource
{
    protected static ?string $model = PdfTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Шаблоны PDF';

    protected static ?string $modelLabel = 'Шаблон PDF';

    protected static ?string $pluralModelLabel = 'Шаблоны PDF';

    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основные настройки')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название шаблона')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('slug')
                            ->label('Слаг')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Например: application_individual')
                            ->columnSpan(2),

                        Forms\Components\Select::make('client_type')
                            ->label('Тип клиента')
                            ->options(PdfTemplate::getClientTypes())
                            ->default('individual')
                            ->required(),

                        Forms\Components\Select::make('document_type')
                            ->label('Тип документа')
                            ->options(PdfTemplate::getDocumentTypes())
                            ->default('application')
                            ->required(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true)
                            ->helperText('Неактивные шаблоны не используются'),
                    ])->columns(2),

                Forms\Components\Section::make('Содержимое шаблона')
                    ->schema([
                        Forms\Components\Textarea::make('content')
                            ->label('HTML/Blade код шаблона')
                            ->rows(25)
                            ->required()
                            ->helperText('Используйте {{ $переменная }} и Blade-директивы (@if, @foreach, @php)')
                            ->columnSpanFull()
                            ->extraAttributes(['style' => 'font-family: monospace; font-size: 12px;']),
                    ]),

                Forms\Components\Section::make('Справка по переменным')
                    ->schema([
                        Forms\Components\Tabs::make('VariablesTabs')
                            ->tabs([
                                Forms\Components\Tabs\Tab::make('Системные')
                                    ->schema([
                                        Forms\Components\Placeholder::make('system_vars')
                                            ->label('Автоматически заполняемые поля:')
                                            ->content(new \Illuminate\Support\HtmlString('
                                                <div style="background: #f5f5f5; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 11px;">
                                                    <code style="display: block; margin: 5px 0;"><b>{{ $application_id }}</b> - номер заявки</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ $full_name }}</b> - ФИО (из клиента)</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ $phone }}</b> - телефон (из клиента)</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ $user_email }}</b> - email</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ $created_at }}</b> - дата подачи</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ $address }}</b> - полный адрес (собирается автоматически)</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ $client_type_name }}</b> - тип клиента</code>
                                                </div>
                                            ')),
                                    ]),
                                Forms\Components\Tabs\Tab::make('Адрес')
                                    ->schema([
                                        Forms\Components\Placeholder::make('address_vars')
                                            ->label('Составные части адреса (через $data[\'ключ\']):')
                                            ->content(new \Illuminate\Support\HtmlString('
                                                <div style="background: #f5f5f5; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 11px;">
                                                    <p style="margin-bottom: 10px; color: #666;">Если в форме есть поля адреса с такими ключами:</p>
                                                    <code style="display: block; margin: 5px 0;"><b>$data[\'Регион\']</b> - регион</code>
                                                    <code style="display: block; margin: 5px 0;"><b>$data[\'Район\']</b> - район</code>
                                                    <code style="display: block; margin: 5px 0;"><b>$data[\'Населенный пункт\']</b> - город/село</code>
                                                    <code style="display: block; margin: 5px 0;"><b>$data[\'Улица\']</b> - улица</code>
                                                    <code style="display: block; margin: 5px 0;"><b>$data[\'Дом\']</b> - номер дома</code>
                                                    <code style="display: block; margin: 5px 0;"><b>$data[\'Корпус\']</b> - корпус</code>
                                                    <code style="display: block; margin: 5px 0;"><b>$data[\'Квартира\']</b> - квартира</code>
                                                </div>
                                            ')),
                                    ]),
                                Forms\Components\Tabs\Tab::make('Физлицо')
                                    ->schema([
                                        Forms\Components\Placeholder::make('individual_vars')
                                            ->label('Поля для физических лиц:')
                                            ->content(new \Illuminate\Support\HtmlString('
                                                <div style="background: #f5f5f5; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 11px;">
                                                    <code style="display: block; margin: 5px 0;"><b>$data[\'last_name\']</b> - Фамилия</code>
                                                    <code style="display: block; margin: 5px 0;"><b>$data[\'first_name\']</b> - Имя</code>
                                                    <code style="display: block; margin: 5px 0;"><b>$data[\'middle_name\']</b> - Отчество</code>
                                                    <code style="display: block; margin: 5px 0;"><b>$data[\'passport_series\']</b> - Серия паспорта</code>
                                                    <code style="display: block; margin: 5px 0;"><b>$data[\'passport_number\']</b> - Номер паспорта</code>
                                                    <code style="display: block; margin: 5px 0;"><b>$data[\'snils\']</b> - СНИЛС</code>
                                                </div>
                                            ')),
                                    ]),
                                Forms\Components\Tabs\Tab::make('Юрлицо')
                                    ->schema([
                                        Forms\Components\Placeholder::make('legal_vars')
                                            ->label('Поля для юридических лиц:')
                                            ->content(new \Illuminate\Support\HtmlString('
                                                <div style="background: #f5f5f5; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 11px;">
                                                    <code style="display: block; margin: 5px 0;"><b>{{ $company_name }}</b> - название организации</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ $inn }}</b> - ИНН</code>
                                                    <code style="display: block; margin: 5px 0;"><b>$data[\'kpp\']</b> - КПП</code>
                                                    <code style="display: block; margin: 5px 0;"><b>$data[\'ogrn\']</b> - ОГРН</code>
                                                    <code style="display: block; margin: 5px 0;"><b>$data[\'legal_address\']</b> - юридический адрес</code>
                                                    <code style="display: block; margin: 5px 0;"><b>$data[\'actual_address\']</b> - фактический адрес</code>
                                                    <code style="display: block; margin: 5px 0;"><b>$data[\'contact_person\']</b> - контактное лицо</code>
                                                </div>
                                            ')),
                                    ]),
                                Forms\Components\Tabs\Tab::make('Доп. поля')
                                    ->schema([
                                        Forms\Components\Placeholder::make('custom_vars')
                                            ->label('Пользовательские поля из формы:')
                                            ->content(new \Illuminate\Support\HtmlString('
                                                <div style="background: #f5f5f5; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 11px;">
                                                    <p style="margin-bottom: 10px;">Любые поля, добавленные в конструкторе формы с ключом <b>key</b>:</p>
                                                    <code style="display: block; margin: 5px 0;"><b>$data[\'ваш_ключ\']</b> - значение поля</code>
                                                    <p style="margin-top: 15px; color: #666;">Примеры:</p>
                                                    <code style="display: block; margin: 5px 0;"><b>$data[\'object_type\']</b> - Тип объекта</code>
                                                    <code style="display: block; margin: 5px 0;"><b>$data[\'power_capacity\']</b> - Мощность</code>
                                                    <code style="display: block; margin: 5px 0;"><b>$data[\'square\']</b> - Площадь</code>
                                                </div>
                                            ')),
                                    ]),
                                Forms\Components\Tabs\Tab::make('Blade')
                                    ->schema([
                                        Forms\Components\Placeholder::make('blade_syntax')
                                            ->label('Примеры Blade-синтаксиса:')
                                            ->content(new \Illuminate\Support\HtmlString('
                                                <div style="background: #f5f5f5; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 11px;">
                                                    <p><b>Условие:</b></p>
                                                    <code style="display: block; margin: 5px 0; white-space: pre;">@if(!empty($data[\'snils\']))
    &lt;tr&gt;&lt;td&gt;СНИЛС&lt;/td&gt;&lt;td&gt;{{ $data[\'snils\'] }}&lt;/td&gt;&lt;/tr&gt;
@endif</code>

                                                    <p style="margin-top: 15px;"><b>Цикл по всем полям:</b></p>
                                                    <code style="display: block; margin: 5px 0; white-space: pre;">@foreach($data as $key => $value)
    &lt;p&gt;{{ $key }}: {{ $value }}&lt;/p&gt;
@endforeach</code>
                                                </div>
                                            ')),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('client_type')
                    ->label('Тип клиента')
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

                Tables\Columns\TextColumn::make('document_type')
                    ->label('Тип документа')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'application' => 'Заявка',
                        'contract' => 'Договор',
                        'other' => 'Другое',
                        default => $state,
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновлён')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('client_type')
                    ->label('Тип клиента')
                    ->options(PdfTemplate::getClientTypes()),

                Tables\Filters\SelectFilter::make('document_type')
                    ->label('Тип документа')
                    ->options(PdfTemplate::getDocumentTypes()),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активность')
                    ->placeholder('Все')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('duplicate')
                    ->label('Дублировать')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function (PdfTemplate $record) {
                        $new = $record->replicate();
                        $new->name = $record->name . ' (копия)';
                        $new->slug = $record->slug . '-copy-' . time();
                        $new->is_active = false;
                        $new->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListPdfTemplates::route('/'),
            'create' => Pages\CreatePdfTemplate::route('/create'),
            'edit' => Pages\EditPdfTemplate::route('/{record}/edit'),
        ];
    }
}