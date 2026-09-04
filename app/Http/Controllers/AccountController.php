<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Mail\AccountLinkCode;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Http\Requests\Account\LinkAccountRequest;
use App\Http\Requests\Account\VerifyAccountRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class AccountController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (in_array($user->role, [UserRole::Admin, UserRole::Staff], true)) {
            return redirect()->route('admin.dashboard');
        }

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

        $draft = app(\App\Services\DraftApplicationService::class)->currentForUser($user);

        if ($draft && $draft->template) {
            return redirect()->route('application.show', ['slug' => $draft->template->slug]);
        }

        return Inertia::render('WelcomePage', [
            'step'        => session('link_step', 'link'),
            'maskedEmail' => session('link_masked_email'),
        ]);
    }

    public function link(LinkAccountRequest $request)
    {
        $user = auth()->user();

        $client = Client::whereHas('properties', function ($q) use ($request) {
                $q->where('account_number', $request->account_number)
                  ->where('status', 'active');
            })
            ->whereRaw('LOWER(last_name)   = LOWER(?)', [$request->last_name])
            ->whereRaw('LOWER(first_name)  = LOWER(?)', [$request->first_name])
            ->whereRaw('LOWER(middle_name) = LOWER(?)', [$request->middle_name])
            ->first();

        if (! $client || ($client->user_id && $client->user_id !== $user->id)) {
            $user->forceFill([
                'link_code'         => null,
                'link_code_expires' => null,
                'link_client_id'    => null,
            ])->save();

            return redirect()->route('welcome.step')
                ->with('link_step', 'verify');
        }
        if (empty($client->email)) {
            $user->forceFill([
                'link_code'         => null,
                'link_code_expires' => null,
                'link_client_id'    => null,
            ])->save();

            return redirect()->route('welcome.step')
                ->with('link_step', 'verify');
        }

        $code = (string) random_int(100000, 999999);

        $user->forceFill([
            'link_code'         => Hash::make($code),
            'link_code_expires' => now()->addMinutes(15),
            'link_client_id'    => $client->id,
        ])->save();

        $fullName = trim("{$client->last_name} {$client->first_name} {$client->middle_name}");

        Mail::to($client->email)->send(new AccountLinkCode($code, $fullName));

        return redirect()->route('welcome.step')
            ->with('link_step', 'verify')
            ->with('link_masked_email', $this->maskEmail($client->email));
    }

    public function verify(VerifyAccountRequest $request)
    {
        $user = auth()->user();

        if (! $user->link_code || ! $user->link_code_expires || now()->isAfter($user->link_code_expires)) {
            return redirect()->route('welcome.step')
                ->with('link_step', 'verify')
                ->withErrors(['code' => 'Код истёк или недействителен. Запросите новый.']);
        }

        if (! Hash::check($request->code, $user->link_code)) {
            return redirect()->route('welcome.step')
                ->with('link_step', 'verify')
                ->with('link_masked_email', session('link_masked_email'))
                ->withErrors(['code' => 'Неверный код. Проверьте письмо и попробуйте снова.']);
        }

        $client = Client::find($user->link_client_id);

        if (! $client || ($client->user_id && $client->user_id !== $user->id)) {
            $this->clearLinkCode($user);
            return redirect()->route('welcome.step')
                ->withErrors(['code' => 'Не удалось привязать лицевой счёт. Обратитесь в службу поддержки.']);
        }

        $client->update(['user_id' => $user->id]);
        $user->forceFill(['role' => UserRole::Client->value])->save();
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