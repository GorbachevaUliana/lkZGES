<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 20px; text-transform: uppercase; }
        .content { margin: 20px 0; }
        .footer { margin-top: 50px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        td { padding: 5px; border-bottom: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Заявка № {{ $application_id }}</h1>
        <p>на присоединение к электрическим сетям</p>
    </div>

    <div class="content">
        <p>Я, <strong>{{ $data['last_name'] }} {{ $data['first_name'] }} {{ $data['middle_name'] ?? '' }}</strong>,</p>
        <p>проживающий по адресу: {{ $data['address'] }},</p>
        <p>прошу заключить договор энергоснабжения. Контактные данные: {{ $data['phone'] }}, {{ $user_email }}.</p>
        
        <h3>Дополнительные данные из формы:</h3>
        <table>
            @foreach($data as $key => $value)
                @if(!in_array($key, ['last_name', 'first_name', 'middle_name']))
                <tr>
                    <td><strong>{{ $key }}:</strong></td>
                    <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                </tr>
                @endif
            @endforeach
        </table>
    </div>

    <div class="footer">
        <p>Дата подачи: {{ date('d.m.Y H:i') }}</p>
        <p>Сгенерировано автоматически системой ЛК.</p>
    </div>
</body>
</html>
