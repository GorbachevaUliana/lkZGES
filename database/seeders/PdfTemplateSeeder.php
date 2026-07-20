<?php

namespace Database\Seeders;

use App\Models\PdfTemplate;
use Illuminate\Database\Seeder;

class PdfTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Шаблон для физических лиц
        $individualContent = <<<'HTML'
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Заявление на заключение договора энергоснабжения</title>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            src: local('DejaVu Sans');
            font-weight: normal;
            font-style: normal;
        }
        body {
            font-family: 'DejaVu Sans', 'Times New Roman', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            margin: 0;
            padding: 15mm 20mm;
            color: #000;
        }
        .header-right {
            text-align: right;
            margin-bottom: 20px;
        }
        .title {
            text-align: center;
            margin-bottom: 25px;
        }
        .title h1 {
            font-size: 14pt;
            text-transform: uppercase;
            margin: 0 0 5px 0;
        }
        .title p {
            margin: 0;
            font-size: 12pt;
        }
        .section {
            margin-bottom: 15px;
        }
        .section-title {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 5px;
        }
        .info-row {
            margin-bottom: 3px;
            text-align: justify;
        }
        .info-row-value {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 200px;
            text-align: center;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .info-table td {
            padding: 4px 8px;
            border: 1px solid #000;
            vertical-align: top;
        }
        .info-table td:first-child {
            width: 45%;
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 30px;
            font-size: 10pt;
        }
        .signature-block {
            margin-top: 25px;
            width: 100%;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            width: 200px;
            height: 25px;
            display: inline-block;
        }
        .caption {
            font-size: 9pt;
            color: #666;
        }
        .document-note {
            font-size: 9pt;
            color: #666;
            margin-top: 20px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    {{-- Шапка --}}
    <div class="header-right">
        <p>Генеральному директору</p>
        <p>ООО «Заринская горэлектросеть»</p>
    </div>

    {{-- Заголовок --}}
    <div class="title">
        <h1>ЗАЯВЛЕНИЕ</h1>
        <p><strong>на заключение договора энергоснабжения</strong></p>
        <p>(открытие лицевого счета)</p>
    </div>

    {{-- 1. Инициалы --}}
    <div class="section">
        <p><strong>1.</strong> ФИО заявителя:
            <span class="info-row-value">{{ $data['last_name'] ?? '___' }}</span>
            <span class="info-row-value">{{ $data['first_name'] ?? '___' }}</span>
            <span class="info-row-value">{{ $data['middle_name'] ?? '___' }}</span>
        </p>
    </div>

    {{-- 2. Паспортные данные --}}
    <div class="section">
        <p><strong>2.</strong> Паспорт: серия и номер <span class="info-row-value">{{ $data['passport'] ?? '___' }}</span></p>
        <p style="margin-left: 30px;">выдан <span class="info-row-value" style="min-width: 400px;">{{ $data['passport_issue'] ?? '___' }}</span></p>
        <p style="margin-left: 30px;">дата выдачи <span class="info-row-value">{{ $data['passport_issue_date'] ?? '___' }}</span></p>
    </div>

    {{-- 3. Адрес регистрации --}}
    <div class="section">
        @php
            $regAddressParts = [];
            if (!empty($data['region'])) $regAddressParts[] = $data['region'];
            if (!empty($data['district'])) $regAddressParts[] = $data['district'];
            if (!empty($data['locality'])) $regAddressParts[] = $data['locality'];
            if (!empty($data['street'])) $regAddressParts[] = 'ул. ' . $data['street'];
            if (!empty($data['house'])) $regAddressParts[] = 'д. ' . $data['house'];
            if (!empty($data['corpus'])) $regAddressParts[] = 'корп. ' . $data['corpus'];
            if (!empty($data['apartment'])) $regAddressParts[] = 'кв. ' . $data['apartment'];
            $regAddressFull = !empty($regAddressParts) ? implode(', ', $regAddressParts) : 'Не указан';
        @endphp
        <p><strong>3.</strong> Адрес регистрации: <span class="info-row-value" style="min-width: 450px;">{{ $regAddressFull }}</span></p>
    </div>

    {{-- 3.1. Адрес фактического проживания --}}
    <div class="section">
        @php
            $actualAddressParts = [];
            if (!empty($data['actual_region'])) $actualAddressParts[] = $data['actual_region'];
            if (!empty($data['actual_district'])) $actualAddressParts[] = $data['actual_district'];
            if (!empty($data['actual_locality'])) $actualAddressParts[] = $data['actual_locality'];
            if (!empty($data['actual_street'])) $actualAddressParts[] = 'ул. ' . $data['actual_street'];
            if (!empty($data['actual_house'])) $actualAddressParts[] = 'д. ' . $data['actual_house'];
            if (!empty($data['actual_corpus'])) $actualAddressParts[] = 'корп. ' . $data['actual_corpus'];
            if (!empty($data['actual_apartment'])) $actualAddressParts[] = 'кв. ' . $data['actual_apartment'];
            $actualAddressFull = !empty($actualAddressParts) ? implode(', ', $actualAddressParts) : 'Не указан';
        @endphp
        <p><strong>3.1.</strong> Адрес фактического проживания: <span class="info-row-value" style="min-width: 400px;">{{ $actualAddressFull }}</span></p>
    </div>

    {{-- 4. Контактная информация --}}
    <div class="section">
        <p><strong>4.</strong> Телефон: <span class="info-row-value">{{ $data['phone'] ?? '___' }}</span></p>
        <p style="margin-left: 20px;">Адрес электронной почты: <span class="info-row-value" style="min-width: 300px;">{{ $data['email'] ?? $user_email ?? '___' }}</span></p>
    </div>

    {{-- Основной текст заявления --}}
    <div class="section" style="margin-top: 20px;">
        <p>Прошу заключить договор энергоснабжения (открыть лицевой счет) с учетом информации, содержащейся в настоящем заявлении.</p>
    </div>

    {{-- 5. Причина обращения --}}
    <div class="section">
        <p><strong>5.</strong> Причина обращения: <span class="info-row-value" style="min-width: 400px;">{{ $data['appeal_reason'] ?? '___' }}</span></p>
    </div>

    {{-- 6. Сведения об объекте энергоснабжения --}}
    <div class="section">
        <p><strong>6.</strong> Сведения об объекте энергоснабжения:</p>
        <p style="margin-left: 20px;">Энергопринимающие устройства, планируемые к присоединению: <span class="info-row-value">{{ $data['power_object'] ?? '___' }}</span></p>
    </div>

    {{-- Местонахождение объекта --}}
    <div class="section">
        <p><strong>Местонахождение объекта, по которому заключается договор:</strong></p>
        @php
            $objectAddressParts = [];
            if (!empty($data['region_object'])) $objectAddressParts[] = $data['region_object'];
            if (!empty($data['district_object'])) $objectAddressParts[] = $data['district_object'];
            if (!empty($data['locality_object'])) $objectAddressParts[] = $data['locality_object'];
            if (!empty($data['street_object'])) $objectAddressParts[] = 'ул. ' . $data['street_object'];
            if (!empty($data['house_object'])) $objectAddressParts[] = 'д. ' . $data['house_object'];
            if (!empty($data['corpus_object'])) $objectAddressParts[] = 'корп. ' . $data['corpus_object'];
            if (!empty($data['apartment_object'])) $objectAddressParts[] = 'кв. ' . $data['apartment_object'];
            $objectAddressFull = !empty($objectAddressParts) ? implode(', ', $objectAddressParts) : 'Не указан';
        @endphp
        <p style="margin-left: 20px;">Адрес: <span class="info-row-value" style="min-width: 500px;">{{ $objectAddressFull }}</span></p>
        @if(!empty($data['note']))
        <p style="margin-left: 20px;">Примечание: {{ $data['note'] }}</p>
        @endif
        <p style="margin-left: 20px;">Общая площадь помещения: <span class="info-row-value">{{ $data['area'] ?? '___' }}</span> кв. м.</p>
        <p style="margin-left: 20px;">Количество лиц, постоянно проживающих в помещении: <span class="info-row-value">{{ $data['residents_count'] ?? '___' }}</span></p>
        <p style="margin-left: 20px;">Максимальная мощность электроприемников: <span class="info-row-value">{{ $data['max_power'] ?? '___' }}</span> кВт</p>
        @php
            $voltageLevel = $data['voltage_level'] ?? [];
            if (is_array($voltageLevel)) {
                $voltageLevelStr = implode(', ', $voltageLevel);
            } else {
                $voltageLevelStr = $voltageLevel ?? '___';
            }
        @endphp
        <p style="margin-left: 20px;">Уровень напряжения: <span class="info-row-value">{{ $voltageLevelStr }}</span></p>
        @if(!empty($data['act_reference']))
        <p style="margin-left: 20px;">Реквизиты акта об определении границы раздела: <span class="info-row-value" style="min-width: 350px;">{{ $data['act_reference'] }}</span></p>
        @endif
        @php
            $consumptionPurpose = $data['consumption_purpose'] ?? [];
            if (is_array($consumptionPurpose)) {
                $consumptionPurposeStr = implode(', ', $consumptionPurpose);
            } else {
                $consumptionPurposeStr = $consumptionPurpose ?? '___';
            }
        @endphp
        <p style="margin-left: 20px;">Сведения о направлениях потребления электроэнергии:</p>
        <p style="margin-left: 40px;">{{ $consumptionPurposeStr }}</p>
        @php
            $hasMeter = $data['has_meter'] ?? [];
            if (is_array($hasMeter)) {
                $hasMeterStr = implode(', ', $hasMeter);
            } else {
                $hasMeterStr = $hasMeter ?? '___';
            }
        @endphp
        <p style="margin-left: 20px;">Приборы учета установлены: <span class="info-row-value">{{ $hasMeterStr }}</span></p>
        @php
            $tariffChoice = $data['tariff_choice'] ?? [];
            if (is_array($tariffChoice)) {
                $tariffChoiceStr = implode(', ', $tariffChoice);
            } else {
                $tariffChoiceStr = $tariffChoice ?? '___';
            }
        @endphp
        <p style="margin-left: 20px;">Тариф: <span class="info-row-value" style="min-width: 450px;">{{ $tariffChoiceStr }}</span></p>
        @if(!empty($data['supply_period']))
        <p style="margin-left: 20px;">Срок электроснабжения: <span class="info-row-value">{{ $data['supply_period'] }}</span></p>
        @endif
    </div>

    {{-- 7. Документы --}}
    <div class="section">
        <p><strong>7.</strong> Документы:</p>
        <p style="margin-left: 20px;">Платежные документы прошу предоставлять:</p>
        @php
            $paymentDelivery = $data['payment_delivery'] ?? [];
            if (is_array($paymentDelivery)) {
                $paymentDeliveryStr = $paymentDelivery['selected'] ?? $paymentDelivery[0] ?? '';
                if (!empty($paymentDelivery['inputValue'])) {
                    $paymentDeliveryStr .= ': ' . $paymentDelivery['inputValue'];
                }
            } else {
                $paymentDeliveryStr = $paymentDelivery ?? '___';
            }
        @endphp
        <p style="margin-left: 40px;">{{ $paymentDeliveryStr }}</p>
        <p style="margin-left: 20px;">Уведомления прошу направлять:</p>
        @php
            $notificationDelivery = $data['notification_delivery'] ?? [];
            if (is_array($notificationDelivery)) {
                $notificationDeliveryStr = $notificationDelivery['selected'] ?? $notificationDelivery[0] ?? '';
                if (!empty($notificationDelivery['inputValue'])) {
                    $notificationDeliveryStr .= ': ' . $notificationDelivery['inputValue'];
                }
            } else {
                $notificationDeliveryStr = $notificationDelivery ?? '___';
            }
        @endphp
        <p style="margin-left: 40px;">{{ $notificationDeliveryStr }}</p>
    </div>

    {{-- Согласие на обработку персональных данных --}}
    <div class="section" style="margin-top: 15px;">
        @php
            $personalInfo = $data['personal_info'] ?? [];
            if (is_array($personalInfo)) {
                $consent = in_array('Да', $personalInfo) ? 'Да' : 'Нет';
            } else {
                $consent = str_contains($personalInfo ?? '', 'Да') ? 'Да' : 'Нет';
            }
        @endphp
        <p>В соответствии с ФЗ от 27.07.2006 №152-ФЗ "О персональных данных" даю своё согласие на обработку своих персональных данных: <strong>{{ $consent }}</strong></p>
    </div>

    {{-- Подпись --}}
    <div class="footer">
        <p><strong>Дата:</strong> {{ $data['created_at'] ?? date('d.m.Y') }}</p>
        
        <table class="signature-block" style="margin-top: 30px;">
            <tr>
                <td style="width: 50px;">Подпись:</td>
                <td style="width: 200px;">
                    <div class="signature-line"></div>
                </td>
                <td style="width: 30px;"></td>
                <td style="width: 120px;">Расшифровка:</td>
                <td style="width: 200px;">
                    <div class="signature-line"></div>
                </td>
            </tr>
        </table>

        <div class="document-note">
            <p>В подтверждение информации, указанной в настоящем заявлении, прилагаются следующие документы:</p>
            <ol style="margin: 5px 0; padding-left: 20px;">
                <li>Копия паспорта</li>
                <li>Копия документа, подтверждающего право собственности (пользования)</li>
                <li>Выписка из поквартирной карточки (сведения о количестве зарегистрированных по адресу объекта энергоснабжения лиц)</li>
                <li>Акт допуска в эксплуатацию коммерческого учета электроэнергии (акт проверки коммерческого учета электроэнергии)</li>
                <li>Документы подтверждающие технологическое присоединение (акт разграничения балансовой принадлежности и эксплуатационной ответственности)</li>
            </ol>
            <p style="font-style: italic; margin-top: 10px;">Документ сгенерирован автоматически в Личном кабинете потребителя. Оригинал подписи проставляется при очном заключении договора.</p>
        </div>
    </div>
</body>
</html>
HTML;

        // Шаблон для юридических лиц (оставляем без изменений)

        $legalContent = <<<'HTML'
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Заявление о заключении договора энергоснабжения</title>
    <style>
        body {
            font-family: 'DejaVu Sans', 'Times New Roman', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            margin: 0;
            padding: 15mm 20mm 15mm 14mm;
            color: #000;
        }
        .header-right {
            text-align: right;
            margin-bottom: 20px;
        }
        .title {
            text-align: center;
            margin-bottom: 25px;
        }
        .title h1 {
            font-size: 14pt;
            text-transform: uppercase;
            margin: 0 0 5px 0;
        }
        .title p {
            margin: 0;
            font-size: 12pt;
        }
        .section {
            margin-bottom: 15px;
        }
        .section-title {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 5px;
            margin-top: 15px;
        }
        .info-row-value {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 250px;
            padding: 0 4px;
        }
        .info-row-value.wide {
            min-width: 400px;
        }
        .footer {
            margin-top: 30px;
            font-size: 10pt;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            width: 220px;
            height: 25px;
            display: inline-block;
        }
        .document-note {
            font-size: 9pt;
            color: #666;
            margin-top: 20px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <!-- Шапка -->
    <div class="header-right">
        <p>Генеральному директору</p>
        <p>ООО «Заринская горэлектросеть»</p>
    </div>

    <!-- Заголовок -->
    <div class="title">
        <h1>Заявление</h1>
        <p>о заключении договора электроснабжения</p>
        <p><strong>Юридическое лицо</strong></p>
    </div>

    <!-- 1. Данные организации -->
    <div class="section-title">1. Данные организации</div>
    <div class="section">
        <p>Наименование организации: <span class="info-row-value wide">{{ company_name }}</span></p>
        <p>В лице (должность): <span class="info-row-value">{{ director_position }}</span></p>
        <p>Ф.И.О. руководителя: <span class="info-row-value wide">{{ director_name }}</span></p>
        <p>Просит заключить договор энергоснабжения на период: <span class="info-row-value">{{ contract_period }}</span></p>
    </div>

    <!-- 2. Адреса -->
    <div class="section-title">2. Адреса</div>
    <div class="section">
        <p>Место нахождения юридического лица: <span class="info-row-value wide">{{ legal_address }}</span></p>
        <p>Фактический адрес для почтовых отправлений: <span class="info-row-value wide">{{ actual_address }}</span></p>
    </div>

    <!-- 3. Контактная информация -->
    <div class="section-title">3. Контактная информация</div>
    <div class="section">
        <p>Телефон (т/факс): <span class="info-row-value">{{ phone }}</span></p>
        <p>Email: <span class="info-row-value">{{ email }}</span></p>
    </div>

    <!-- 4. Реквизиты -->
    <div class="section-title">4. Реквизиты организации</div>
    <div class="section">
        <p>ОГРН: <span class="info-row-value">{{ ogrn }}</span></p>
        <p>ИНН: <span class="info-row-value">{{ inn }}</span></p>
        <p>КПП: <span class="info-row-value">{{ kpp }}</span></p>
        <p>ОКВЭД: <span class="info-row-value">{{ okved }}</span></p>
        <p>Банковские реквизиты: <span class="info-row-value wide">{{ bank_details }}</span></p>
    </div>

    <!-- 5. ЭДО -->
    <div class="section-title">5. Электронный документооборот</div>
    <div class="section">
        <p>Оператор ЭДО: <span class="info-row-value wide">{{ edo_operator }}</span></p>
    </div>

    <!-- 6. Объект -->
    <div class="section-title">6. Сведения об объекте энергоснабжения</div>
    <div class="section">
        <p>Категория объекта: <span class="info-row-value wide">{{ object_category }}</span></p>
        <p>Адрес объекта: <span class="info-row-value wide">{{ object_address }}</span></p>
        <p>График работы объекта: <span class="info-row-value wide">{{ object_schedule }}</span></p>
    </div>

    <!-- 7. Бюджетные организации -->
    <div class="section-title">7. Для бюджетных организаций</div>
    <div class="section">
        <p>Уровень бюджетного финансирования: <span class="info-row-value wide">{{ budget_level }}</span></p>
        <p>Министерство/комитет/муниципальное образование: <span class="info-row-value wide">{{ budget_authority }}</span></p>
    </div>

    <!-- 8. Технические характеристики -->
    <div class="section-title">8. Технические характеристики энергоснабжения</div>
    <div class="section">
        <p>Ценовая категория: <span class="info-row-value wide">{{ price_category }}</span></p>
        <p>Плановое количество электроэнергии на год: <span class="info-row-value">{{ planned_consumption }}</span> кВт·ч</p>
        <p>Уровень напряжения: <span class="info-row-value">{{ voltage_level_legal }}</span></p>
        <p>Категория надёжности снабжения: <span class="info-row-value">{{ reliability_category }}</span></p>
        <p>Максимальная мощность: <span class="info-row-value">{{ max_power }}</span> кВт</p>
    </div>

    <!-- 9. Показания счётчика -->
    <div class="section-title">9. Показания электросчётчика</div>
    <div class="section">
        <p>Показания на момент заключения договора: <span class="info-row-value">{{ meter_reading_at_signing }}</span></p>
    </div>

    <!-- Подпись -->
    <div class="footer">
        <p><strong>Дата подачи заявки:</strong> {{ created_at }}</p>

        <table style="margin-top: 30px; width: 100%;">
            <tr>
                <td style="width: 120px;">Руководитель:</td>
                <td style="width: 220px;">
                    <div class="signature-line"></div>
                </td>
                <td style="width: 30px;"></td>
                <td style="width: 120px;">Расшифровка:</td>
                <td style="width: 220px;">
                    <div class="signature-line"></div>
                </td>
            </tr>
        </table>

        <div class="document-note">
            <p style="font-style: italic;">Документ сгенерирован автоматически в Личном кабинете потребителя. Оригинал подписи и печати проставляется при очном заключении договора.</p>
        </div>
    </div>
</body>
</html>
HTML;

        // Создаём/обновляем шаблон для физлиц
        PdfTemplate::updateOrCreate(
            ['slug' => 'application_individual'],
            [
                'name' => 'Заявление на заключение договора (физлицо)',
                'client_type' => 'individual',
                'document_type' => 'application',
                'content' => $individualContent,
                'is_active' => true,
            ]
        );

        // Создаём/обновляем шаблон для юрлиц
        PdfTemplate::updateOrCreate(
            ['slug' => 'application_legal'],
            [
                'name' => 'Заявление на заключение договора (юрлицо)',
                'client_type' => 'legal',
                'document_type' => 'application',
                'content' => $legalContent,
                'is_active' => true,
            ]
        );

        $this->command->info('PDF шаблоны успешно созданы/обновлены');
    }
}