<?php

namespace Database\Seeders;

use App\Models\ApplicationTemplate;
use Illuminate\Database\Seeder;

class ApplicationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Сидер для шаблона заявки на заключение договора энергоснабжения
     */
    public function run(): void
    {
        ApplicationTemplate::updateOrCreate(
            ['slug' => 'application-individual'],
            [
                'title' => 'Заявление на заключение договора энергоснабжения (физическое лицо)',
                'client_type' => 'individual',
                'is_active' => true,
                'content' => [
                    [
                        'type' => 'text_block',
                        'data' => [
                            'body' => '<p>Добро пожаловать! Заполните форму для создания заявления на заключение договора энергоснабжения</p>',
                            'visibility' => 'all'
                        ]
                    ],
                    [
                        'type' => 'section_header',
                        'data' => [
                            'title' => '1. Инициалы',
                            'level' => 'h4',
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'last_name',
                            'label' => 'Фамилия',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => true,
                            'visibility' => 'individual',
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'first_name',
                            'label' => 'Имя',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => true,
                            'visibility' => 'individual',
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'middle_name',
                            'label' => 'Отчество',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => false,
                            'visibility' => 'individual',
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'section_header',
                        'data' => [
                            'title' => '2. Паспортные данные',
                            'level' => 'h4',
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'passport',
                            'label' => 'Серия и номер паспорта',
                            'type' => 'text',
                            'special_format' => 'passport',
                            'is_required' => true,
                            'visibility' => 'individual',
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'passport_issue',
                            'label' => 'Кем выдан',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => true,
                            'visibility' => 'individual',
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'passport_issue_date',
                            'label' => 'Дата выдачи',
                            'type' => 'date',
                            'special_format' => 'none',
                            'is_required' => true,
                            'visibility' => 'individual',
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'section_header',
                        'data' => [
                            'title' => '3. Адрес регистрации',
                            'level' => 'h4',
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'select_field',
                        'data' => [
                            'key' => 'region',
                            'label' => 'Регион',
                            'options' => [
                                ['value' => 'Алтайский край']
                            ],
                            'allow_custom' => false,
                            'is_required' => true,
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'district',
                            'label' => 'Район',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => false,
                            'visibility' => 'individual',
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'locality',
                            'label' => 'Населенный пункт',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => true,
                            'visibility' => 'individual',
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'street',
                            'label' => 'Улица',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => true,
                            'visibility' => 'individual',
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'house',
                            'label' => 'Дом',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => true,
                            'visibility' => 'individual',
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'corpus',
                            'label' => 'Корпус',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => false,
                            'visibility' => 'individual',
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'apartment',
                            'label' => 'Квартира',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => false,
                            'visibility' => 'individual',
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'section_header',
                        'data' => [
                            'title' => '3.1. Адрес фактического проживания',
                            'level' => 'h4',
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'select_field',
                        'data' => [
                            'key' => 'actual_region',
                            'label' => 'Регион',
                            'options' => [
                                ['value' => 'Алтайский край']
                            ],
                            'allow_custom' => true,
                            'is_required' => true,
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'actual_district',
                            'label' => 'Район',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => false,
                            'visibility' => 'individual',
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'actual_locality',
                            'label' => 'Населенный пункт',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => true,
                            'visibility' => 'individual',
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'actual_street',
                            'label' => 'Улица',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => true,
                            'visibility' => 'individual',
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'actual_house',
                            'label' => 'Дом',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => true,
                            'visibility' => 'individual',
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'actual_corpus',
                            'label' => 'Корпус',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => false,
                            'visibility' => 'all',
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'actual_apartment',
                            'label' => 'Квартира',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => false,
                            'visibility' => 'individual',
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'section_header',
                        'data' => [
                            'title' => '4. Контактная информация',
                            'level' => 'h4',
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'phone',
                            'label' => 'Телефон',
                            'type' => 'text',
                            'special_format' => 'phone',
                            'is_required' => true,
                            'visibility' => 'individual',
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'email',
                            'label' => 'Адрес электронной почты',
                            'type' => 'email',
                            'special_format' => 'none',
                            'is_required' => true,
                            'visibility' => 'individual',
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'section_header',
                        'data' => [
                            'title' => '6. Сведения об объекте энергоснабжения',
                            'level' => 'h4',
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'select_field',
                        'data' => [
                            'key' => 'power_object',
                            'label' => 'Энергопринимающие устройства планируемые к присоединению',
                            'options' => [
                                ['value' => 'Дом'],
                                ['value' => 'Гараж'],
                                ['value' => 'Дача'],
                                ['value' => 'Квартира']
                            ],
                            'allow_custom' => true,
                            'is_required' => true,
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'section_header',
                        'data' => [
                            'title' => 'Местонахождение объекта, по которому заключается договор',
                            'level' => 'h3',
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'region_object',
                            'label' => 'Регион',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => true,
                            'is_readonly' => true,
                            'default_value' => 'Алтайский край',
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'district_object',
                            'label' => 'Район',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => false,
                            'is_readonly' => false,
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'locality_object',
                            'label' => 'Населенный пункт',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => true,
                            'is_readonly' => false,
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'street_object',
                            'label' => 'Улица',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => true,
                            'is_readonly' => false,
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'house_object',
                            'label' => 'Дом',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => true,
                            'is_readonly' => false,
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'corpus_object',
                            'label' => 'Корпус',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => false,
                            'is_readonly' => false,
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'apartment_object',
                            'label' => 'Квартира',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => false,
                            'is_readonly' => false,
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'note',
                            'label' => 'Примечание',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => false,
                            'is_readonly' => false,
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'area',
                            'label' => 'Общая площадь помещения (для помещений расположенных в многоквартирном доме)',
                            'type' => 'number',
                            'special_format' => 'none',
                            'is_required' => true,
                            'is_readonly' => false,
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'residents_count',
                            'label' => 'Количество лиц, постоянно проживающих в помещении',
                            'type' => 'number',
                            'special_format' => 'none',
                            'is_required' => true,
                            'is_readonly' => false,
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'max_power',
                            'label' => 'Максимальная мощность электроприемников',
                            'type' => 'number',
                            'special_format' => 'none',
                            'is_required' => true,
                            'is_readonly' => false,
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'checkbox_group',
                        'data' => [
                            'key' => 'voltage_level',
                            'label' => 'Уровень напряжения',
                            'options' => [
                                ['value' => '220 В'],
                                ['value' => '380 В']
                            ],
                            'allow_multiple_custom' => false,
                            'visibility' => 'individual',
                            'is_required' => null
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'act_reference',
                            'label' => 'Реквизиты акта об определении границы раздела внутридомовых инженерных систем и централизованных сетей электроснабжения',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => false,
                            'is_readonly' => false,
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'checkbox_group',
                        'data' => [
                            'key' => 'consumption_purpose',
                            'label' => 'Сведения о направлениях потребления электроэнергии',
                            'options' => [
                                ['value' => 'Освещение'],
                                ['value' => 'Отопление'],
                                ['value' => 'Подогрев воды'],
                                ['value' => 'Бытовые приборы'],
                                ['value' => 'Электроплита'],
                                ['value' => 'Электроприемники для строительных работ']
                            ],
                            'allow_multiple_custom' => true,
                            'visibility' => 'individual',
                            'is_required' => null
                        ]
                    ],
                    [
                        'type' => 'checkbox_group',
                        'data' => [
                            'key' => 'has_meter',
                            'label' => 'Для определения объемов потребления электроэнергии установлены приборы учета',
                            'options' => [
                                ['value' => 'Да'],
                                ['value' => 'Нет']
                            ],
                            'allow_multiple_custom' => false,
                            'visibility' => 'individual',
                            'is_required' => null
                        ]
                    ],
                    [
                        'type' => 'checkbox_group',
                        'data' => [
                            'key' => 'tariff_choice',
                            'label' => 'Для определения размера платы за электроэнергию применять тариф:',
                            'options' => [
                                ['value' => 'Одноставочный'],
                                ['value' => 'Тариф дифференцированный по двум зонам (при наличии введенных в эксплуатацию приборов, обеспечивающих такой учет)'],
                                ['value' => 'Тариф дифференцированный по трем зонам (при наличии введенных в эксплуатацию приборов, обеспечивающих такой учет)']
                            ],
                            'allow_multiple_custom' => false,
                            'visibility' => 'individual',
                            'is_required' => null
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'supply_period',
                            'label' => 'Срок электроснабжения (для заявителей, заключающих договор на определенный срок, присоединенных по временной схеме электроснабжения)',
                            'type' => 'text',
                            'special_format' => 'range_date',
                            'is_required' => false,
                            'is_readonly' => false,
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'section_header',
                        'data' => [
                            'title' => '7. Документы',
                            'level' => 'h4',
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'select_field',
                        'data' => [
                            'key' => 'appeal_reason',
                            'label' => 'Причина обращения',
                            'options' => [
                                ['value' => 'Впервые вводимое в эксплуатацию'],
                                ['value' => 'Смена собственника']
                            ],
                            'allow_custom' => true,
                            'is_required' => true,
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'select_field',
                        'data' => [
                            'key' => 'payment_delivery',
                            'label' => 'Платежные документы для внесения платы за коммунальную услугу (электроснабжение) прошу предоставлять',
                            'options' => [
                                ['value' => 'В почтовый ящик по адресу, указанному в пункте 4'],
                                ['value' => 'В почтовый ящик по адресу, указанному в пункте 4.1'],
                                ['value' => 'В почтовый ящик по адресу, указанному в пункте 8'],
                                ['value' => 'Через личный кабинет ГИС ЖКЧ'],
                                ['value' => 'По электронной почте на адрес, указанный в пункте 6 (или укажите другой адрес электронной почты)']
                            ],
                            'allow_custom' => true,
                            'is_required' => true,
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'dynamic_input',
                        'data' => [
                            'key' => 'payment_delivery',
                            'label' => 'Платежные документы для внесения платы за коммунальную услугу (электроснабжение) прошу предоставлять',
                            'is_required' => true,
                            'options' => [
                                [
                                    'value' => 'В почтовый ящик по адресу, указанному в пункте 3',
                                    'input_type' => 'none'
                                ],
                                [
                                    'value' => 'В почтовый ящик по адресу, указанному в пункте 3.1',
                                    'input_type' => 'none'
                                ],
                                [
                                    'value' => 'В почтовый ящик по адресу, указанному в пункте 7',
                                    'input_type' => 'none'
                                ],
                                [
                                    'value' => 'Через личный кабинет ГИС ЖКЧ',
                                    'input_type' => 'none'
                                ],
                                [
                                    'value' => 'По электронной почте на адрес, указанный в пункте 4',
                                    'input_type' => 'none'
                                ],
                                [
                                    'value' => 'Или укажите другой адрес электронной почты',
                                    'input_type' => 'email',
                                    'input_label' => 'Адрес электронной почты'
                                ]
                            ],
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'dynamic_input',
                        'data' => [
                            'key' => 'notification_delivery',
                            'label' => 'Уведомления, письма, иную информацию и документы, предусмотренные Постановлениями Правительства от 06.05.2011 №354 и от 04.05.2012 №442 направлять',
                            'is_required' => true,
                            'options' => [
                                [
                                    'value' => 'По электронной почте на адрес, указанный в пункте 7 ',
                                    'input_type' => 'none'
                                ],
                                [
                                    'value' => 'В почтовый ящик по адресу, указанному в пункте 3',
                                    'input_type' => 'none'
                                ],
                                [
                                    'value' => 'В почтовый ящик по адресу, указанному в пункте 3.1',
                                    'input_type' => 'none'
                                ],
                                [
                                    'value' => 'В почтовый ящик по адресу, указанному в пункте 6',
                                    'input_type' => 'none'
                                ],
                                [
                                    'value' => 'СМС-уведомлением на телефон (указать)',
                                    'input_type' => 'phone',
                                    'input_label' => null
                                ]
                            ],
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'text_block',
                        'data' => [
                            'body' => '<p>В подтверждение информации, указанной в настоящем заявлении требуется приложить следующие документы:</p><p>1. Копия паспорта</p><p>2. Копия документа, подтверждающего право собственности (пользования)</p><p>3. Выписка из поквартирной карточки (сведения о количестве зарегистрированных по адресу объекта энергоснабжения лиц)</p><p>4. Акт допуска в эксплуатацию коммерческого учета электроэнергии (акт проверки коммерческого учета электроэнергии)</p><p>5. Документы подтверждающие технологическое присоединение (акт разграничения балансовой принадлежности и эксплуатационной ответственности)</p>',
                            'visibility' => 'all'
                        ]
                    ],
                    [
                        'type' => 'file_upload',
                        'data' => [
                            'key' => 'passport_copy',
                            'label' => 'Копия паспорта',
                            'is_required' => true,
                            'allow_multiple' => true,
                            'accepted_types' => [
                                'pdf' => 'PDF документы',
                                'jpg' => 'Изображения JPG',
                                'png' => 'Изображения PNG',
                                'doc' => 'Word документы',
                                'docx' => 'Word документы'
                            ],
                            'max_size' => '9',
                            'max_files' => '2',
                            'helper_text' => null,
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'file_upload',
                        'data' => [
                            'key' => 'ownership_doc',
                            'label' => 'Копия документа, подтверждающего право собственности',
                            'is_required' => true,
                            'allow_multiple' => true,
                            'accepted_types' => [
                                'pdf' => 'PDF документы',
                                'jpg' => 'Изображения JPG',
                                'png' => 'Изображения PNG',
                                'doc' => 'Word документы',
                                'docx' => 'Word документы'
                            ],
                            'max_size' => 10,
                            'max_files' => '1',
                            'helper_text' => null,
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'file_upload',
                        'data' => [
                            'key' => 'apartment_card',
                            'label' => 'Выписка из поквартирной карточки',
                            'is_required' => true,
                            'allow_multiple' => true,
                            'accepted_types' => [
                                'pdf' => 'PDF документы',
                                'jpg' => 'Изображения JPG',
                                'png' => 'Изображения PNG',
                                'doc' => 'Word документы',
                                'docx' => 'Word документы'
                            ],
                            'max_size' => 10,
                            'max_files' => '3',
                            'helper_text' => null,
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'file_upload',
                        'data' => [
                            'key' => 'meter_acceptance_act',
                            'label' => 'Акт допуска в эксплуатацию коммерческого учета электроэнергии',
                            'is_required' => true,
                            'allow_multiple' => true,
                            'accepted_types' => [
                                'pdf' => 'PDF документы',
                                'jpg' => 'Изображения JPG',
                                'png' => 'Изображения PNG',
                                'doc' => 'Word документы',
                                'docx' => 'Word документы'
                            ],
                            'max_size' => 10,
                            'max_files' => '3',
                            'helper_text' => null,
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'file_upload',
                        'data' => [
                            'key' => 'tech_connection_docs',
                            'label' => 'Документы подтверждающие технологическое присоединение',
                            'is_required' => true,
                            'allow_multiple' => true,
                            'accepted_types' => [
                                'pdf' => 'PDF документы',
                                'jpg' => 'Изображения JPG',
                                'png' => 'Изображения PNG',
                                'doc' => 'Word документы',
                                'docx' => 'Word документы'
                            ],
                            'max_size' => 10,
                            'max_files' => '5',
                            'helper_text' => null,
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'file_upload',
                        'data' => [
                            'key' => 'add_docs',
                            'label' => 'Дополнительные документы',
                            'is_required' => false,
                            'allow_multiple' => true,
                            'accepted_types' => [
                                'pdf' => 'PDF документы',
                                'jpg' => 'Изображения JPG',
                                'png' => 'Изображения PNG',
                                'doc' => 'Word документы',
                                'docx' => 'Word документы'
                            ],
                            'max_size' => 10,
                            'max_files' => '10',
                            'helper_text' => null,
                            'visibility' => 'individual'
                        ]
                    ],
                    [
                        'type' => 'checkbox_group',
                        'data' => [
                            'key' => 'personal_info',
                            'label' => 'В соответствии с ФЗ от 27.07.2006 №152-ФЗ "О персональных данных" даёте своё согласие на обработку своих персональных данных',
                            'options' => [
                                ['value' => 'Да']
                            ],
                            'allow_multiple_custom' => false,
                            'visibility' => 'all',
                            'is_required' => true
                        ]
                    ],
                ],
            ]
        );

        ApplicationTemplate::updateOrCreate(
            ['slug' => 'application-legal'],
            [
                'title' => 'Заявление на заключение договора энергоснабжения (юридическое лицо)',
                'client_type' => 'legal',
                'is_active' => true,
                'content' => [
                    [
                        'type' => 'text_block',
                        'data' => [
                            'body' => '<p>Заполните форму для создания заявления о заключении договора электроснабжения от имени организации</p>',
                        ]
                    ],
                    [
                        'type' => 'section_header',
                        'data' => ['title' => '1. Данные организации', 'level' => 'h4']
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'company_name',
                            'label' => 'Полное наименование организации',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => true,
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'director_position',
                            'label' => 'Должность руководителя',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => true,
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'director_name',
                            'label' => 'Ф.И.О. руководителя',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => true,
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'section_header',
                        'data' => ['title' => '2. Срок действия договора', 'level' => 'h4']
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'contract_period',
                            'label' => 'Период времени, на который заключается договор',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => true,
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'section_header',
                        'data' => ['title' => '3. Адреса', 'level' => 'h4']
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'legal_address',
                            'label' => 'Место нахождения юридического лица (согласно учредительных документов)',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => true,
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'actual_address',
                            'label' => 'Фактический адрес для почтовых отправлений',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => true,
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'section_header',
                        'data' => ['title' => '4. Контактная информация', 'level' => 'h4']
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'phone',
                            'label' => 'Телефон (т/факс)',
                            'type' => 'text',
                            'special_format' => 'phone',
                            'is_required' => true,
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'email',
                            'label' => 'Адрес электронной почты',
                            'type' => 'email',
                            'special_format' => 'none',
                            'is_required' => true,
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'section_header',
                        'data' => ['title' => '5. Реквизиты организации', 'level' => 'h4']
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'ogrn',
                            'label' => 'ОГРН',
                            'type' => 'text',
                            'special_format' => 'ogrn',
                            'is_required' => true,
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'inn',
                            'label' => 'ИНН',
                            'type' => 'text',
                            'special_format' => 'inn_legal',
                            'is_required' => true,
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'kpp',
                            'label' => 'КПП',
                            'type' => 'text',
                            'special_format' => 'kpp',
                            'is_required' => true,
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'okved',
                            'label' => 'ОКВЭД',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => true,
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'bank_details',
                            'label' => 'Банковские реквизиты (наименование банка, р/с, к/с, БИК)',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => true,
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'section_header',
                        'data' => ['title' => '6. Электронный документооборот', 'level' => 'h4']
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'edo_operator',
                            'label' => 'Оператор ЭДО (если применяется усиленная квалифицированная подпись)',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => false,
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'section_header',
                        'data' => ['title' => '7. Сведения об объекте энергоснабжения', 'level' => 'h4']
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'object_category',
                            'label' => 'Категория объекта (промышленное предприятие, учреждение, торговая палатка и т.п.)',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => true,
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'object_address',
                            'label' => 'Адрес объекта (при нескольких объектах — через ";" с порядковым номером)',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => true,
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'object_schedule',
                            'label' => 'График работы объекта',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => false,
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'section_header',
                        'data' => ['title' => '8. Для бюджетных организаций (заполняется при наличии)', 'level' => 'h4']
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'budget_level',
                            'label' => 'Уровень бюджетного финансирования',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => false,
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'budget_authority',
                            'label' => 'Министерство, комитет, область, район, муниципальное образование',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => false,
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'section_header',
                        'data' => ['title' => '9. Технические характеристики энергоснабжения', 'level' => 'h4']
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'price_category',
                            'label' => 'Избранный вариант ценовой категории (макс. мощность энергопринимающих устройств менее 670 кВт)',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => true,
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'planned_consumption',
                            'label' => 'Плановое количество электроэнергии на год, кВт·ч',
                            'type' => 'number',
                            'special_format' => 'none',
                            'is_required' => true,
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'voltage_level_legal',
                            'label' => 'Уровень напряжения',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => true,
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'reliability_category',
                            'label' => 'Категория надёжности снабжения электроэнергией',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => true,
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'max_power',
                            'label' => 'Максимальная мощность, кВт',
                            'type' => 'number',
                            'special_format' => 'none',
                            'is_required' => true,
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'section_header',
                        'data' => ['title' => '10. Показания электросчётчика', 'level' => 'h4']
                    ],
                    [
                        'type' => 'input_field',
                        'data' => [
                            'key' => 'meter_reading_at_signing',
                            'label' => 'Показания электросчётчика на момент заключения договора',
                            'type' => 'text',
                            'special_format' => 'none',
                            'is_required' => true,
                            'is_readonly' => false
                        ]
                    ],
                    [
                        'type' => 'checkbox_group',
                        'data' => [
                            'key' => 'personal_info',
                            'label' => 'В соответствии с ФЗ от 27.07.2006 №152-ФЗ "О персональных данных" даёте своё согласие на обработку персональных данных руководителя',
                            'options' => [['value' => 'Да']],
                            'allow_multiple_custom' => false,
                            'is_required' => true
                        ]
                    ],
                ],
            ]
        );

        $this->command->info('Шаблон заявки на заключение договора успешно создан/обновлен');
    }
}
