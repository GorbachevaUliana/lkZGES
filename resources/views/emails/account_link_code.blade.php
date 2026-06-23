<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Код подтверждения</title>
    <style>
        body { font-family: Arial, sans-serif; background: #F4F7FE; margin: 0; padding: 40px 20px; }
        .wrapper { max-width: 520px; margin: 0 auto; }
        .card {
            background: #ffffff;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.07);
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 32px;
        }
        .logo-icon {
            width: 44px; height: 44px;
            background: #4318FF;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
        }
        .logo-text { color: #1B2559; font-size: 15px; font-weight: bold; line-height: 1.3; }
        .logo-sub  { color: #A3AED0; font-size: 12px; }
        h1 { color: #1B2559; font-size: 20px; margin: 0 0 12px; }
        p  { color: #485585; font-size: 14px; line-height: 1.6; margin: 0 0 16px; }
        .code-block {
            background: #F4F7FE;
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            margin: 28px 0;
        }
        .code {
            font-size: 40px;
            font-weight: bold;
            letter-spacing: 10px;
            color: #4318FF;
        }
        .code-note { color: #A3AED0; font-size: 13px; margin-top: 8px; }
        .warning {
            background: #FFF8E1;
            border-left: 4px solid #FFB800;
            border-radius: 6px;
            padding: 12px 16px;
            color: #7A5C00;
            font-size: 13px;
            margin-top: 24px;
        }
        .footer { margin-top: 32px; color: #A3AED0; font-size: 12px; text-align: center; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <div class="logo">
            <div class="logo-icon">⚡</div>
            <div>
                <div class="logo-text">Личный кабинет</div>
                <div class="logo-sub">ООО «Заринская горэлектросеть»</div>
            </div>
        </div>

        <h1>Подтверждение привязки лицевого счёта</h1>

        <p>Здравствуйте, <strong>{{ $clientName }}</strong>.</p>

        <p>
            Кто-то попытался привязать ваш лицевой счёт к личному кабинету.
            Если это были вы — введите код ниже на странице подтверждения.
        </p>

        <div class="code-block">
            <div class="code">{{ $code }}</div>
            <div class="code-note">Код действителен 15 минут</div>
        </div>

        <div class="warning">
            ⚠️ Если вы не запрашивали привязку — проигнорируйте это письмо.
            Ваши данные в безопасности, никаких действий не требуется.
        </div>
    </div>
    <div class="footer">
        ООО «Заринская горэлектросеть» · Это письмо отправлено автоматически, не отвечайте на него.
    </div>
</div>
</body>
</html>