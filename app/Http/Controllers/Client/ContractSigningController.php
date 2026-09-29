<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Services\ContractPepSigningService;
use App\Services\ContractUkepSigningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContractSigningController extends Controller
{
    public function __construct(
        private ContractPepSigningService $pepSigning,
    ) {}

    /**
     * Запросить код для подписания.
     *
     * Все проверки — чей это договор, ждёт ли он подписи, не рано ли
     * запрашивать новый код — делает сервис. Контроллер только передаёт.
     */
    public function sendCode(Request $request, Contract $contract): RedirectResponse
    {
        $code = $this->pepSigning->sendCode($contract, $request->user());
        return back()->with('success', 'Код отправлен на ' . $this->mask($code->sent_to));
    }

    public function sign(Request $request, Contract $contract): RedirectResponse
    {
        $request->validate(
            ['code' => ['required', 'digits:6']],
            [
                'code.required' => 'Введите код из письма.',
                'code.digits'   => 'Код состоит из 6 цифр.',
            ]
        );

        $this->pepSigning->confirm(
            $contract,
            $request->user(),
            $request->input('code'),
            $request->ip(),
            $request->userAgent(),
        );

        return back()->with('success', 'Договор подписан.');
    }

    /**
     * ivan@mail.ru → i***@mail.ru
     *
     * Полный адрес хранится в базе — он нужен как доказательство.
     * Маскировка только для показа на экране.
     */
    private function mask(string $email): string
    {
        [$name, $domain] = array_pad(explode('@', $email, 2), 2, '');
        return mb_substr($name, 0, 1) . '***' . ($domain ? '@' . $domain : '');
    }

    /**
     * Скачивание договора клиентом
     * 
     * Пока договор ждет подписи, записи Document для него в списке нет
     * файл выдается отсюда с проверкой владельца
     */
    public function download(Request $request, Contract $contract)
    {
        if ($contract->client?->user_id !== $request->user()->id) {
            abort (403);
        }

        if (! Storage::disk('local')->exists($contract->file_path)) {
            abort(404);
        }

        return Storage::disk('local')->download($contract->file_path, $contract->original_name);
    }

    /**
     * Подписание договора электронной подписью (юрлица и ИП).
     *
     * Отдельного FormRequest тут нет: кроме авторизации (которую даёт
     * группа маршрутов) проверять нечего — принадлежность договора
     * и его состояние проверяет сервис.
     */
    public function signWithUkep(
        Request $request,
        Contract $contract,
        ContractUkepSigningService $ukepSigning
    ): RedirectResponse {
        $request->validate(
            ['file' => ['required', 'file', 'extensions:sig,p7s', 'max:1024']],
            [
                'file.required'   => 'Выберите файл подписи.',
                'file.extensions' => 'Файл подписи должен иметь расширение .sig или .p7s.',
                'file.max'        => 'Файл подписи не должен превышать 1 МБ.',
            ]
        );

        $ukepSigning->sign(
            $contract,
            $request->user(),
            $request->file('file'),
            $request->ip(),
            $request->userAgent(),
        );

        return back()->with('success', 'Договор подписан.');
    }
}