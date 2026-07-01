<?php
 
namespace App\Services;

use App\Enums\ClientType;
 
/**
 * Единый источник Twig-содержимого для PDF-шаблонов.
 *
 * И сидер (PdfTemplateSeeder), и миграция обновления (update_pdf_templates_to_twig)
 * берут содержимое отсюда, чтобы не дублировать ~700 строк в двух местах.
 *
 * ВАЖНО: это Twig-синтаксис ({{ var }}, {% if %}), НЕ Blade.
 * Никакого PHP, никаких @php-блоков. Логика — в PdfDataPreparator.
 */
class PdfTemplateDefinitions
{
    /**
     * Все доступные переменные Twig в шаблонах.
     * Используется в Filament-справке и для документации.
     */
    public static function availableVariables(): array
    {
        return [
            'system' => [
                'application_id' => 'Номер заявки',
                'full_name' => 'ФИО заявителя',
                'user_email' => 'Email пользователя',
                'created_at' => 'Дата/время подачи',
                'client_type_name' => 'Тип клиента (Физлицо/Юрлицо)',
                'company_name' => 'Название организации (для юрлиц)',
                'inn' => 'ИНН',
                'phone' => 'Телефон',
                'object_address' => 'Адрес объекта (из property)',
            ],
            'addresses' => [
                'registration_address' => 'Адрес регистрации (собран из полей)',
                'actual_address' => 'Адрес фактического проживания (собран)',
                'object_address_full' => 'Адрес объекта энергоснабжения (собран)',
            ],
            ClientType::Individual->value => [
                'last_name' => 'Фамилия',
                'first_name' => 'Имя',
                'middle_name' => 'Отчество',
                'passport' => 'Серия и номер паспорта',
                'passport_issue' => 'Кем выдан паспорт',
                'passport_issue_date' => 'Дата выдачи паспорта',
                'phone' => 'Телефон',
                'email' => 'Email',
                'power_object' => 'Тип энергопринимающих устройств',
                'note' => 'Примечание',
                'area' => 'Площадь помещения',
                'residents_count' => 'Кол-во проживающих',
                'max_power' => 'Максимальная мощность (кВт)',
                'act_reference' => 'Реквизиты акта границы раздела',
                'appeal_reason' => 'Причина обращения',
                'supply_period' => 'Срок электроснабжения',
                'voltage_level' => 'Уровень напряжения (220В/380В)',
                'consumption_purpose' => 'Направления потребления',
                'has_meter' => 'Приборы учета (Да/Нет)',
                'tariff_choice' => 'Выбранный тариф',
                'payment_delivery' => 'Способ доставки платежек',
                'notification_delivery' => 'Способ доставки уведомлений',
                'consent' => 'Согласие на обработку ПДн (Да/Нет)',
            ],
            'address_parts' => [
                'region', 'district', 'locality', 'street', 'house', 'corpus', 'apartment',
                'actual_region', 'actual_district', 'actual_locality', 'actual_street',
                'actual_house', 'actual_corpus', 'actual_apartment',
                'region_object', 'district_object', 'locality_object', 'street_object',
                'house_object', 'corpus_object', 'apartment_object',
            ],
            ClientType::Legal->value => [
                'company_name' => 'Наименование организации',
                'inn' => 'ИНН',
                'kpp' => 'КПП',
                'ogrn' => 'ОГРН',
                'legal_address' => 'Юридический адрес',
                'actual_address' => 'Фактический адрес',
                'contact_person' => 'Контактное лицо',
            ],
        ];
    }
 
