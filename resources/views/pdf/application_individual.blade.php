<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Заявка на заключение договора энергоснабжения</title>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            src: local('DejaVu Sans');
            font-weight: normal;
            font-style: normal;
        }
        body { 
            font-family: 'DejaVu Sans', Arial, sans-serif; 
            font-size: 12px; 
            line-height: 1.5; 
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px; 
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
            text-transform: uppercase;
            margin: 0 0 5px 0;
        }
        .header p {
            margin: 0;
            font-size: 14px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 8px;
            padding-bottom: 3px;
            border-bottom: 1px solid #ccc;
            background-color: #eee;
            padding-left: 5px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 6px 10px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        .info-table td:first-child {
            width: 35%;
            background-color: #fcfcfc;
            font-weight: bold;
        }
        .footer { 
            margin-top: 40px; 
            font-size: 11px;
        }
        .signature-block {
            margin-top: 30px;
            width: 100%;
        }
        .signature-table {
            width: 100%;
            margin-top: 20px;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            width: 100%;
            height: 20px;
        }
        .caption {
            font-size: 9px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Заявка № {{ $data['application_id'] ?? '___' }}</h1>
        <p>на заключение договора энергоснабжения</p>
        <p><strong>(Физическое лицо)</strong></p>
    </div>

    <div class="content">
        <div class="section">
            <div class="section-title">1. Информация о заявителе</div>
            <table class="info-table">
                <tr>
                    <td>ФИО заявителя</td>
                    <!-- <td><strong>{{ $data['last_name'] ?? '' }} {{ $data['first_name'] ?? '' }} {{ $data['middle_name'] ?? '' }}</strong></td> -->
                    <td><strong>{{ $data['full_name'] ?? 'Не указано' }}</strong></td>
                </tr>
                <tr>
                    <td>Адрес регистрации</td>
                    <td>{{ $data['address'] ?? 'Не указан' }}</td>
                </tr>
                <tr>
                    <td>Контактный телефон</td>
                    <td>{{ $data['phone'] ?? 'Не указан' }}</td>
                </tr>
                <tr>
                    <td>Электронная почта</td>
                    <td>{{ $data['user_email'] ?? 'Не указана' }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">2. Сведения об объекте энергопотребления</div>
            <table class="info-table">
                <tr>
                    <td>Тип объекта</td>
                    <td>{{ $data['object_type'] ?? 'Жилой дом / квартира' }}</td>
                </tr>
                @if(!empty($data['power_capacity']))
                <tr>
                    <td>Запрашиваемая мощность</td>
                    <td>{{ $data['power_capacity'] }} кВт</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- @php
            $excludeKeys = [
                'last_name', 'first_name', 'middle_name', 'address', 'phone', 'full_name',
                'client_type', 'application_id', 'user_email', 'created_at', 
                'client_type_name', 'object_type', 'power_capacity', 'meter_info',
                'Фамилия', 'Имя', 'Отчество', 'Адрес', 'Телефон'
            ];
            
            $extraData = collect($data)->filter(function($value, $key) use ($excludeKeys) {
                return !in_array($key, $excludeKeys) && !empty($value) && !is_array($value);
            });
        @endphp -->
        @php
        $excludeKeys = [
            'application_id', 'created_at', 'full_name', 'last_name', 'first_name', 'middle_name',
            'address', 'phone', 'user_email', 'email', 'inn', 'kpp', 'ogrn', 'company_name',
            'legal_address', 'actual_address', 'contact_person', 'object_type', 'power_capacity',
            'client_type', 'client_type_name', 'meter_info', 'voltage_level', 'reliability_category',
            'max_power', 'purpose'
        ];

        // Мапа для красивых названий ключей
        $labels = [
            'passport_series' => 'Серия паспорта',
            'passport_number' => 'Номер паспорта',
            'snils' => 'СНИЛС',
            'inn_personal' => 'ИНН',
            'square' => 'Площадь объекта (кв.м.)',
        ];
    @endphp

    @php
        $extraData = collect($data)->filter(function($value, $key) use ($excludeKeys) {
            return !in_array($key, $excludeKeys) && !empty($value) && !is_array($value);
        });
    @endphp

    @if($extraData->isNotEmpty())
        <div class="section">
            <div class="section-title">Дополнительные сведения из заявки</div>
            <table class="info-table">
                @foreach($extraData as $key => $value)
                <tr>
                    <td>{{ $labels[$key] ?? $key }}</td>
                    <td>{{ $value }}</td>
                </tr>
                @endforeach
            </table>
        </div>
    @endif

        <div class="section" style="margin-top: 30px;">
            <p>Прошу заключить договор энергоснабжения на условиях III категории надёжности для обеспечения бытовых нужд.</p>
        </div>
    </div>

    <div class="footer">
        <p><strong>Дата подачи заявки:</strong> {{ $data['created_at'] ?? date('d.m.Y H:i') }}</p>
        
        <table class="signature-table">
            <tr>
                <td style="width: 45%;">
                    <div class="signature-line"></div>
                    <div class="caption">подпись заявителя</div>
                </td>
                <td style="width: 10%;"></td>
                <td style="width: 45%;">
                    <div class="signature-line"></div>
                    <div class="caption">расшифровка (ФИО)</div>
                </td>
            </tr>
        </table>

        <p style="color: #666; font-size: 10px; margin-top: 30px; text-align: center; font-style: italic;">
            Документ сгенерирован автоматически в Личном кабинете потребителя.<br>
            Оригинал подписи проставляется при очном заключении договора.
        </p>
    </div>
</body>
</html>