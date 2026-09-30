<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <style>
        body  { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #000; }
        h1    { font-size: 15px; margin: 0 0 4px; }
        .sub  { font-size: 10px; color: #555; margin-bottom: 16px; }    
        h2    { font-size: 12px; margin: 18px 0 6px; }
        table { width: 100%; border-collapse: collapse; }
        td    { padding: 4px 6px; border: 1px solid #999; vertical-align: top; word-break: break-word; }
        td.k  { width: 38%; background: #f2f2f2; }
        .hash { font-family: DejaVu Sans Mono, monospace; font-size: 9px; word-break: break-all; }
        .note { margin-top: 18px; font-size: 9px; color: #555; line-height: 1.5; }
    </style>
</head>
<body>

<h1>Протокол подписания документа</h1>
<div class="sub">Сформирован автоматически {{ $generated_at }} (время местное, UTC+7)</div>

<h2>Документ</h2>
<table>
    <tr><td class="k">Наименование</td><td>Договор энергоснабжения №{{ $contract_number }}</td></tr>
    <tr><td class="k">Файл</td><td>{{ $file_name }}</td></tr>
    <tr><td class="k">Хеш файла (SHA-256)</td><td class="hash">{{ $file_hash }}</td></tr>
    <tr><td class="k">Основание подписания</td><td>{{ $signing_reason }}</td></tr>
</table>

<h2>Стороны</h2>
<table>
    <tr><td class="k">Исполнитель</td><td>{{ $organization_name }}</td></tr>
    <tr><td class="k">Потребитель</td><td>{{ $client_name }}</td></tr>
    @if($client_inn)
        <tr><td class="k">ИНН потребителя</td><td>{{ $client_inn }}</td></tr>
    @endif
    @if($account_number)
        <tr><td class="k">Лицевой счёт</td><td>{{ $account_number }}</td></tr>
    @endif
</table>

@foreach($signatures as $signature)
    <h2>{{ $signature['signer'] }}</h2>
    <table>
        <tr><td class="k">Способ подписания</td><td>{{ $signature['method'] }}</td></tr>
        <tr><td class="k">Дата и время</td><td>{{ $signature['signed_at'] }}</td></tr>
        <tr><td class="k">Хеш подписанного файла</td><td class="hash">{{ $signature['document_hash'] }}</td></tr>
        @foreach($signature['details'] as $label => $value)
            <tr><td class="k">{{ $label }}</td><td>{{ $value }}</td></tr>
        @endforeach
    </table>
@endforeach

<div class="note">
    Документ сформирован автоматически информационной системой и не требует
    собственноручной подписи. Сведения о подписании хранятся в системе
    и могут быть предоставлены по запросу.
    <br><br>
    Совпадение хеша подписанного файла с хешем документа подтверждает,
    что подписан именно тот файл, который указан в настоящем протоколе.
</div>

</body>
</html>