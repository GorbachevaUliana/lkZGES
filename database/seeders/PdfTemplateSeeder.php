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
    <title>Заявка на заключение договора энергоснабжения (Юридическое лицо)</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; line-height: 1.6; margin: 0; padding: 40px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .header h1 { font-size: 16px; text-transform: uppercase; margin: 0 0 10px 0; }
        .header p { margin: 0; color: #666; }
        .section { margin-bottom: 25px; }
        .section-title { font-weight: bold; font-size: 13px; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px solid #ccc; }
        .info-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .info-table td { padding: 8px 12px; border-bottom: 1px solid #eee; }
        .info-table td:first-child { width: 40%; background-color: #f9f9f9; font-weight: 500; }
        .footer { margin-top: 50px; padding-top: 20px; border-top: 1px solid #ccc; }
        .signature-block { display: flex; justify-content: space-between; margin-top: 40px; }
        .signature-field { border-bottom: 1px solid #333; width: 200px; display: inline-block; }
        .stamp-area { width: 150px; height: 150px; border: 1px dashed #ccc; display: flex; align-items: center; justify-content: center; color: #ccc; font-size: 10px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Заявка № {{ $application_id ?? '___' }}</h1>
        <p>на заключение договора энергоснабжения</p>
        <p><strong>Юридическое лицо</strong></p>
    </div>

    <div class="content">
        <div class="section">
            <div class="section-title">1. Сведения об организации</div>
            <table class="info-table">
                <tr><td>Полное наименование</td><td><strong>{{ $company_name ?? $data['company_name'] ?? 'Не указано' }}</strong></td></tr>
                <tr><td>ИНН</td><td>{{ $inn ?? $data['inn'] ?? 'Не указан' }}</td></tr>
                @if(!empty($data['kpp']))<tr><td>КПП</td><td>{{ $data['kpp'] }}</td></tr>@endif
                @if(!empty($data['ogrn']))<tr><td>ОГРН</td><td>{{ $data['ogrn'] }}</td></tr>@endif
            </table>
        </div>

        <div class="section">
            <div class="section-title">2. Контактная информация</div>
            <table class="info-table">
                @if(!empty($data['contact_person']))<tr><td>Контактное лицо</td><td><strong>{{ $data['contact_person'] }}</strong></td></tr>@endif
                <tr><td>Телефон</td><td>{{ $phone ?? 'Не указан' }}</td></tr>
                <tr><td>Email</td><td>{{ $user_email ?? 'Не указана' }}</td></tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">3. Адрес объекта энергопотребления</div>
            @php
                $addressParts = [];
                $region = $data['region'] ?? $data[' region'] ?? null;
                if (!empty($region)) $addressParts[] = $region;
                if (!empty($data['district'])) $addressParts[] = $data['district'];
                if (!empty($data['locality'])) $addressParts[] = $data['locality'];
                if (!empty($data['street'])) $addressParts[] = 'ул. ' . $data['street'];
                if (!empty($data['house'])) $addressParts[] = 'д. ' . $data['house'];
                $fullAddress = !empty($addressParts) ? implode(', ', $addressParts) : ($address ?? 'Не указан');
            @endphp
            <table class="info-table">
                <tr><td>Полный адрес</td><td><strong>{{ $fullAddress }}</strong></td></tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">4. Условия договора</div>
            <p>Прошу заключить договор энергоснабжения на условиях III категории надёжности.</p>
        </div>
    </div>

    <div class="footer">
        <p><strong>Дата подачи заявки:</strong> {{ $created_at ?? date('d.m.Y H:i') }}</p>
        <div class="signature-block">
            <div><p>Руководитель:</p><p class="signature-field"></p><p style="font-size: 10px; color: #666;">ФИО, подпись</p></div>
            <div><p>М.П.</p><div class="stamp-area">Место печати</div></div>
            <div><p>Дата:</p><p class="signature-field" style="width: 120px;"></p></div>
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