<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Заявка на заключение договора энергоснабжения (Юридическое лицо)</title>
    <style>
        body { 
            font-family: 'DejaVu Sans', Arial, sans-serif; 
            font-size: 12px; 
            line-height: 1.6; 
            margin: 0;
            padding: 40px;
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px; 
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            font-size: 16px;
            text-transform: uppercase;
            margin: 0 0 10px 0;
        }
        .header p {
            margin: 0;
            color: #666;
        }
        .content { 
            margin: 20px 0; 
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ccc;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .info-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
        }
        .info-table td:first-child {
            width: 40%;
            background-color: #f9f9f9;
            font-weight: 500;
        }
        .footer { 
            margin-top: 50px; 
            padding-top: 20px;
            border-top: 1px solid #ccc;
        }
        .signature-block {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        .signature-field {
            border-bottom: 1px solid #333;
            width: 200px;
            display: inline-block;
        }
        .stamp-area {
            width: 150px;
            height: 150px;
            border: 1px dashed #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ccc;
            font-size: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Заявка № {{ $data['application_id'] ?? '___' }}</h1>
        <p>на заключение договора энергоснабжения</p>
        <p><strong>Юридическое лицо</strong></p>
    </div>

    <div class="content">
        <div class="section">
            <div class="section-title">1. Сведения об организации</div>
            <table class="info-table">
                <tr>
                    <td>Полное наименование</td>
                    <td><strong>{{ $data['company_name'] ?? '' }}</strong></td>
                </tr>
                <tr>
                    <td>ИНН</td>
                    <td>{{ $data['inn'] ?? '' }}</td>
                </tr>
                <tr>
                    <td>КПП</td>
                    <td>{{ $data['kpp'] ?? '—' }}</td>
                </tr>
                <tr>
                    <td>ОГРН</td>
                    <td>{{ $data['ogrn'] ?? '—' }}</td>
                </tr>
                <tr>
                    <td>Юридический адрес</td>
                    <td>{{ $data['legal_address'] ?? $data['address'] ?? '' }}</td>
                </tr>
                <tr>
                    <td>Фактический адрес</td>
                    <td>{{ $data['actual_address'] ?? $data['address'] ?? '' }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">2. Контактная информация</div>
            <table class="info-table">
                <tr>
                    <td>Контактное лицо</td>
                    <td><strong>{{ $data['contact_person'] ?? '' }}</strong></td>
                </tr>
                <tr>
                    <td>Телефон</td>
                    <td>{{ $data['phone'] ?? '' }}</td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>{{ $data['user_email'] ?? '' }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">3. Сведения об объекте энергопотребления</div>
            <table class="info-table">
                <tr>
                    <td>Адрес объекта</td>
                    <td>{{ $data['address'] ?? '' }}</td>
                </tr>
                <tr>
                    <td>Тип объекта</td>
                    <td>{{ $data['object_type'] ?? 'Нежилое помещение' }}</td>
                </tr>
                @if(isset($data['power_capacity']))
                <tr>
                    <td>Запрашиваемая мощность</td>
                    <td>{{ $data['power_capacity'] }} кВт</td>
                </tr>
                @endif
                @if(isset($data['voltage_level']))
                <tr>
                    <td>Уровень напряжения</td>
                    <td>{{ $data['voltage_level'] }}</td>
                </tr>
                @endif
            </table>
        </div>

        @if(isset($data['meter_info']))
        <div class="section">
            <div class="section-title">4. Сведения о приборах учёта</div>
            <table class="info-table">
                <tr>
                    <td>Номер счётчика</td>
                    <td>{{ $data['meter_info']['number'] ?? '' }}</td>
                </tr>
                <tr>
                    <td>Тип счётчика</td>
                    <td>{{ $data['meter_info']['type'] ?? '' }}</td>
                </tr>
                <tr>
                    <td>Дата поверки</td>
                    <td>{{ $data['meter_info']['verification_date'] ?? '' }}</td>
                </tr>
            </table>
        </div>
        @endif

        <div class="section">
            <div class="section-title">5. Условия договора</div>
            <p>Прошу заключить договор энергоснабжения на следующих условиях:</p>
            <ul>
                <li>Категория надёжности электроснабжения: {{ $data['reliability_category'] ?? 'II (вторая)' }}</li>
                <li>Максимальная мощность: {{ $data['max_power'] ?? 'до 150 кВт' }}</li>
                <li>Целевое использование: {{ $data['purpose'] ?? 'Производственные нужды' }}</li>
            </ul>
        </div>

        <!-- Дополнительные данные из формы -->
        @php
            $excludeKeys = ['last_name', 'first_name', 'middle_name', 'address', 'phone', 'client_type', 'application_id', 'user_email', 'created_at', 'client_type_name', 'company_name', 'inn', 'kpp', 'ogrn', 'contact_person'];
        @endphp
        @foreach($data as $key => $value)
            @if(!in_array($key, $excludeKeys) && !empty($value) && !is_array($value))
                @if($loop->first)
                <div class="section">
                    <div class="section-title">Дополнительные сведения</div>
                    <table class="info-table">
                @endif
                <tr>
                    <td>{{ $key }}</td>
                    <td>{{ $value }}</td>
                </tr>
                @if($loop->last)
                    </table>
                </div>
                @endif
            @endif
        @endforeach
    </div>

    <div class="footer">
        <p><strong>Дата подачи заявки:</strong> {{ $data['created_at'] ?? date('d.m.Y H:i') }}</p>
        <p style="color: #666; font-size: 10px; margin-top: 20px;">
            Документ сгенерирован автоматически системой «Личный кабинет потребителя».<br>
            Подпись уполномоченного лица будет проставлена при заключении договора.
        </p>

        <div class="signature-block">
            <div>
                <p>Руководитель:</p>
                <p class="signature-field"></p>
                <p style="font-size: 10px; color: #666;">ФИО, подпись</p>
            </div>
            <div>
                <p>М.П.</p>
                <div class="stamp-area">Место печати</div>
            </div>
            <div>
                <p>Дата:</p>
                <p class="signature-field" style="width: 120px;"></p>
            </div>
        </div>
    </div>
</body>
</html>