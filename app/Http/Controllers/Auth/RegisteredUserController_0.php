<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    // app/Http/Controllers/Auth/RegisteredUserController.php

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 1. Создаем пользователя
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 2. Ищем, создал ли уже админ запись для этого email в таблице clients
        $client = Client::where('email', $request->email)->first();

        if ($client) {
            // Если нашли — привязываем client_id к пользователю
            $user->update(['client_id' => $client->id]);

            event(new Registered($user));
            Auth::login($user);

            // Сразу отправляем в профиль, так как счет уже подтвержден админом
            return redirect(route('client.profile'));
        }

        event(new Registered($user));
        Auth::login($user);

        // Если в таблице clients записи нет — отправляем на ввод лицевого счета
        return redirect(route('welcome.step'));
    }
}
