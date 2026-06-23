<?php

namespace App\Http\Controllers;

use App\Mail\AccountLinkCode;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class AccountController extends Controller
{
    /**
     * GET /welcome-step — единственный GET-маршрут для обоих шагов.
     *
     * Состояние (какой шаг показывать, email) хранится в сессии,
     * а не в URL. Это решает проблему 405 при обновлении страницы:
     * браузер всегда делает GET /welcome-step, а не GET /account/link.
     */
    public function index()
    {
        $user = auth()->user();

        $client = $user->client;

        if ($client) {
            $hasActiveProperties = $client->properties()
                ->where('status', 'active')
                ->whereNotNull('account_number')
                ->where('account_number', '!=', '')
                ->exists();

            if ($hasActiveProperties) {
                return redirect()->route('client.dashboard');
            }
        }

        return Inertia::render('WelcomePage', [
            // Шаг и maskedEmail берём из flash-сессии (выставляются в link/verify).
            // По умолчанию — шаг 1 (форма ввода ЛС + ФИО).
            'step'        => session('link_step', 'link'),
            'maskedEmail' => session('link_masked_email'),
        ]);
    }

    /**
     * POST /account/link — шаг 1: найти клиента, отправить код.
     *
     * Намеренно не сообщаем, найден ли клиент — защита от перебора.
     * После обработки ВСЕГДА делаем redirect на GET /welcome-step (PRG-паттерн).
     */
    public function link(Request $request)
    {
        $request->validate([
            'account_number' => 'required|string',
            'last_name'      => 'required|string|max:100',
            'first_name'     => 'required|string|max:100',
            'middle_name'    => 'required|string|max:100',
        ]);

        $user = auth()->user();

        $client = Client::whereHas('properties', function ($q) use ($request) {
                $q->where('account_number', $request->account_number)
                  ->where('status', 'active');
            })
            ->whereRaw('LOWER(last_name)   = LOWER(?)', [$request->last_name])
            ->whereRaw('LOWER(first_name)  = LOWER(?)', [$request->first_name])
            ->whereRaw('LOWER(middle_name) = LOWER(?)', [$request->middle_name])
            ->first();

        // Клиент не найден или ЛС уже привязан к другому — тихо уходим на шаг 2.
        // Код не отправляем, link_code оставляем null.
        if (! $client || ($client->user_id && $client->user_id !== $user->id)) {
            $user->forceFill([
                'link_code'         => null,
                'link_code_expires' => null,
                'link_client_id'    => null,
            ])->save();

            return redirect()->route('welcome.step')
                ->with('link_step', 'verify');
        }

        // Клиент найден — генерируем код, отправляем письмо.
        $code = (string) random_int(100000, 999999);

        $user->forceFill([
            'link_code'         => Hash::make($code),
            'link_code_expires' => now()->addMinutes(15),
            'link_client_id'    => $client->id,
        ])->save();

        $sendTo   = $client->email ?? $user->email;
        $fullName = trim("{$client->last_name} {$client->first_name} {$client->middle_name}");

        Mail::to($sendTo)->send(new AccountLinkCode($code, $fullName));

        return redirect()->route('welcome.step')
            ->with('link_step', 'verify')
            ->with('link_masked_email', $this->maskEmail($sendTo));
    }

    /**
     * POST /account/verify — шаг 2: проверить код, привязать ЛС.
     *
     * На ошибке тоже делаем redirect на GET /welcome-step с сохранением
     * шага 'verify' в сессии — иначе браузер при обновлении страницы
     * делает GET /account/verify и получает 405 Method Not Allowed.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = auth()->user();

        // Код просрочен или не запрашивался.
        if (! $user->link_code || ! $user->link_code_expires || now()->isAfter($user->link_code_expires)) {
            return redirect()->route('welcome.step')
                ->with('link_step', 'verify')
                ->withErrors(['code' => 'Код истёк или недействителен. Запросите новый.']);
        }

        // Неверный код.
        if (! Hash::check($request->code, $user->link_code)) {
            return redirect()->route('welcome.step')
                ->with('link_step', 'verify')
                ->with('link_masked_email', session('link_masked_email'))
                ->withErrors(['code' => 'Неверный код. Проверьте письмо и попробуйте снова.']);
        }

        $client = Client::find($user->link_client_id);

        // Клиент мог быть привязан другим пользователем пока шёл процесс.
        if (! $client || ($client->user_id && $client->user_id !== $user->id)) {
            $this->clearLinkCode($user);
            return redirect()->route('welcome.step')
                ->withErrors(['code' => 'Не удалось привязать лицевой счёт. Обратитесь в службу поддержки.']);
        }

        // Всё верно — привязываем и чистим временные поля.
        $client->update(['user_id' => $user->id]);
        $user->forceFill(['role' => 'client'])->save();
        $this->clearLinkCode($user);

        return redirect()->route('client.dashboard');
    }

    private function clearLinkCode($user): void
    {
        $user->forceFill([
            'link_code'         => null,
            'link_code_expires' => null,
            'link_client_id'    => null,
        ])->save();
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2);
        $visible = mb_substr($local, 0, min(2, mb_strlen($local)));
        return $visible . '***@' . $domain;
    }
}