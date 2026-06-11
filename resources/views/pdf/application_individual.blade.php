<!-- <!DOCTYPE html>
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
        {{-- СЕКЦИЯ 1: Персональная информация --}}
        <div class="section">
            <div class="section-title">1. Персональная информация</div>
            <table class="info-table">
                <tr>
                    <td>ФИО заявителя</td>
                    <td><strong>{{ $data['full_name'] ?? trim(($data['last_name'] ?? '') . ' ' . ($data['first_name'] ?? '') . ' ' . ($data['middle_name'] ?? '')) }}</strong></td>
                </tr>
                <tr>
                    <td>Паспорт (серия и номер)</td>
                    <td>{{ $data['passport_series'] ?? 'Не указан' }}</td>
                </tr>
                <tr>
                    <td>Кем выдан</td>
                    <td>{{ $data['passport_issue'] ?? 'Не указано' }}</td>
                </tr>
                <tr>
                    <td>Дата выдачи</td>
                    <td>{{ $data['passport_issue_date'] ?? 'Не указана' }}</td>
                </tr>
            </table>
        </div>

        {{-- СЕКЦИЯ 2: Адрес регистрации --}}
        <div class="section">
            <div class="section-title">2. Адрес регистрации</div>
            @php
                // Собираем полный адрес из отдельных полей
                $addressParts = [];
                
                // Регион (учитываем возможный пробел в ключе)
                $region = $data['region'] ?? $data[' region'] ?? null;
                if (!empty($region)) $addressParts[] = $region;
                
                // Район
                if (!empty($data['district'])) $addressParts[] = $data['district'];
                
                // Населенный пункт
                if (!empty($data['locality'])) $addressParts[] = $data['locality'];
                
                // Улица
                if (!empty($data['street'])) $addressParts[] = 'ул. ' . $data['street'];
                
                // Дом
                if (!empty($data['house'])) $addressParts[] = 'д. ' . $data['house'];
                
                // Корпус
                if (!empty($data['corpus'])) $addressParts[] = 'корп. ' . $data['corpus'];
                
                // Квартира
                if (!empty($data['apartment'])) $addressParts[] = 'кв. ' . $data['apartment'];
                
                // Итоговый адрес
                $fullAddress = !empty($addressParts) 
                    ? implode(', ', $addressParts) 
                    : ($data['address'] ?? 'Не указан');
            @endphp
            
            <table class="info-table">
                <tr>
                    <td>Полный адрес</td>
                    <td><strong>{{ $fullAddress }}</strong></td>
                </tr>
                <tr>
                    <td>Регион</td>
                    <td>{{ $region ?? 'Не указан' }}</td>
                </tr>
                @if(!empty($data['district']))
                <tr>
                    <td>Район</td>
                    <td>{{ $data['district'] }}</td>
                </tr>
                @endif
                <tr>
                    <td>Населенный пункт</td>
                    <td>{{ $data['locality'] ?? 'Не указан' }}</td>
                </tr>
                <tr>
                    <td>Улица</td>
                    <td>{{ $data['street'] ?? 'Не указана' }}</td>
                </tr>
                <tr>
                    <td>Дом</td>
                    <td>{{ $data['house'] ?? 'Не указан' }}</td>
                </tr>
                @if(!empty($data['corpus']))
                <tr>
                    <td>Корпус</td>
                    <td>{{ $data['corpus'] }}</td>
                </tr>
                @endif
                @if(!empty($data['apartment']))
                <tr>
                    <td>Квартира</td>
                    <td>{{ $data['apartment'] }}</td>
                </tr>
                @endif
            </table>
        </div>

        {{-- СЕКЦИЯ 3: Контактная информация --}}
        <div class="section">
            <div class="section-title">3. Контактная информация</div>
            <table class="info-table">
                <tr>
                    <td>Телефон</td>
                    <td>{{ $data['phone'] ?? 'Не указан' }}</td>
                </tr>
                <tr>
                    <td>Электронная почта</td>
                    <td>{{ $data['user_email'] ?? 'Не указана' }}</td>
                </tr>
            </table>
        </div>

        {{-- СЕКЦИЯ 4: Дополнительные поля (если есть) --}}
        @php
            // Ключи которые уже выведены
            $usedKeys = [
                'client_type', 'application_id', 'created_at', 'full_name', 'user_email',
                'last_name', 'first_name', 'middle_name', 'phone', 'address',
                'passport_series', 'passport_issue', 'passport_issue_date',
                'region', ' region', 'district', 'locality', 'street', 'house', 'corpus', 'apartment',
            ];
            
            // Фильтруем - оставляем только не выведенные
            $extraFields = collect($data ?? [])->filter(function($value, $key) use ($usedKeys) {
                return !in_array($key, $usedKeys) && !empty($value) && !is_array($value);
            });
        @endphp
        
        @if($extraFields->isNotEmpty())
        <div class="section">
            <div class="section-title">4. Дополнительные сведения</div>
            <table class="info-table">
                @foreach($extraFields as $key => $value)
                <tr>
                    <td>{{ $key }}</td>
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
</html> -->

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
        .info-row-label {
            font-weight: normal;
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
        .checkbox-checked {
            font-weight: bold;
        }
        .checkbox-unchecked {
            color: #999;
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
        <p style="margin-left: 20px;">Адрес электронной почты: <span class="info-row-value" style="min-width: 300px;">{{ $data['email'] ?? $data['user_email'] ?? '___' }}</span></p>
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
        <p style="margin-left: 20px;">Уровень напряжения: <span class="info-row-value">{{ $data['voltage_level'] ?? '___' }}</span></p>
        @if(!empty($data['act_reference']))
        <p style="margin-left: 20px;">Реквизиты акта об определении границы раздела: <span class="info-row-value" style="min-width: 350px;">{{ $data['act_reference'] }}</span></p>
        @endif
        <p style="margin-left: 20px;">Сведения о направлениях потребления электроэнергии:</p>
        <p style="margin-left: 40px;">{{ $data['consumption_purpose'] ?? '___' }}</p>
        <p style="margin-left: 20px;">Приборы учета установлены: <span class="info-row-value">{{ $data['has_meter'] ?? '___' }}</span></p>
        <p style="margin-left: 20px;">Тариф: <span class="info-row-value" style="min-width: 450px;">{{ $data['tariff_choice'] ?? '___' }}</span></p>
        @if(!empty($data['supply_period']))
        <p style="margin-left: 20px;">Срок электроснабжения: <span class="info-row-value">{{ $data['supply_period'] }}</span></p>
        @endif
    </div>

    {{-- 7. Документы --}}
    <div class="section">
        <p><strong>7.</strong> Документы:</p>
        <p style="margin-left: 20px;">Платежные документы прошу предоставлять:</p>
        <p style="margin-left: 40px;">{{ $data['payment_delivery'] ?? '___' }}</p>
        <p style="margin-left: 20px;">Уведомления прошу направлять:</p>
        <p style="margin-left: 40px;">{{ $data['notification_delivery'] ?? '___' }}</p>
    </div>

    {{-- Согласие на обработку персональных данных --}}
    <div class="section" style="margin-top: 15px;">
        <p>В соответствии с ФЗ от 27.07.2006 №152-ФЗ "О персональных данных" даю своё согласие на обработку своих персональных данных: <strong>{{ isset($data['personal_info']) && (is_array($data['personal_info']) ? in_array('Да', $data['personal_info'] ?? []) : str_contains($data['personal_info'], 'Да')) ? 'Да' : 'Нет' }}</strong></p>
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