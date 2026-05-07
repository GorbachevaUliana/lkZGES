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
        <h1>Заявка № {{ $application_id ?? '___' }}</h1>
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
                    <td><strong>{{ $full_name ?? trim(($data['last_name'] ?? '') . ' ' . ($data['first_name'] ?? '') . ' ' . ($data['middle_name'] ?? '')) }}</strong></td>
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
                // Собираем полный адрес
                $addressParts = [];
                
                $region = $data['region'] ?? $data[' region'] ?? null;
                if (!empty($region)) $addressParts[] = $region;
                if (!empty($data['district'])) $addressParts[] = $data['district'];
                if (!empty($data['locality'])) $addressParts[] = $data['locality'];
                if (!empty($data['street'])) $addressParts[] = 'ул. ' . $data['street'];
                if (!empty($data['house'])) $addressParts[] = 'д. ' . $data['house'];
                if (!empty($data['corpus'])) $addressParts[] = 'корп. ' . $data['corpus'];
                if (!empty($data['apartment'])) $addressParts[] = 'кв. ' . $data['apartment'];
                
                $fullAddress = !empty($addressParts) ? implode(', ', $addressParts) : ($address ?? 'Не указан');
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
                    <td>{{ $phone ?? 'Не указан' }}</td>
                </tr>
                <tr>
                    <td>Электронная почта</td>
                    <td>{{ $user_email ?? 'Не указана' }}</td>
                </tr>
            </table>
        </div>

        {{-- СЕКЦИЯ 4: Дополнительные поля --}}
        @php
            $usedKeys = [
                'client_type', 'application_id', 'created_at', 'full_name', 'user_email',
                'last_name', 'first_name', 'middle_name', 'phone', 'address',
                'passport_series', 'passport_issue', 'passport_issue_date',
                'region', ' region', 'district', 'locality', 'street', 'house', 'corpus', 'apartment',
            ];
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
        <p><strong>Дата подачи заявки:</strong> {{ $created_at ?? date('d.m.Y H:i') }}</p>

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
HTML;

        // Шаблон для юридических лиц
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
                'name' => 'Заявка (физическое лицо)',
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
                'name' => 'Заявка (юридическое лицо)',
                'client_type' => 'legal',
                'document_type' => 'application',
                'content' => $legalContent,
                'is_active' => true,
            ]
        );

        $this->command->info('PDF шаблоны успешно созданы/обновлены');
    }
}