    /**
     * Twig-содержимое шаблона для физических лиц.
     */
    public static function individualContent(): string
    {
        return <<<'TWIG'
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
        .header-right { text-align: right; margin-bottom: 20px; }
        .title { text-align: center; margin-bottom: 25px; }
        .title h1 { font-size: 14pt; text-transform: uppercase; margin: 0 0 5px 0; }
        .title p { margin: 0; font-size: 12pt; }
        .section { margin-bottom: 15px; }
        .info-row { margin-bottom: 3px; text-align: justify; }
        .info-row-value {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 200px;
            text-align: center;
        }
        .footer { margin-top: 30px; font-size: 10pt; }
        .signature-block { margin-top: 25px; width: 100%; }
        .signature-line {
            border-bottom: 1px solid #000;
            width: 200px;
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
    <div class="header-right">
        <p>Генеральному директору</p>
        <p>ООО «Заринская горэлектросеть»</p>
    </div>
 
    <div class="title">
        <h1>ЗАЯВЛЕНИЕ</h1>
        <p><strong>на заключение договора энергоснабжения</strong></p>
        <p>(открытие лицевого счета)</p>
    </div>
 
    <div class="section">
        <p><strong>1.</strong> ФИО заявителя:
            <span class="info-row-value">{{ last_name|default('___') }}</span>
            <span class="info-row-value">{{ first_name|default('___') }}</span>
            <span class="info-row-value">{{ middle_name|default('___') }}</span>
        </p>
    </div>
 
    <div class="section">
        <p><strong>2.</strong> Паспорт: серия и номер <span class="info-row-value">{{ passport|default('___') }}</span></p>
        <p style="margin-left: 30px;">выдан <span class="info-row-value" style="min-width: 400px;">{{ passport_issue|default('___') }}</span></p>
        <p style="margin-left: 30px;">дата выдачи <span class="info-row-value">{{ passport_issue_date|default('___') }}</span></p>
    </div>
 
    <div class="section">
        <p><strong>3.</strong> Адрес регистрации:
            <span class="info-row-value" style="min-width: 450px;">{{ registration_address|raw }}</span>
        </p>
    </div>
 
    <div class="section">
        <p><strong>3.1.</strong> Адрес фактического проживания:
            <span class="info-row-value" style="min-width: 400px;">{{ actual_address|raw }}</span>
        </p>
    </div>
 
    <div class="section">
        <p><strong>4.</strong> Телефон: <span class="info-row-value">{{ phone|default('___') }}</span></p>
        <p style="margin-left: 20px;">Адрес электронной почты:
            <span class="info-row-value" style="min-width: 300px;">{{ email|default(user_email|default('___')) }}</span>
        </p>
    </div>
 
    <div class="section" style="margin-top: 20px;">
        <p>Прошу заключить договор энергоснабжения (открыть лицевый счет) с учетом информации, содержащейся в настоящем заявлении.</p>
    </div>
 
    <div class="section">
        <p><strong>5.</strong> Причина обращения:
            <span class="info-row-value" style="min-width: 400px;">{{ appeal_reason|default('___') }}</span>
        </p>
    </div>
 
    <div class="section">
        <p><strong>6.</strong> Сведения об объекте энергоснабжения:</p>
        <p style="margin-left: 20px;">Энергопринимающие устройства, планируемые к присоединению:
            <span class="info-row-value">{{ power_object|default('___') }}</span>
        </p>
    </div>
 
    <div class="section">
        <p><strong>Местонахождение объекта, по которому заключается договор:</strong></p>
        <p style="margin-left: 20px;">Адрес:
            <span class="info-row-value" style="min-width: 500px;">{{ object_address_full|raw }}</span>
        </p>
        {% if note %}
        <p style="margin-left: 20px;">Примечание: {{ note|raw }}</p>
        {% endif %}
        <p style="margin-left: 20px;">Общая площадь помещения:
            <span class="info-row-value">{{ area|default('___') }}</span> кв. м.
        </p>
        <p style="margin-left: 20px;">Количество лиц, постоянно проживающих в помещении:
            <span class="info-row-value">{{ residents_count|default('___') }}</span>
        </p>
        <p style="margin-left: 20px;">Максимальная мощность электроприемников:
            <span class="info-row-value">{{ max_power|default('___') }}</span> кВт
        </p>
        <p style="margin-left: 20px;">Уровень напряжения:
            <span class="info-row-value">{{ voltage_level|default('___') }}</span>
        </p>
        {% if act_reference %}
        <p style="margin-left: 20px;">Реквизиты акта об определении границы раздела:
            <span class="info-row-value" style="min-width: 350px;">{{ act_reference|raw }}</span>
        </p>
        {% endif %}
        <p style="margin-left: 20px;">Сведения о направлениях потребления электроэнергии:</p>
        <p style="margin-left: 40px;">{{ consumption_purpose|default('___')|raw }}</p>
        <p style="margin-left: 20px;">Приборы учета установлены:
            <span class="info-row-value">{{ has_meter|default('___') }}</span>
        </p>
        <p style="margin-left: 20px;">Тариф:
            <span class="info-row-value" style="min-width: 450px;">{{ tariff_choice|default('___')|raw }}</span>
        </p>
        {% if supply_period %}
        <p style="margin-left: 20px;">Срок электроснабжения:
            <span class="info-row-value">{{ supply_period|raw }}</span>
        </p>
        {% endif %}
    </div>
 
    <div class="section">
        <p><strong>7.</strong> Документы:</p>
        <p style="margin-left: 20px;">Платежные документы прошу предоставлять:</p>
        <p style="margin-left: 40px;">{{ payment_delivery|default('___')|raw }}</p>
        <p style="margin-left: 20px;">Уведомления прошу направлять:</p>
        <p style="margin-left: 40px;">{{ notification_delivery|default('___')|raw }}</p>
    </div>
 
    <div class="section" style="margin-top: 15px;">
        <p>В соответствии с ФЗ от 27.07.2006 №152-ФЗ "О персональных данных"
           даю своё согласие на обработку своих персональных данных: <strong>{{ consent|raw }}</strong></p>
    </div>
 
    <div class="footer">
        <p><strong>Дата:</strong> {{ created_at|default("now"|date("d.m.Y"))|raw }}</p>
 
        <table class="signature-block" style="margin-top: 30px;">
            <tr>
                <td style="width: 50px;">Подпись:</td>
                <td style="width: 200px;"><div class="signature-line"></div></td>
                <td style="width: 30px;"></td>
                <td style="width: 120px;">Расшифровка:</td>
                <td style="width: 200px;"><div class="signature-line"></div></td>
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
TWIG;
    }
 
    /**
     * Twig-содержимое шаблона для юридических лиц.
     */
    public static function legalContent(): string
    {
        return <<<'TWIG'
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
        <h1>Заявка № {{ application_id|default('___') }}</h1>
        <p>на заключение договора энергоснабжения</p>
        <p><strong>Юридическое лицо</strong></p>
    </div>
 
    <div class="content">
        <div class="section">
            <div class="section-title">1. Сведения об организации</div>
            <table class="info-table">
                <tr><td>Полное наименование</td><td><strong>{{ company_name|default('Не указано') }}</strong></td></tr>
                <tr><td>ИНН</td><td>{{ inn|default('Не указан') }}</td></tr>
                {% if kpp %}<tr><td>КПП</td><td>{{ kpp|raw }}</td></tr>{% endif %}
                {% if ogrn %}<tr><td>ОГРН</td><td>{{ ogrn|raw }}</td></tr>{% endif %}
            </table>
        </div>
 
        <div class="section">
            <div class="section-title">2. Контактная информация</div>
            <table class="info-table">
                {% if contact_person %}<tr><td>Контактное лицо</td><td><strong>{{ contact_person|raw }}</strong></td></tr>{% endif %}
                <tr><td>Телефон</td><td>{{ phone|default('Не указан') }}</td></tr>
                <tr><td>Email</td><td>{{ user_email|default('Не указана') }}</td></tr>
            </table>
        </div>
 
        <div class="section">
            <div class="section-title">3. Адрес объекта энергопотребления</div>
            <table class="info-table">
                <tr><td>Полный адрес</td><td><strong>{{ object_address_full|raw }}</strong></td></tr>
            </table>
        </div>
 
        <div class="section">
            <div class="section-title">4. Условия договора</div>
            <p>Прошу заключить договор энергоснабжения на условиях III категории надёжности.</p>
        </div>
    </div>
 
    <div class="footer">
        <p><strong>Дата подачи заявки:</strong> {{ created_at|default("now"|date("d.m.Y H:i"))|raw }}</p>
        <div class="signature-block">
            <div><p>Руководитель:</p><p class="signature-field"></p><p style="font-size: 10px; color: #666;">ФИО, подпись</p></div>
            <div><p>М.П.</p><div class="stamp-area">Место печати</div></div>
            <div><p>Дата:</p><p class="signature-field" style="width: 120px;"></p></div>
        </div>
    </div>
</body>
</html>
TWIG;
    }
}