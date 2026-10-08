<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Заявление о заключении договора электроснабжения (ИП)</title>
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
        .header-right p {
            margin: 0;
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
        .info-row-value {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 200px;
            text-align: center;
        }
        .caption {
            font-size: 9pt;
            color: #666;
            margin: 0;
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
@php
    /**
     * Короткий доступ к полю заявки.
     *
     * Нельзя обойтись одним `?? '___'`: поля из $mainInfo (full_name, phone, inn)
     * приходят пустой строкой, а не null, и оператор ?? их пропускает.
     * Поля самой формы PdfDataPreparator уже отдаёт как '___', если они пустые.
     */
    $v = fn (string $key, string $fallback = '___') => ! empty($data[$key]) ? $data[$key] : $fallback;
@endphp

    {{-- Шапка --}}
    <div class="header-right">
        <p>{{ config('organization.addressee.position', 'Генеральному директору') }}</p>
        <p>{{ config('organization.name', 'ООО «Заринская горэлектросеть»') }}</p>
        <p>{{ config('organization.addressee.name', 'Гороховой Е.В.') }}</p>
    </div>

    {{-- Заголовок --}}
    <div class="title">
        <h1>Заявление</h1>
        <p><strong>о заключении договора электроснабжения</strong></p>
        <p>(индивидуальный предприниматель)</p>
    </div>

    {{-- 1. Заявитель и срок договора --}}
    <div class="section">
        <p style="text-align: center;">
            <span class="info-row-value" style="min-width: 460px;">ИП {{ $v('full_name') }}</span>
        </p>
        <p class="caption" style="text-align: center;">(Ф.И.О.)</p>

        <p style="margin-top: 10px;">просит Вас заключить договор электроснабжения с
            <span class="info-row-value" style="min-width: 260px;">{{ $v('supply_period') }}</span>
        </p>
        <p class="caption" style="margin-left: 320px;">(период времени)</p>
    </div>

    {{-- 2. Адреса --}}
    <div class="section">
        <p>Адрес регистрации:
            <span class="info-row-value" style="min-width: 400px;">{{ $v('registration_address') }}</span>
        </p>
        <p>Фактический адрес:
            <span class="info-row-value" style="min-width: 400px;">{{ $v('actual_address') }}</span>
        </p>
        <p class="caption">(адрес потребителя для почтовых отправлений при исполнении договора)</p>
    </div>

    {{-- 3. Контакты --}}
    <div class="section">
        <p>Телефон, e-mail:
            <span class="info-row-value" style="min-width: 180px;">{{ $v('phone') }}</span>,
            <span class="info-row-value" style="min-width: 240px;">{{ $v('email', $v('user_email')) }}</span>
        </p>
    </div>

    {{-- 4. Паспортные данные --}}
    <div class="section">
        <p>Паспорт: серия и номер
            <span class="info-row-value" style="min-width: 180px;">{{ $v('passport') }}</span>
            дата выдачи
            <span class="info-row-value" style="min-width: 140px;">{{ $v('passport_issue_date') }}</span>
        </p>
        <p>Кем выдан:
            <span class="info-row-value" style="min-width: 430px;">{{ $v('passport_issue') }}</span>
        </p>
    </div>

    {{-- 5. Реквизиты --}}
    <div class="section">
        <p>ОГРНИП
            <span class="info-row-value" style="min-width: 170px;">{{ $v('ogrn') }}</span>
            ИНН
            <span class="info-row-value" style="min-width: 150px;">{{ $v('inn') }}</span>
        </p>
        <p>ОКВЭД
            <span class="info-row-value" style="min-width: 430px;">{{ $v('okved') }}</span>
        </p>
    </div>

    {{-- 6. Электронный документооборот --}}
    <div class="section">
        <p>Сведения об электронном документообороте через оператора ЭДО с использованием усиленной
            квалифицированной подписи:</p>
        <p style="margin-left: 20px;">
            <span class="info-row-value" style="min-width: 450px;">{{ $v('edo_operator') }}</span>
        </p>
        <p class="caption" style="margin-left: 20px;">(указать оператора ЭДО)</p>
    </div>

    {{-- 7. Сведения об объектах потребителя --}}
    <div class="section">
        <p><strong>Сведения об объектах потребителя:</strong></p>

        <p style="margin-left: 20px;">Наименование:
            <span class="info-row-value" style="min-width: 380px;">{{ $v('object_category') }}</span>
        </p>
        <p class="caption" style="margin-left: 20px;">
            (указать категорию объектов: промышленное предприятие, учреждение, торговая палатка и т.п.)
        </p>

        <p style="margin-left: 20px; margin-top: 8px;">Адрес:
            <span class="info-row-value" style="min-width: 420px;">{{ $v('object_address') }}</span>
        </p>
        <p class="caption" style="margin-left: 20px;">
            (в случае расположения объектов по разным адресам применён порядковый №)
        </p>

        <p style="margin-left: 20px; margin-top: 8px;">График работы объектов:
            <span class="info-row-value" style="min-width: 320px;">{{ $v('object_schedule') }}</span>
        </p>
    </div>

    {{-- 8. Технические характеристики энергоснабжения --}}
    <div class="section">
        <p>Избранный покупателем вариант ценовой категории:
            <span class="info-row-value" style="min-width: 240px;">{{ $v('price_category') }}</span>
        </p>
        <p class="caption">
            (Максимальная мощность энергопринимающих устройств в границах балансовой принадлежности
            Потребителя составляет менее 670 кВт)
        </p>

        <p style="margin-top: 8px;">Плановое количество электроэнергии на год:
            <span class="info-row-value" style="min-width: 220px;">{{ $v('planned_consumption') }}</span> кВт*ч
        </p>

        <p>Уровень напряжения:
            <span class="info-row-value" style="min-width: 380px;">{{ $v('voltage_level') }}</span>
        </p>

        <p>Энергопринимающие устройства покупателя относятся к
            <span class="info-row-value" style="min-width: 120px;">{{ $v('reliability_category') }}</span>
            категории надёжности снабжения электроэнергией.
        </p>

        <p>Максимальная мощность:
            <span class="info-row-value" style="min-width: 260px;">{{ $v('max_power') }}</span> кВт.
        </p>

        <p>Показания электросчётчика на момент заключения договора:
            <span class="info-row-value" style="min-width: 200px;">{{ $v('meter_reading_at_signing') }}</span>
        </p>
    </div>

    {{-- Согласие на обработку персональных данных --}}
    <div class="section" style="margin-top: 15px;">
        <p>В соответствии с ФЗ от 27.07.2006 №152-ФЗ «О персональных данных» даю своё согласие
            на обработку своих персональных данных: <strong>{{ $v('consent', 'Нет') }}</strong></p>
    </div>

    {{-- Подпись --}}
    <div class="footer">
        <p><strong>Дата:</strong> {{ $v('created_at', date('d.m.Y')) }}</p>

        <table class="signature-block" style="margin-top: 30px;">
            <tr>
                <td style="width: 80px;">Подпись:</td>
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
            <p>Заявление № {{ $v('application_id') }}</p>
            <p style="font-style: italic; margin-top: 10px;">
                Документ сгенерирован автоматически в Личном кабинете потребителя.
                Оригинал подписи проставляется при очном заключении договора.
            </p>
        </div>
    </div>
</body>
</html>