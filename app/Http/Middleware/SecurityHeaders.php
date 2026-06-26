<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Запрет загрузки страницы во фрейме — защита от clickjacking.
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Браузер не должен угадывать Content-Type — только явно заданный.
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Управление Referrer — не передаём полный URL на внешние сайты.
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Запрет доступа к микрофону, камере, геолокации через браузерные API.
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // HSTS — браузер всегда использует HTTPS после первого визита.
        // Включать ТОЛЬКО на продакшне с HTTPS, на локалке безвреден но бесполезен.
        if (app()->environment('production')) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        // Content Security Policy — разрешаем только нужные источники.
        // 'unsafe-inline' для script-src нужен пока Inertia/Vite не настроены
        // на nonce. После настройки nonce — убрать 'unsafe-inline'.
        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline'",   // TODO: заменить на nonce после настройки
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: blob:",           // data: нужен для превью файлов в браузере
            "font-src 'self' data:",
            "connect-src 'self'",
            "frame-ancestors 'self'",               // дублирует X-Frame-Options для новых браузеров
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]));

        return $response;
    }
}