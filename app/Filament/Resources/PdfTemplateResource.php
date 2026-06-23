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
                            // ->label('HTML/Blade код шаблона')
                            ->label('HTML/Twig код шаблона')
                            ->rows(25)
                            ->required()
                            // ->helperText('Используйте {{ $переменная }} и Blade-директивы (@if, @foreach, @php)')
                            ->helperText('Используйте синтаксис Twig: {{ переменная }} для вывода, {% if %}...{% endif %} для условий. Исполнение PHP-кода (@php, eval, system) в шаблонах запрещено')
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
                                                    <code style="display: block; margin: 5px 0;"><b>{{ application_id }}</b> - номер заявки</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ full_name }}</b> - ФИО (из клиента)</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ phone }}</b> - телефон (из клиента)</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ user_email }}</b> - email</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ created_at }}</b> - дата подачи</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ client_type_name }}</b> - тип клиента</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ object_address }}</b> - полный адрес объекта ( из property )</code>
                                                </div>
                                            ')),
                                    ]),
                                Forms\Components\Tabs\Tab::make('Адреса')
                                    ->schema([
                                        Forms\Components\Placeholder::make('address_vars')
                                            ->label('Собранные адреса:')
                                            ->content(new \Illuminate\Support\HtmlString('
                                                <div style="background: #f5f5f5; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 11px;">
                                                    <code style="display:block; margin: 5px 0;"><b>{{ registration_address }}</b> - адрес регистрации</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ actual_address }}</b> - адрес фактического проживания</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ object_address_full }}</b> - адрес объекта энергоснабжения</code>
                                                </div>
                                            ')),
                                    ]),
                                Forms\Components\Tabs\Tab::make('Физлицо')
                                    ->schema([
                                        Forms\Components\Placeholder::make('individual_vars')
                                            ->label('Поля для физических лиц:')
                                            ->content(new \Illuminate\Support\HtmlString('
                                                <div style="background: #f5f5f5; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 11px;">
                                                    <code style="display: block; margin: 5px 0;"><b>{{ last_name }}</b> - Фамилия</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ first_name }}</b> - Имя</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ middle_name }}</b> - Отчество</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ passport }}</b> - Серия и номер паспорта</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ passport_issue }}</b> - Кем выдан</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ passport_issue_date }}</b> - Дата выдачи</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ power_object }}</b> - Тип объекта</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ area }}</b> - Площадь помещения</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ max_power }}</b> - Максимальная мощность (кВт)</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ residents_count }}</b> - Кол-во проживающих</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ voltage_level }}</b> - Уровень напряжения</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ consumption_purpose }}</b> - Направления потребления</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ has_meter }}</b> - Приборы учета</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ tariff_choice }}</b> - Тариф</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ appeal_reason }}</b> - Причина обращения</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ payment_delivery }}</b> - Способ доставки платежек</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ notification_delivery }}</b> - Способ доставки уведомлений</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ consent }}</b> - Согласие на ПДн (Да/Нет)</code>
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


                                                    <code style="display: block; margin: 5px 0;"><b>{{ company_name }}</b> - название организации</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ inn }}</b> - ИНН</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ kpp }}</b> - КПП</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ ogrn }}</b> - ОГРН</code>
                                                    <code style="display: block; margin: 5px 0;"><b>{{ contact_person }}</b> - контактное лицо</code>
                                                </div>
                                            ')),
                                    ]),
                                Forms\Components\Tabs\Tab::make('Twig синтаксис')
                                    ->schema([
                                        Forms\Components\Placeholder::make('twig-syntax')
                                            ->label('Примеры Twig синтаксиса:')
                                            ->content(new \Illuminate\Support\HtmlString('
                                                <div style="background: #f5f5f5; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 11px;">
                                                    <p><b>Вывод переменной (по умолчанию прочерк):</b></p>
                                                    <code style="display: block; margin: 5px 0; white-space: pre;">{{ passport|default(\'___\') }}</code>@endif</code>

                                                    <p style="margin-top: 15px;"><b>Условие (показать, если заполнено):</b></p>
                                                    <code style="display: block; margin: 5px 0; white-space: pre;">{% if snils %}СНИЛС: {{ snils }}{% endif %}</code>

                                                    <p style="margin-top: 15px;"><b>Текущая дата:</b></p>
                                                    <code style="display: block; margin: 5px 0;">{{ "now"|date("d.m.Y") }}</code>

                                                    <p style="margin-top: 15px; color: #666;">Весь HTML/CSS работает как обычно. Переменные экранируются автоматически; используйте |raw только для доверенного текста (адреса).</p>
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